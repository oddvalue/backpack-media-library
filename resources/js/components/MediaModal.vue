<template>
  <div>
    <modal v-model="modalIsShown" @close="showModal = false; $emit('close')" cancelText="Close" size="lg">
      <template v-slot:header>
        <ul class="nav nav-tabs" role="tablist">
          <li role="presentation" :class="{'active': tab === 'upload'}" @click="tab = 'upload'">
            <a role="tab">
              <span class="icon is-small"><i class="fa fa-upload"></i></span>
              <span>Upload</span>
            </a>
          </li>
          <li role="presentation" :class="{'active': tab === 'browse'}" @click="tab = 'browse'">
            <a role="tab">
              <span class="icon is-small"><i class="fa fa-folder-open"></i></span>
              <span>Browse</span>
            </a>
          </li>
        </ul>
      </template>
      <uploader
        v-if="tab === 'upload'"
        :multiple="isManyRelation"
        :showTitle="false"
        :dataTransfer="dataTransfer"
        @uploaded="selectFile"
      ></uploader>
      <media-browser
        v-else
        @selectFile="selectFile"
        :highlightFiles="selectedFiles"
      ></media-browser>
    </modal>

    <div v-if="!modalOnly">
      <slick-list class="tiles" v-model="selectedFiles" axis="xy" :distance="5">
        <slick-item v-for="(file, index) in selectedFiles"
          :key="file.id"
          :index="index"
          class="file-drop col-lg-2 col-md-3 col-sm-6"
        >
          <file
            :file="file"
            :canDelete="true"
            :canDrag="false"
            class="file"
            @delete="deselectFile(file)"
            @select="editFile(file)"
          >
            <input type="hidden" :name="name" v-model="file.id">
          </file>
        </slick-item>
        <div class="col-lg-2 col-md-3 col-sm-6" v-if="selectedFiles.length<1">
          <div class="panel panel-default">
            <div class="panel-body" style="font-size: 4em; text-align:center; color: silver">
              <i class="fa fa-picture-o"></i>
            </div>
          </div>
        </div>
      </slick-list>

      <div
        v-if="canSelect"
      >
        <button @click.prevent="showModal = true;tab = 'upload'"
          class="btn btn-primary"
        >
          <i class="fa fa-upload"></i>
          Upload
        </button>
        <button @click.prevent="showModal = true;tab = 'browse'"
          class="btn btn-info"
        >
          <i class="fa fa-folder-open"></i>
          Browse Media
        </button>
      </div>
      <div v-else>
        <button @click.prevent="showModal = true"
          class="btn btn-info"
        >Change</button>
        <button @click.prevent="deselectFile(selectedFiles[0])"
          class="btn btn-danger"
        >Remove</button>
      </div>
    </div>
  </div>
</template>

<script>
import { SlickList, SlickItem } from 'vue-slicksort';
import { setTimeout } from 'timers';
import Modal from './Modal';
import MediaBrowser from './MediaBrowser';
import Uploader from './Uploader';
import File from './File';
import EditModal from './EditModal';

export default {
  components: {
    SlickList,
    SlickItem,
    Modal,
    MediaBrowser,
    Uploader,
    File,
    EditModal,
  },
  props: {
    isManyRelation: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Object,
      default: () => ({
        media: [],
      }),
    },
    name: {
      type: String,
    },
    modalOnly: {
      type: Boolean,
      default: false,
    },
    show: {
      type: Boolean,
      default: false,
    },
    dataTransfer: {
      default: null,
    },
  },
  data() {
    return {
      showModal: false,
      selectedFiles: [],
      editingFile: {},
      tab: 'upload',
    };
  },
  watch: {
    showModal(newValue) {
    },
  },
  computed: {
    canSelect() {
      return this.isManyRelation || (!this.isManyRelation && this.selectedFiles.length === 0);
    },
    modalIsShown() {
      return this.show || this.showModal;
    },
  },
  methods: {
    selectFiles(files) {
      files.map(this.selectFile);
    },
    selectFile(file) {
      if (this.modalOnly) {
        this.$emit('select', file);
        return;
      }

      const exists = this.selectedFiles.findIndex(f => f.id === file.id) >= 0;
      if (exists) {
        this.deselectFile(file);
        return;
      }

      if (!this.canSelect) {
        this.selectedFiles.shift();
      }

      file.over = false;
      this.selectedFiles.push(file);

      if (!this.isManyRelation) {
        this.showModal = false;
      }
    },
    deselectFile(file) {
      this.selectedFiles.splice(this.selectedFiles.findIndex(f => f.id === file.id), 1);
    },
    editFile(file) {
      this.$store.dispatch('EditFile/edit', {
        file: {
          ...file,
          tags: (file.tags || []).map(tag => tag.name),
          name: file.filename,
          exists: true,
        },
        hiddenFields: [
          'name', 'tags', 'folder', 'file',
        ],
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
      });
    },
  },
  mounted() {
    this.selectedFiles = this.data.media;
    this.selectedFiles.map(file => file.over = false);
  }
};
</script>

<style scoped>
  .file {
    height: 100%;
  }
  .file::after {
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
  .is-dropping .file::after {
    opacity: 1;
  }
  .file-drop {
    height: 100%;
  }
  .nav-tabs {
    position: relative;
    bottom: -15px;
    border: 0;
  }

  .tiles {
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 1em;
  }
  @supports(display: grid) {
    .tiles {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(20rem, 1fr));
    }
    .tiles > * {
      width: 100%;
    }
    .tiles::before,
    .tiles::after {
      display: none;
    }
  }
</style>