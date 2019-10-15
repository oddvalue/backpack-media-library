<template>
  <drag
    :draggable="canDrag"
    :class="file.type == 'image' && !failed ? 'is-image' : 'is-file'"
    :transfer-data="{type: 'file', file: file}"
  >
    <div :class="['panel', `panel-${computedPanelClass}`]">
      <div class="panel-image">
        <button v-if="canDelete" class="btn btn-danger btn-sm delete-btn" title="Delete" @click.prevent="$emit('delete')">
          <i class="fa fa-trash"></i> Delete
        </button>
        <i v-if="computedIcon" @click="$emit('select')" :class="['icon', 'fa', `fa-${computedIcon}`]"></i>
        <a :class="{ image: true, 'image-preview': loaded && !failed, 'is-loading': !loaded, 'has-failed': failed }" v-if="file.type == 'image'" @click="$emit('select')">
          <transition name="fade">
            <img v-if="loaded && !failed" :src="src" :alt="file.filename" draggable="false">
          </transition>
        </a>
      </div>
      <div :class="[computedPanelClass === 'default' ? 'panel-footer' : 'panel-heading', 'text-center']">
        <div v-if="file.type !== 'image'" class="document_block">
          <a class="btn btn-sm btn-default" :href="`/media/${file.type}/${file.filename}`" target="_blank">
            <i class="fa fa-download" aria-hidden="true"></i>
            &nbsp;Download
          </a>
        </div>
        <div @click.prevent="$emit('select')">
          {{ file.caption || file.filename }}
        </div>
        <slot></slot>
      </div>
    </div>
  </drag>
</template>

<script>
import { Drag } from 'vue-drag-drop';
export default {
  components: {
    Drag,
  },
  props: {
    file: {
      type: Object,
    },
    canDrag: {
      type: Boolean,
      default: false,
    },
    canDelete: {
      type: Boolean,
      default: false,
    },
    panelClass: {
      type: String,
      default: 'default',
    },
    icon: {
      type: String,
      default: null,
    },
  },
  data() {
    return {
      loaded: false,
      failed: false,
    };
  },
  mounted() {
    this.loaded = false;
    if (this.file.type === 'image') {
      this.preloadImage();
    }
  },
  computed: {
    computedIcon() {
      return this.icon
        || (this.failed ? 'picture-o' : null)
        || (this.file.type === 'pdf' ? 'file-pdf-o' : null)
        || (this.file.type === 'misc' ? 'file-o' : null);
    },
    computedPanelClass() {
      return this.failed ? 'danger' : this.panelClass;
    },
    src() {
      return `/media/image/resize/400/${this.file.filename}`;
    },
  },
  methods: {
    preloadImage() {
      const img = new Image;
      img.onload = () => this.loaded = true;
      img.onerror = () => {
        this.loaded = true;
        this.failed = true;
      }
      img.src = this.src;
    },
  },
}
</script>

<style scoped lang="scss">
.fade-enter-active, .fade-leave-active {
  transition: opacity .5s;
}
.fade-enter, .fade-leave-to /* .fade-leave-active below version 2.1.8 */ {
  opacity: 0;
}
  .panel-danger .icon {
    opacity: .1;
  }
  .panel-heading,
  .panel-footer {
    height: 100%;
  }
  .icon {
    display: flex;
    position: absolute;
    justify-content: center;
    align-items: center;
  }
  .is-file .icon {
    font-size: 4em;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
  }
  .is-image .icon {
    top: 5px;
    right: 5px;
    border-radius: 50%;
    background-color: rgba(60, 118, 61, 0.75);
    color: white;
    padding: 1em;
    z-index: 1;
  }
  .panel-image {
    position: relative;
  }
  .is-file .panel-image {
    min-height: 8em;
  }
  .image {
    display: block;
    position: relative;
    padding-bottom: calc(180/240*100%);
    height: 0;

    &::before {
      position: absolute;
      opacity: .75;
    }
  }

  .is-loading {
    &::before {
      content: '';
      animation: loading 1.5s linear infinite;
      top: calc(50% - 1.5em);
      left: calc(50% - 1.5em);
      width: 3em;
      height: 3em;
      border-radius: 50%;
      border: 5px solid silver;
      border-top-color: white;
      border-bottom-color: white;
      animation: loading 2s linear infinite;
    }
  }

  img {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 100%;
    max-height: 100%;
    // background: url("data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQAQMAAAAlPW0iAAAAA3NCSVQICAjb4U/gAAAABlBMVEXMzMz////TjRV2AAAACXBIWXMAAArrAAAK6wGCiw1aAAAAHHRFWHRTb2Z0d2FyZQBBZG9iZSBGaXJld29ya3MgQ1M26LyyjAAAABFJREFUCJlj+M/AgBVhF/0PAH6/D/HkDxOGAAAAAElFTkSuQmCC");
  }

  .has-failed {
    &::before {
      content: 'IMAGE MISSING';
      font-size: 1em;
      bottom: 1.5em;
      width: 100%;
      left: 0;
      color: black;
      font-weight: normal;
      opacity: .5;
      text-align: center;
    }
  }

  @keyframes loading {
    0% {
      transform: rotate(0deg);
    }
    100% {
      transform: rotate(360deg);
    }
  }

</style>
