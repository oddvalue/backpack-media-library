<?php

namespace Oddvalue\BackpackMediaLibrary;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class Uploader
{
    protected $mimeTypeMapping = [
        'image/*' => 'image',
        'application/pdf' => 'pdf'
    ];

    protected $client;
    protected $imageManager;

    public function __construct(Client $client, ImageManager $imageManager)
    {
        $this->client = $client;
        $this->imageManager = $imageManager;
    }

    /**
     * Tidy-up and generate a unique filename for an uploaded file, determine
     * type and move into correct location
     *
     * @param  Symfony\Component\HttpFoundation\File\UploadedFile  $uploadedFile
     * @param  Bozboz\Admin\Media\Media  $instance
         * @throws Bozboz\Admin\Exceptions\UploadException
     * @return void
     */
    public function upload(UploadedFile $uploadedFile, Media $instance, $filename = null)
    {
        DB::beginTransaction();

        $instance->name = $filename;

        $instance->filename = $this->generateUniqueFilename(
            $filename ?: $uploadedFile->getClientOriginalName(),
            $uploadedFile->getClientOriginalExtension()
        );

        $instance->disk = 'local';

        $instance->size = \File::size($uploadedFile);

        // $instance->save();

        $this->saveFile($uploadedFile, $instance);

        DB::commit();
    }

    /**
     * Tidy-up and generate a unique filename for an uploaded file, determine
     * type and move into correct location
     *
     * @param  Symfony\Component\HttpFoundation\File\UploadedFile  $uploadedFile
     * @param  Bozboz\Admin\Media\Media  $instance
         * @throws Bozboz\Admin\Exceptions\UploadException
     * @return void
     */
    public function replace(UploadedFile $uploadedFile, Media $instance)
    {
        if (pathinfo(public_path($instance->getFilename()))['extension'] !== $uploadedFile->getClientOriginalExtension()) {
            throw new \Exception("Cannot replace a file with a different extension", 1);
        }

        DB::beginTransaction();

        $this->saveFile($uploadedFile, $instance);

        DB::commit();
    }

    /**
     * Download and save a local copy of the passed in URL and associate with
     * the given media $instance
     *
     * @param  string  $url
     * @param  Bozboz\MediaLibrary\Models\Media  $instance
     * @throws Bozboz\MediaLibrary\Exceptions\UploadException
     * @return void
     */
    public function fromUrl($url, Media $instance)
    {
        DB::beginTransaction();

        $temporaryPath = public_path('.tmp/DOWNLOADED_FILE-' . time());

        try {
            $this->client->get($url, ['sink' => $temporaryPath]);
        } catch (RequestException $e) {
            DB::rollback();
            throw new UploadException($e->getMessage());
        }

        $externalFile = new File($url, false);

        $instance->save();

        $instance->filename = $this->generateUniqueFilename(
            $externalFile->getBasename(),
            $externalFile->getExtension()
        );

        $tempFile = new File($temporaryPath);

        $this->saveFile($tempFile, $instance);

        DB::commit();
    }

    /**
     * Generate a unique, clean filename from the uploaded file
     *
     * @param  string  $name
     * @param  string  $extension
     * @return string
     */
    protected function generateUniqueFilename($name, $extension)
    {
        if ($extension) {
            $filenameWithoutExtension = str_replace('.' . $extension, '', $name);
        } else {
            @list($filenameWithoutExtension, $extension) = preg_split('/\.(?=[^\.]*$)/', $name);
        }

        $suffix = '';

        do {
            $filename = Str::slug($filenameWithoutExtension) . ($suffix--) . '.' . $extension;
        } while (Media::where('filename', $filename)->exists());


        return $filename;
    }

    /**
     * Generate a type on the media instance based on the passed $file, if it
     * does not exist. Then move the file to the appropriate destination.
     *
     * @param  Symfony\Component\HttpFoundation\File\File   $file
     * @param  Bozboz\MediaLibrary\Models\Media  $instance
     * @throws Bozboz\MediaLibrary\Exceptions\UploadException
     * @return void
     */
    protected function saveFile(File $file, Media $instance)
    {
        if (empty($instance->type)) {
            $instance->type = $this->getTypeFromFile($file);
        }

        $destination = $this->getPathFromScope($instance) . '/' . $instance->getDirectory();

        if ($file->guessExtension() === 'jpeg' && env('JPG_COMPRESSION')) {
            try {
                $path = $destination . '/' . $instance->filename;
                $image = $this->imageManager->make($file)->save($path, env('JPG_COMPRESSION', 100));
            } catch (Exception $e) {
                throw new UploadException($e->getMessage());
            }
        } else {
            try {
                $file->move($destination, $instance->filename);
            } catch (FileException $e) {
                throw new UploadException($e->getMessage());
            }
        }

        $instance->save();
    }

    /**
     * Return the sub-directory to save the file, based on the mime type
     *
     * @param  Symfony\Component\HttpFoundation\File\File  $file
     * @return string
     */
    protected function getTypeFromFile(File $file)
    {
        $mimeType = $file->getMimeType();

        foreach ($this->mimeTypeMapping as $regex => $directory) {
            if (preg_match("#{$regex}#", $mimeType)) {
                return $directory;
            }
        }

        return 'misc';
    }

    /**
     * Get absolute path to the root of the directory, depending on if the file
     * is publicly accessible or not.
     *
     * @param  Bozboz\MediaLibrary\Models\Media  $instance
     * @return string
     */
    protected function getPathFromScope(Media $instance)
    {
        if ($instance->private) {
            return storage_path();
        } else {
            return public_path();
        }
    }
}
