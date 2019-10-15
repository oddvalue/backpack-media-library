<template>
<div>
  <!-- File form -->
  <div v-if="canCreate">
    <a :href="'/admin/media/upload' + (currentFolder ? `?folder=${currentFolder.id}` : '')" class="btn-success pull-right btn btn-sm new-btn">New Media <i class="fa fa-plus"></i></a>
    <form id="new-folder-form" class="form-inline pull-right" style="clear:right;" action="#" method="#" @submit.prevent="newFolder">
      <label for="new_folder">New Folder</label>
      <div class="input-group">
        <input class="form-control input-sm" type="text" id="new_folder" name="name" placeholder="Folder name" v-model="folderName" required>
        <div class="input-group-btn">
          <button type="submit" class="btn btn-primary btn-sm">
            Add new folder
          </button>
        </div>
      </div>
    </form>
  </div>


  <!-- Confirm -->
  <modal title="Are you sure?" v-model="showConfirm" @close="cancelDeleting()" size="sm">
    <p>Are you sure you want to continue?</p>

    <template v-slot:footer>
      <button class="btn btn-primary" @click="deleteFile()">
        Confirm
      </button>
    </template>
  </modal>

  <!-- Main -->
  <div class="container-fluid" style="margin-bottom: 1em">
    <div class="form-inline">
      <div class="form-group">
        <label for="search">Search</label>
        <div class="input-group">
          <input name="search" v-model="searchString" @input="pagination.current_page = 1" type="text" id="search" class="form-control input-sm">
          <div class="input-group-btn">
            <input v-if="searchString" @click.prevent="searchString = ''" type="button" value="×" class="btn btn-sm btn-default">
          </div>
        </div>
      </div>
      <div class="form-group">
        <label for="tags">Tags</label>
        <div class="input-group" style="min-width: 6em">
          <multiselect
            v-model="tagFilter"
            placeholder=""
            label="name"
            track-by="name"
            id="tags"
            :tabindex="0"
            :options="tags"
            :multiple="true"
            :taggable="false"
          ></multiselect>
        </div>
      </div>
    </div>
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation" :class="{'active': isActive('image')}" @click="getFiles('image')">
        <a role="tab">
          <span class="icon is-small"><i class="fa fa-image"></i></span>
          <span>Pictures</span>
        </a>
      </li>
      <li role="presentation" :class="{'active': isActive('pdf')}" @click="getFiles('pdf')">
        <a role="tab">
          <span class="icon is-small"><i class="fa fa-file-text-o"></i></span>
          <span>PDF</span>
        </a>
      </li>
      <li role="presentation" :class="{'active': isActive('misc')}" @click="getFiles('misc')">
        <a role="tab">
          <span class="icon is-small"><i class="fa fa-file"></i></span>
          <span>Misc</span>
        </a>
      </li>
    </ul>
  </div>

  <pagination v-model="pagination" @input="changePage"></pagination>

  <div class="tabs-details">
    <div class="text-center tiles">
    <div class="col-lg-2 col-md-3 col-sm-6" v-if="currentFolder" v-cloak>
      <drop
        @drop="moveIntoFolder({ id: currentFolder.parent_id }, ...arguments)"
        @dragover="upFolderOver = true"
        @dragleave="upFolderOver = false"
        :class="{'panel': true, 'panel-info': true, 'is-dropping': upFolderOver}"
      >
      <div class="card-image">
        <div class="panel-body">
        <a class="text-center center-block" style="font-size: 4em" @click="upFolder(currentFolder)">
          <i class="fa fa-arrow-left"></i>
        </a>
        « Back
        </div>
      </div>
      </drop>
    </div>
    <div class="col-lg-2 col-md-3 col-sm-6" v-for="folder in folders" :key="folder.id" v-cloak>
      <drop
        @drop="moveIntoFolder(folder, ...arguments)"
        @dragover="folder.over = true"
        @dragleave="folder.over = false"
        :class="{'panel': true, 'panel-primary': true, 'is-dropping': folder.over}"
      >
      <drag
      :draggable="canEdit"  class="card-image" :transfer-data="{type: 'folder', folder: folder}">
        <button v-if="canDelete" class="btn btn-danger btn-sm delete-btn" title="Delete" @click="prepareToDeleteFolder(folder)">
          <i class="fa fa-trash"></i> Delete
        </button>
        <div class="panel-body">
        <a class="text-center center-block" style="font-size: 4em" @click="openFolder(folder)">
          <i class="fa fa-folder"></i>
        </a>
        <input class="form-control input-sm" v-if="folder === editingFolder" v-autofocus @keyup.enter="updateFolder(folder)" @blur="updateFolder(folder)" type="text" :placeholder="folder.name" v-model="folder.name">
        <div @click="editFolder(folder)" v-else title="Click to edit folder name">
          {{ folder.caption || folder.name }}
        </div>
        </div>
      </drag>
      </drop>
    </div>
    </div>
    <hr>
    <div class="text-center tiles">

      <div class="col-sm-12 col-md-4 col-md-offset-4 text-center p-5" v-if="pagination.total == 0" v-cloak>
        <figure>
        <i class="fa fa-folder-open-o" style="font-size: 4em"></i>
        <figcaption>
          <p class="title is-2">
          This folder is empty!
          </p>
        </figcaption>
        </figure>
      </div>

      <transition name="fade">
        <div class="loader" v-if="loading">
          <i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i>
          <span class="sr-only">Loading...</span>
        </div>
      </transition>

      <file class="col-lg-2 col-md-3 col-sm-6"
        v-for="file in files"
        :key="file.id"
        :file="file"
        :canDrag="canEdit"
        :canDelete="canDelete"
        :panelClass="highlightIds.includes(file.id) ? 'success' : 'default'"
        :icon="highlightIds.includes(file.id) ? 'check' : null"
        @select="editFile(file)"
        @delete="prepareToDelete(file)"
      ></file>

    </div>
  </div>

  <pagination v-model="pagination" @input="changePage"></pagination>

