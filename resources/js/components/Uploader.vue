<template>
<div>
  <h1 id="title" class="title" v-if="showTitle">Upload</h1>

  <div v-show="$refs.upload && $refs.upload.dropActive" class="drop-active">
		<h3>Drop files to upload</h3>
  </div>
  <div class="upload">
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>#</th>
            <th>Thumb</th>
            <th>Name</th>
            <th>Size</th>
            <th>Speed</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!files.length">
            <td colspan="7">
              <div class="text-center p-5">
                <h4>Drop files anywhere to upload<br/>or</h4>
                <label :for="name" class="btn btn-lg btn-primary">Select Files</label>
              </div>
            </td>
          </tr>
          <tr v-for="(file, index) in files" :key="file.id" :class="{ danger: file.error, success: file.success, active: file.active }">
            <td>{{index+1}}</td>
            <td>
              <img v-if="file.thumb" :src="file.thumb" width="40" height="auto" />
              <span v-else>No Image</span>
            </td>
            <td>
              <div class="filename">
                {{file.name}}
              </div>
              <div class="progress" v-if="file.active || file.progress !== '0.00'">
                <div :class="{'progress-bar': true, 'progress-bar-striped': true, 'bg-danger': file.error, 'progress-bar-animated': file.active}" role="progressbar" :style="{width: file.progress + '%'}">{{file.progress}}%</div>
              </div>
            </td>
            <td>{{file.size | formatSize}}</td>
            <td>{{file.speed | formatSize}}</td>

            <td v-if="file.error">{{file.error}}</td>
            <td v-else-if="file.success">success</td>
            <td v-else-if="file.active">active</td>
            <td v-else></td>
            <td style="text-align: left">
              <div class="btn-group">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button">
                  Action
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
                  <li><a class="dropdown-item" v-if="!file.active && !file.success && file.error !== 'compressing'" href="#" @click.prevent="file.active || file.success || file.error === 'compressing' ? false :  onEditFileShow(file)">Edit</a></li>
                  <li><a class="dropdown-item" v-if="file.active" href="#" @click.prevent="file.active ? $refs.upload.update(file, {error: 'cancel'}) : false">Cancel</a></li>

                  <li v-if="file.active" @click.prevent="$refs.upload.update(file, {active: false})">
                    <a class="dropdown-item" href="#">Abort</a>
                  </li>
                  <li v-else-if="file.error && file.error !== 'compressing' && $refs.upload.features.html5">
                    <a class="dropdown-item" href="#" @click.prevent="$refs.upload.update(file, {active: true, error: '', progress: '0.00'})">Retry upload</a>
                  </li>
                  <li v-else-if="!file.success && file.error !== 'compressing'">
                    <a :class="{'dropdown-item': true}" href="#" @click.prevent="file.success || file.error === 'compressing' ? false : $refs.upload.update(file, {active: true})">Upload</a>
                  </li>

                  <li role="separator" class="divider"></li>
                  <li><a class="dropdown-item" href="#" @click.prevent="$refs.upload.remove(file)">Remove</a></li>
                </ul>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div class="footer">
      <div class="btn-group">
        <file-upload
          class="btn btn-primary dropdown-toggle"
          :custom-action="upload"
          :extensions="extensions"
          :accept="accept"
          :multiple="multiple"
          :directory="directory"
          :size="size || 0"
          :thread="thread < 1 ? 1 : (thread > 5 ? 5 : thread)"
          :headers="headers"
          :data="data"
          :drop="drop"
          :drop-directory="dropDirectory"
          :add-index="addIndex"
          v-model="files"
          @input-filter="inputFilter"
          @input-file="inputFile"
          ref="upload">
          <i class="fa fa-plus"></i>
          Select
          <span class="caret"></span>
        </file-upload>
        <div class="dropdown-menu">
          <li><a><label class="dropdown-item" :for="name">Add files</label></a></li>
          <li><a class="dropdown-item" href="#" @click="onAddFolder">Add folder</a></li>
        </div>
      </div>
      <button type="button" class="btn btn-success" v-if="!$refs.upload || !$refs.upload.active" @click.prevent="$refs.upload.active = true">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
        Start Upload
      </button>
      <button type="button" class="btn btn-danger"  v-else @click.prevent="$refs.upload.active = false">
        <i class="fa fa-stop" aria-hidden="true"></i>
        Stop Upload
      </button>
    </div>
  </div>
</div>
</template>

<script>
import { mapState } from 'vuex';
import ImageCompressor from 'compressorjs'
import FileUpload from 'vue-upload-component'

