<?php

namespace Oddvalue\BackpackMediaLibrary\Http\Controllers;

use Illuminate\Routing\Controller;
use Oddvalue\BackpackMediaLibrary\Media;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Symfony\Component\HttpFoundation\Request;
use Oddvalue\BackpackMediaLibrary\MediaFolder;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class MediaLibraryCrudController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    // use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
    // use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;

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

        debug($request->all());

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

        app(\Bozboz\Admin\Services\Uploader::class)->upload($file, $instance, $request->input('name'));

        if ($request->has('tags')) {
            $tags = collect($request->input('tags'))->map(function($value) {
                return Tag::firstOrCreate([
                    'name' => $value
                ])->id;
            });
            $instance->tags()->sync($tags->all());
        }

        $instance->save();

        return $instance;
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

            $query = $model::where('mime_type', $mime_type)
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
                $query->whereHas('tags', function($query) {
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
        return response()->json([]);
        // if (!$this->canView()) {
        //     return abort(403);
        // }
        return response()->json(
            Tag::orderBy('name')->get()
        );
    }
}