</div>
</template>


<script>
import _ from 'lodash';
import Multiselect from 'vue-multiselect';
import { mapState } from 'vuex';
import { Drag, Drop } from 'vue-drag-drop';
import Modal from './Modal';
import File from './File';
import Pagination from './Pagination';
import mediaApi from '../api/media';

export default {
  components: {
    Multiselect,
    Modal,
    File,
    Pagination,
    Drag,
    Drop,
  },
  directives: {
    'autofocus': {
      inserted(el) {
        el.focus();
      }
    }
  },
  props: {
    canCreate: {
      type: Boolean,
      default: false,
    },
    canDelete: {
      type: Boolean,
      default: false,
    },
    canEdit: {
      type: Boolean,
      default: false,
    },
    highlightFiles: {
      type: Array,
      default: () => [],
    },
  },
  data() {
    return {
      files: [],
      folders: {},
      pagination: {},
      file: {},
      tags: [],
      tagFilter: [],
      pagination: {},
      currentFolder: null,
      activeTab: 'image',
      formData: {},
      fileName: '',
      attachment: '',
      folderName: '',
      editingFile: {},
      deletingFile: {},
      editingFolder: {},
      deletingFolder: {},
      showConfirm: false,
      modalActive: false,
      message: '',
      errors: {},
      upFolderOver: false,
      searchString: '',
    };
  },

  computed: {
    ...mapState({
      data: state => state.MediaList.data,
      loading: state => state.MediaList.loading,
    }),
    highlightIds() {
      return this.highlightFiles.map(file => file.id);
    }
  },

  watch: {
    data() {
      this.files = [ ...this.data.data.data ];
      this.folders = [ ...this.data.folders.map(folder => ({ ...folder, over: false })) ];
      this.pagination = { ...this.data.pagination };
    },
    searchString(search) {
      if (search) {
        this.currentFolder = null;
      }
      this.throttledRefresh();
    },
    tagFilter() {
      this.throttledRefresh();
    },
  },

  methods: {
    changePage(pagination) {
      this.pagination.current_page = pagination.current_page;
      this.fetchFile(this.activeTab, pagination.current_page);
    },

    throttledRefresh: _.throttle(function() {
      this.fetchFile(this.activeTab, this.pagination.current_page)
    }, 1000),

    isActive(tabItem) {
      return this.activeTab === tabItem;
    },

    setActive(tabItem) {
      this.activeTab = tabItem;
    },

    fetchFile(type, page = 1) {
      this.$store.dispatch('MediaList/get', {
        type: type,
        page: page,
        folderId: this.currentFolder ? this.currentFolder.id : '',
        search: this.searchString,
        tags: this.tagFilter.length ? this.tagFilter.map(tag => tag.name) : [],
      });
    },

    getFiles(type) {
      this.setActive(type);
      this.fetchFile(type);
    },

    newFolder() {
      this.formData = new FormData();
      this.formData.append('name', this.folderName);

      if (this.currentFolder) {
        this.formData.append('parent_id', this.currentFolder.id);
      }

      window.axios.post('/admin/media/folder/add', this.formData, {headers: {'Content-Type': 'multipart/form-data'}})
        .then(response => {
          this.folderName = '';
          this.showNotification('Folder created', true);
          this.fetchFile(this.activeTab);
        })
        .catch(error => {
          this.showNotification(error.response.data.message, false);
          for (const field in error.response.data) {
            if (error.response.data.hasOwnProperty(field)) {
              const message = error.response.data[field];
              this.showNotification(message, false);
            }
          }
        });
    },

    moveIntoFolder(targetFolder, data) {
      targetFolder.over = false;
      this.upFolderOver = false;

      switch (data.type) {
        case 'folder':
          if (targetFolder.id === data.folder.id) {
            return;
          }
          this.updateFolder({
            ...data.folder,
            parent_id: targetFolder.id,
          });
          break;

        case 'file':
          const {folder, ...fileWithoutFolder} = data.file;
          this.$store.dispatch('EditFile/update', {
            ...fileWithoutFolder,
            folder_id: targetFolder.id,
          });
          break;

        default:
          this.showNotification(`Unknown type: '${data.type}'`, false);
          break;
      }
    },

    prepareToDelete(file) {
      this.deletingFile = file;
      this.showConfirm = true;
    },

    prepareToDeleteFolder(folder) {
      this.deletingFolder = folder;
      this.showConfirm = true;
    },

    cancelDeleting() {
      this.deletingFile = {};
      this.deletingFolder = {};
      this.showConfirm = false;
    },

    deleteFile() {
      const config = {};
      if (this.deletingFolder.id) {
        config.url = `/admin/media/folder/delete/${this.deletingFolder.id}`;
        config.type = 'Folder';
      } else {
        config.url = `/admin/media/delete/${this.deletingFile.id}`;
        config.type = 'File';
      }
      window.axios.post(config.url)
        .then(response => {
          this.showNotification(`${config.type} deleted`, true);
          this.fetchFile(this.activeTab, this.pagination.current_page);
        })
        .catch(error => {
          this.errors = error.response.data.errors();
          this.showNotification('Something went wrong! Please try again later.', false);
          this.fetchFile(this.activeTab, this.pagination.current_page);
        });

      this.cancelDeleting();
    },

    editFolder(folder) {
      if (!this.canEdit) {
        this.$emit('selectFolder', folder);
        return;
      }
      this.editingFolder = folder;
    },

    updateFolder(folder) {
      this.editingFolder = {};

      if (folder.name.trim() === '') {
        alert('Folder name cannot be empty!');
        this.fetchFile(this.activeTab, this.pagination.current_page);
      } else {
        let formData = new FormData();
        formData.append('name', folder.name);
        if (folder.parent_id) {
          formData.append('parent_id', folder.parent_id);
        }

        axios.post('/admin/media/folder/update/' + folder.id, formData)
          .then(response => {
            if (response.data.success === true) {
              this.showNotification('Folder successfully updated', true, () => this.updateFolder(response.data.original));
            }
          })
          .catch(error => {
            this.showNotification(error.response.data.message, false);
            for (const field in error.response.data) {
              if (error.response.data.hasOwnProperty(field)) {
                const message = error.response.data[field];
                this.showNotification(message, false);
              }
            }
          })
          .finally(() => {
            this.fetchFile(this.activeTab, this.pagination.current_page);
          });
      }
    },

    editFile(file) {
      if (!this.canEdit) {
        this.$emit('selectFile', file);
        return;
      }
      this.$store.dispatch('EditFile/edit', {
        file: {
          ...file,
          tags: file.tags.map(tag => tag.name),
          name: file.filename,
          exists: true,
        },
      });
    },

    showNotification(text, success, undoAction = null) {

      const actions = [
        {
          text: '×',
          onClick: (e, toast) => toast.goAway(),
          class: 'toast-action toast-action--dismiss',
        },
      ];

      if (undoAction) {
        actions.unshift({
          text: 'Undo',
          class: 'toast-action',
          onClick: (e, toast) => {
            toast.goAway(0);
            undoAction(toast);
          },
        });
      }

      this.$toasted.show(text, {
        duration: 15000,
        type: success ? 'success' : 'error',
        position: 'bottom-right',
        iconPack: 'fontawesome',
        action: actions,
        icon: success ? 'check' : 'exclamation-triangle',
      })
    },

    openFolder(folder) {
      this.currentFolder = folder;
      this.fetchFile(this.activeTab);
    },

    upFolder(folder) {
      if (folder && folder.parent_id) {
        this.currentFolder = {id: folder.parent_id};
      } else {
        this.currentFolder = null;
      }
      this.fetchFile(this.activeTab);
    },
  },

  mounted() {
    this.fetchFile(this.activeTab, this.pagination.current_page);
    window.axios.get('/admin/media/tags').then(response => this.tags = response.data);
  },

}
</script>