export default {
  components: {
    FileUpload,
  },
  props: {
    showTitle: {
      type: Boolean,
      default: true,
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    defaultFolder: {
      type: Number,
      default: null,
    },
    dataTransfer: {
      default: null,
    },
  },
  data() {
    return {
      files: [],
      accept: null, //'image/png,image/gif,image/jpeg,image/webp',
      extensions: null, //'gif,jpg,jpeg,png,webp',
      minSize: 1024,
      size: 1024 * 1024 * 10,
      directory: false,
      drop: true,
      dropDirectory: true,
      addIndex: false,
      thread: 3,
      name: 'file',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      data: {
        // '_csrf_token': document.querySelector('meta[name="csrf-token"]').content,
      },
      autoCompress: 1024 * 1024,
      uploadAuto: false,
    }
  },
  computed: {
    ...mapState({
      editFile: state => state.EditFile.updatedFile,
    })
  },
  watch: {
    editFile(newValue) {
      if (!newValue) {
        return;
      }
      const file = { ...newValue, error: '' };
      this.$refs.upload.update(file.id, file);
    },
  },
  mounted() {
    if (this.dataTransfer) {
      this.$refs.upload.addDataTransfer(this.dataTransfer);
    }
  },
  methods: {
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        // Before adding a file
        // Filter system files or hide files
        if (/(\/|^)(Thumbs\.db|desktop\.ini|\..+)$/.test(newFile.name)) {
          return prevent()
        }
        // Filter php html js file
        if (/\.(php5?|html?|jsx?)$/i.test(newFile.name)) {
          return prevent()
        }
        // Automatic compression
        if (newFile.file && newFile.type.substr(0, 6) === 'image/' && this.autoCompress > 0 && this.autoCompress < newFile.size) {
          newFile.error = 'compressing'
          new ImageCompressor(newFile.file, {
            convertSize: 3000000, // Convert pngs over 3mb to jpg
            maxWidth: 1920,
            maxHeight: 1920,
            quality: 0.8,
            success: (file) => {
              this.$refs.upload.update(newFile, { error: '', file, size: file.size, type: file.type })
            },
            error: (err) => {
              this.$refs.upload.update(newFile, { error: err.message || 'compress' })
            }
          });
        }

        newFile.tags = [];
        newFile.folder_id = this.defaultFolder;
      }
      if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
        // Create a blob field
        newFile.blob = ''
        let URL = window.URL || window.webkitURL
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file)
        }
        // Thumbnails
        newFile.thumb = ''
        if (newFile.blob && newFile.type.substr(0, 6) === 'image/') {
          newFile.thumb = newFile.blob
        }
      }
    },
    // add, update, remove File Event
    inputFile(newFile, oldFile) {
      if (newFile && newFile !== oldFile) {
        this.$refs.upload.update(file, {
          caption: '',
          tags: [],
          folder_id: this.defaultFolder,
        });
      }
    },
    async upload(file, component) {
      const formData = new FormData();
      formData.append('name', file.name);
      formData.set("file", file.file, file.filename);
      if (file.caption) {
        formData.append('caption', file.caption);
      }
      if (file.tags) {
        file.tags.map(tag => {
          formData.append('tags[]', tag);
        });
      }
      if (file.folder_id) {
        formData.append('folder_id', file.folder_id);
      }

      try {
        const response = await (await axios.post('/admin/media/upload', formData)).data;
        this.$emit('uploaded', response);
        return response;
      } catch (error) {
        if (error.response && error.response.status === 422) {
          const errors = [error.response.data.message];
          for (const field in error.response.data) {
            if (error.response.data.hasOwnProperty(field)) {
              errors.push(error.response.data[field]);
            }
          }
          throw new Error(errors.join("\n"));
        }
        throw error;
      }
    },
    alert(message) {
      alert(message)
    },
    onEditFileShow(editFile) {
      this.$store.dispatch('EditFile/edit', { file: { ...editFile, show: true } });
    },
    // add folder
    onAddFolder() {
      if (!this.$refs.upload.features.directory) {
        this.alert('Your browser does not support')
        return
      }
      let input = this.$refs.upload.$el.querySelector('input')
      input.directory = true
      input.webkitdirectory = true
      this.directory = true
      input.onclick = null
      input.click()
      input.onclick = (e) => {
        this.directory = false
        input.directory = false
        input.webkitdirectory = false
      }
    },
  }
}
</script>

<style scoped>
.btn-group .dropdown-menu {
  display: block;
  visibility: hidden;
  transition: all .2s
}
.btn-group:hover > .dropdown-menu {
  visibility: visible;
}
label.dropdown-item {
  margin-bottom: 0;
  font-weight: normal;
  cursor: pointer;
}
label.dropdown-item::after,
label.btn::after {
  display: none;
}
.btn-group .dropdown-toggle {
  margin-right: .6rem
}
.filename {
  margin-bottom: .3rem
}
.btn-is-option {
  margin-top: 0.25rem;
}
.footer {
  padding-top: 1rem;
  border-top: 1px solid #e9ecef;
}
.edit-image img {
  max-width: 100%;
}
.edit-image-tool {
  margin-top: .6rem;
}
.edit-image-tool .btn-group{
  margin-right: .6rem;
}
.footer-status {
  padding-top: .4rem;
}
.drop-active {
  top: 0;
  bottom: 0;
  right: 0;
  left: 0;
  position: fixed;
  z-index: 9999;
  opacity: .6;
  text-align: center;
  background: #000;
}
.drop-active h3 {
  margin: -.5em 0 0;
  position: absolute;
  top: 50%;
  left: 0;
  right: 0;
  -webkit-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  transform: translateY(-50%);
  font-size: 40px;
  color: #fff;
  padding: 0;
}
.p-5 {
  padding: 4rem 3rem;
}
</style>