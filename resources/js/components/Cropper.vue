<template>
  <div class="form-group" v-if="file.show && file.blob && file.type && file.type.substr(0, 6) === 'image/'">
    <label>Image</label>
    <div class="edit-image">
      <img :src="file.blob" ref="editImage" />
    </div>
    <div class="edit-image-tool">
      <div class="btn-group" role="group">
      <button type="button" class="btn btn-primary" @click="file.cropper.rotate(-90)" title="cropper.rotate(-90)"><i class="fa fa-undo" aria-hidden="true"></i></button>
      <button type="button" class="btn btn-primary" @click="file.cropper.rotate(90)"  title="cropper.rotate(90)"><i class="fa fa-repeat" aria-hidden="true"></i></button>
      </div>
      <div class="btn-group" role="group">
      <button type="button" class="btn btn-primary" @click="file.cropper.crop()" title="cropper.crop()"><i class="fa fa-check" aria-hidden="true"></i></button>
      <button type="button" class="btn btn-primary" @click="file.cropper.clear()" title="cropper.clear()"><i class="fa fa-remove" aria-hidden="true"></i></button>
      </div>
    </div>
  </div>
</template>