<style>
  .fade-enter-active, .fade-leave-active {
    transition: opacity .3s;
  }
  .fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
    opacity: 0;
  }
  a {
    cursor: pointer;
  }
  .new-btn {
    margin-bottom: 1em;
  }
  .p-5 {
    padding: 3em;
  }
  .panel::after {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    background-color: rgba(0,0,0,.5);
    opacity: 0;
    pointer-events: none;
    transition: .2s opacity;
  }
  .is-dropping::after {
    opacity: 1;
  }
  .toast-action {
    color: white !important;
    opacity: .75;
    font-size: .9em !important;
  }
  .toast-action:hover {
    opacity: 1;
    text-decoration: none !important;
  }
  .toast-action--dismiss {
    font-size: 1.5em !important;
  }
  .tiles {
    display: flex;
    flex-wrap: wrap;
  }
  .panel {
    position: relative;
    overflow: hidden;
  }
  @supports(display: grid) {
    .panel {
      height: 100%;
      margin-bottom: 0;
    }
    .tiles {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr));
      grid-gap: 1em;
    }
    .tiles > * {
      width: 100%;
      padding: 0;
    }
    .tiles::before,
    .tiles::after {
      display: none;
    }
  }
  .loader {
    position: fixed;
    display: flex;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    justify-content: center;
    align-items: center;
    z-index: 1;
    background-color: rgba(255,255,255,.5);
  }
  .delete-btn {
    padding: 1px 5px;
    position: absolute;
    right: 5px;
    top: 5px;
    text-indent: 100%;
    overflow: hidden;
    width: 1.5em;
    height: 1.5em;
    font-size: 1.5em;
    border-radius: 50%;
    background-color: rgba(0,0,0,.25);
    border-color: rgba(0,0,0,0);
    z-index: 1;
  }
  .delete-btn::after {
    content: '×';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-indent: 0;
  }
  .delete-btn:hover {
    background-color: #d9534f;
  }
</style>
