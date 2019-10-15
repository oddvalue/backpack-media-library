<template>
  <div>
    <div v-if="editFile.show" :class="{'modal-backdrop': true, 'fade': true, in: editFile.show}"></div>
    <div :class="{modal: true, fade: true, in: editFile.show}" id="modal-edit-file" tabindex="-1" role="dialog" :style="{display: editFile.show ? 'block' : 'none'}">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
      <div class="modal-header">
      <button type="button" class="close" @click.prevent="editFile.show = false">
      <span>&times;</span>
      </button>
      <h5 class="modal-title">Edit file</h5>
      </div>
      <form @submit.prevent="onEditorFile">
      <div class="modal-body">
      <div class="form-group">
        <label for="name">Name:</label>
        <input type="text" class="form-control" required id="name"  placeholder="Please enter a file name" v-model="editFile.name">
      </div>
      <div class="form-group">
        <label for="caption">Caption:</label>
        <input type="text" class="form-control" id="caption" v-model="editFile.caption">
      </div>
      <div class="form-group">
        <label for="tagsSelect">Tags</label>
        <multiselect
        v-model="editFile.tags"
        tag-placeholder="Add this as new tag"
        placeholder=""
        label="name"
        track-by="name"
        id="tagsSelect"
        :tabindex="0"
        :options="options.tags"
        :multiple="true"
        :taggable="true"
        @tag="addTag"
        ></multiselect>
      </div>
      <div class="form-group">
        <label for="folder">Folder</label>
        <multiselect
        v-model="editFile.folder"
        placeholder=""
        label="name"
        track-by="id"
        id="folder"
        :tabindex="0"
        :options="options.folders"
        :multiple="false"
        >
        <template slot="option" slot-scope="props">
        <span v-html="props.option.indentedName"></span>
        </template>
        </multiselect>
      </div>
      <div class="form-group" v-if="editFile.show && editFile.blob && editFile.type && editFile.type.substr(0, 6) === 'image/'">
        <label>Image: </label>
        <div class="edit-image">
        <img :src="editFile.blob" ref="editImage" />
        </div>
        <div class="edit-image-tool">
        <div class="btn-group" role="group">
        <button type="button" class="btn btn-primary" @click="editFile.cropper.rotate(-90)" title="cropper.rotate(-90)"><i class="fa fa-undo" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-primary" @click="editFile.cropper.rotate(90)"  title="cropper.rotate(90)"><i class="fa fa-repeat" aria-hidden="true"></i></button>
        </div>
        <div class="btn-group" role="group">
        <button type="button" class="btn btn-primary" @click="editFile.cropper.crop()" title="cropper.crop()"><i class="fa fa-check" aria-hidden="true"></i></button>
        <button type="button" class="btn btn-primary" @click="editFile.cropper.clear()" title="cropper.clear()"><i class="fa fa-remove" aria-hidden="true"></i></button>
        </div>
        </div>
      </div>
      </div>
      <div class="modal-footer">
      <button type="button" class="btn btn-secondary" @click.prevent="editFile.show = false">Close</button>
      <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
      </div>
    </div>
  </div>
  </div>
</template>