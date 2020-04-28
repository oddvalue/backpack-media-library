<?php

namespace Oddvalue\BackpackMediaLibrary\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Oddvalue\BackpackMediaLibrary\MediaFolder;
use Illuminate\Foundation\Validation\ValidatesRequests;

class FolderCrudController extends Controller
{
    use ValidatesRequests;

    /**
     * Upload new file and store it
     * @param  Request $request Request with form data: filename and file info
     * @return boolean          True if success, otherwise - false
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:media_folders,name,NULL,id,parent_id,'.$request->input('parent_id', 'NULL'),
        ]);

        return MediaFolder::create([
            'name' => $request->input('name'),
            'parent_id' => $request->input('parent_id'),
        ]);
    }

    /**
     * Edit specific file
     * @param  integer  $id      Media Id
     * @param  Request $request  Request with form data: filename
     * @return boolean           True if success, otherwise - false
     */
    public function update($id, Request $request)
    {
        $model = MediaFolder::find($id);

        if (! $model) {
            return response()->json(false);
        }

        $this->validate($request, [
            'name' => 'required|unique:media_folders,name,'
                .$id
                .',id,parent_id,'
                .($request->input('parent_id') ?: 'NULL'),
            'parent_id' => 'int|exists:media_folders,id',
        ], [
            'name.unique' => "There is already a folder called '{$request->input('name')}' in this folder",
        ]);

        return response()->json([
            'original' => $model->getOriginal(),
            'success' => $model->update([
                'name' => $request->input('name'),
                'parent_id' => $request->input('parent_id'),
            ])
        ]);
    }


    /**
     * Delete file from disk and database
     * @param  integer $id  Media Id
     * @return boolean      True if success, otherwise - false
     */
    public function destroy($id)
    {
        $folder = MediaFolder::findOrFail($id);

        return response()->json($folder->delete());
    }

    public function getDropdownOptions()
    {
        return MediaFolder::whereNull('parent_id')->orderBy('name')->get()->reduce(function ($collection, $folder) {
            return $folder->indentDescendants($collection);
        }, collect());
    }
}
