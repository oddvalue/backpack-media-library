<?php

namespace Oddvalue\BackpackMediaLibrary\Http\Controllers;

use Illuminate\Routing\Controller;
use Oddvalue\BackpackMediaLibrary\Tag;
use Oddvalue\BackpackMediaLibrary\Media;
use Oddvalue\BackpackMediaLibrary\Uploader;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\HttpFoundation\Request;
use Oddvalue\BackpackMediaLibrary\MediaFolder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MediaLibraryCrudController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $image_ext = ['jpg', 'jpeg', 'png', 'gif'];
    private $audio_ext = ['mp3', 'ogg', 'mpga'];
    private $video_ext = ['mp4', 'mpeg'];
    private $document_ext = [
        'doc',
        'docx',
        'pdf',
        'odt',
        'xls',
        'xlsx',
        'csv',
        'ppt',
        'pptx',
        'xml',
        'zip',
        'tar',
        'txt',
        'rtf',
    ];

    public function index()
    {
        return view('media-library::crud.list');
    }

    public function create()
    {
        return view('media-library::crud.create');
    }

    public function store(Request $request)
    {
        // if (!$this->canCreate()) {
        //     return abort(403);
        // }
        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->allExtensions());

        $this->validate($request, [
            'name' => 'required',
            'file' => 'required|file|mimes:' . $all_ext . '|max:' . $max_size,
            'folder_id' => 'int|exists:media_folders,id',
        ]);

        $instance = new Media([
            'folder_id' => $request->input('folder_id'),
            'caption' => $request->input('caption'),
        ]);

        $file = $request->file('file');

        app(Uploader::class)->upload($file, $instance, $request->input('name'));

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))->map(function ($value) {
                return Tag::firstOrCreate([
                    'name' => $value
                ])->id;
            });
            $instance->tags()->sync($tags->all());
        }

        $instance->save();

        return $instance;
    }

    /**
     * Edit specific file
     * @param  integer  $id      Media Id
     * @param  Request $request  Request with form data: filename
     * @return boolean           True if success, otherwise - false
     */
    public function update($id, Request $request)
    {
        // if (!$this->canEdit()) {
        //     return abort(403);
        // }
        $instance = Media::findOrFail($id)->load('tags');

        $max_size = (int)ini_get('upload_max_filesize') * 1000;
        $all_ext = implode(',', $this->allExtensions());

        $original = $instance->getOriginal();

        $this->validate($request, [
            'file' => 'file|max:' . $max_size . '|mimes:' . $all_ext,
            'folder_id' => 'nullable|int|exists:media_folders,id',
            'tags' => 'array',
        ]);

        $instance->caption = $request->input('caption');
        $instance->folder_id = $request->input('folder_id') ?: null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            try {
                app(Uploader::class)->replace($file, $instance);
            } catch (\Exception $e) {
                return response()->json(new MessageBag(['file' => $e->getMessage()]), 422);
            }
        }

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))->filter(function ($tag) {
                return !is_null($tag) && $tag !== '';
            })->map(function ($value) {
                return Tag::firstOrCreate([
                    'name' => $value
                ])->id;
            });
            $instance->tags()->sync($tags->all());
        }

        $instance->save();

        return response()->json([
            'success' => true,
            'original' => $original,
        ]);
    }

    /**
     * Delete file from disk and database
     * @param  integer $id  Media Id
     * @return boolean      True if success, otherwise - false
     */
    public function destroy($id)
    {
        // if (!$this->canDestroy()) {
        //     return abort(403);
        // }
        $file = Media::findOrFail($id);
        return response()->json($file->delete());
    }

    public function resize($mode, $size, $filename)
    {
        list($width, $height) = explode('x', $size.'x');
        try {
            $img = \Image::make(public_path("/media/image/{$filename}"));
        } catch (NotReadableException $e) {
            return abort(404);
        }
        if ($width || $height) {
            $img->$mode($width ?: null, $height ?: null, function ($constraint) {
                $constraint->aspectRatio();
            });
        }
        $folder = collect(explode('/', dirname(request()->path())))->reduce(function ($path, $folder) {
            $path .= "/$folder";
            if (!is_dir($path)) {
                mkdir($path, 0755);
            }
            return $path;
        }, public_path());
        $img->save("$folder/$filename");
        return $img->response();
    }

    public function setupListOperation()
    {
        $this->crud->setListView('media-library::crud.list');

        // columns to show in the table view
        $this->crud->setColumns([
            [
                'name'  => 'name',
                'label' => trans('media-library::admin.media'),
            ],
            [
                'name'  => 'value',
                'label' => trans('media-library::admin.media'),
            ],
            [
                'name'  => 'description',
                'label' => trans('media-library::admin.media'),
            ],
        ]);
    }

    public function setupUpdateOperation()
    {
        $this->crud->addField([
            'name'       => 'name',
            'label'      => trans('media-library::admin.media'),
            'mime_type'       => 'text',
            'attributes' => [
                'disabled' => 'disabled',
            ],
        ]);
        $this->crud->addField(json_decode($this->crud->getCurrentEntry()->field, true));
    }

    /**
     * Fetch files by Type or Id
     * @param  string $mime_type  Media mime_type
     * @param  integer $id   Media Id
     * @return object        Files list, JSON
     */
    public function getList($mime_type, $id = null)
    {
        // if (!$this->canView()) {
        //     return abort(403);
        // }
        $model = new \Oddvalue\BackpackMediaLibrary\Media();

        if (!is_null($id)) {
            $response = $model::findOrFail($id);
        } else {
            $records_per_page = 24;

            $query = MediaFolder::orderBy('name');
            if (request()->input('search')) {
                $query->where('name', 'LIKE', '%' . request()->input('search') . '%');
            } else {
                if (request()->input('folder')) {
                    $query->where('parent_id', request()->input('folder'));
                } else {
                    $query->whereNull('parent_id');
                }
            }
            $folders = $query->get();

            $query = $model::where('type', $mime_type)
                ->with('tags')
                ->orderBy('id', 'desc');

            if (request()->input('search')) {
                $query->where(function ($query) {
                    $query->orWhere('filename', 'LIKE', '%' . request()->input('search') . '%');
                    $query->orWhere('caption', 'LIKE', '%' . request()->input('search') . '%');
                });
            } else {
                if (request()->input('folder')) {
                    $query->where('folder_id', request()->input('folder'));
                } else {
                    $query->whereNull('folder_id');
                }
            }

            if (request()->input('tags')) {
                $query->whereHas('tags', function ($query) {
                    $query->whereIn('name', request()->input('tags'));
                });
            }

            $files = $query->paginate($records_per_page);

            $response = [
                'pagination' => [
                    'total' => $files->total(),
                    'per_page' => $files->perPage(),
                    'current_page' => $files->currentPage(),
                    'last_page' => $files->lastPage(),
                    'from' => $files->firstItem(),
                    'to' => $files->lastItem()
                ],
                'data' => $files,
                'folders' => $folders,
            ];
        }

        return response()->json($response);
    }

    public function getTags()
    {
        // if (!$this->canView()) {
        //     return abort(403);
        // }
        return response()->json(
            Tag::orderBy('name')->get()
        );
    }

        /**
     * Get all extensions
     * @return array Extensions of all file types
     */
    private function allExtensions()
    {
        return array_merge($this->image_ext, $this->audio_ext, $this->video_ext, $this->document_ext);
    }
}
