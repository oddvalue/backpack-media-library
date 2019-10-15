<template>
    <div class="tinymce">
        <tinymce :name="name" v-model="value" :init="{
            content_css: '/admin/tinymce/skin/content.min.css',
            content_style: `blockquote { border-left: 5px solid silver; padding-left: 1em; margin-left: .25em; } img { max-width: 100%; } ${contentStyle}`,
            formats: formats,
            style_formats: styleFormats,
            style_formats_merge: true,
            skin_url: '/admin/tinymce/skin',
            browser_spellcheck: true,
            plugins: 'paste link wordcount anchor code fullscreen autoresize visualblocks help mediabrowser lists toc stickytoolbar table',
            toolbar: 'undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | table | toc | link anchor mediabrowser',
        }" @onDrop="drop"></tinymce>
        <modal
            v-model="showEditModal"
            @close="endEditing"
        >
            <label for="caption">Caption</label>
            <input type="text" @keydown.enter.prevent="endEditing" v-model="editingFile.alt" class="form-control">
            <label for="caption">Width</label>
            <input type="number" v-model="editingFile.width" ref="width" class="form-control">
            <label for="caption">Height</label>
            <input type="number" v-model="editingFile.height" ref="height" class="form-control">
        </modal>
        <media-modal
            :modalOnly="true"
            :show="showMediaModal"
            :dataTransfer="dataTransfer"
            @close="closeMediaModal"
            @select="insertMedia"
        ></media-modal>
    </div>
</template>

<script>
import 'tinymce/tinymce';
import Tinymce from '@tinymce/tinymce-vue';
import 'tinymce/themes/silver';
import 'tinymce/plugins/paste';
import 'tinymce/plugins/link';
import 'tinymce/plugins/wordcount';
import 'tinymce/plugins/anchor';
import 'tinymce/plugins/code';
import 'tinymce/plugins/fullscreen';
import 'tinymce/plugins/autoresize';
import 'tinymce/plugins/visualblocks';
import 'tinymce/plugins/lists';
import 'tinymce/plugins/toc';
import 'tinymce/plugins/table';
import 'tinymce/plugins/help';

import 'stickytoolbar';
import setupMediaPlugin from '../tinymce/plugins/media-picker/plugin';

import MediaModal from './MediaModal';
import Modal from './Modal';

export default {
    components: {
        Tinymce,
        MediaModal,
        Modal,
    },
    props: {
        replace: {
            type: String,
        },
        contentStyle: {
            type: String,
            default: '',
        },
        formats: {
            type: Object,
            default: () => ({}),
        },
        styleFormats: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            showMediaModal: false,
            editorInstance: null,
            bookmark: null,
            name: null,
            value: null,
            showEditModal: false,
            currentNode: null,
            editingFile: {},
            dataTransfer: [],
        };
    },
    computed: {
    },
    watch: {
        'editingFile.width'(newWidth, oldWidth) {
            if (!this.$refs.width || this.$refs.width !== document.activeElement) {
                return;
            }
            this.editingFile.height = Math.round(this.editingFile.height * (newWidth / oldWidth)) || '';
        },
        'editingFile.height'(newHeight, oldHeight) {
            if (!this.$refs.height || this.$refs.height !== document.activeElement) {
                return;
            }
            this.editingFile.width = Math.round(this.editingFile.width * (newHeight / oldHeight)) || '';
        },
    },
    methods: {
        handleClick(editor) {
            const currentNode = editor.selection.getNode();

            if (currentNode.nodeName === 'IMG') {
                this.currentNode = currentNode;
                this.editingFile = {
                    alt: currentNode.alt || '',
                    width: currentNode.width,
                    height: currentNode.height,
                };
                this.showEditModal = true;
                return;
            }

            this.showMediaModal = true;
            this.editorInstance = editor;
            this.bookmark = this.editorInstance.selection.getBookmark();
        },
        endEditing() {
            this.currentNode.alt = this.editingFile.alt;
            this.currentNode.width = this.editingFile.width;
            const src = this.currentNode.src;
            const newSrc = `/media/image/resize/${this.editingFile.width}x${this.editingFile.height}/${src.substring(src.lastIndexOf('/')+1)}`;
            this.currentNode.src = newSrc;
            this.currentNode.dataset.mceSrc = newSrc;
            this.currentNode = null;
            this.editingFile = {};
            this.showEditModal = false;
        },
        insertMedia(file) {
            this.closeMediaModal();
            this.editorInstance.insertContent(this.formatMedia(file));
        },
        formatMedia(file) {
            if (file.type === 'image') {
                return `<img src="/media/image/${file.filename}" alt="${file.caption}" data-id="${file.id}" style="max-width: 100%">`;
            }
            return `<a href="/media/${file.type}/${file.filename}" target="_blank" data-id="${file.id}">${file.caption || file.filename}</a>`;
        },
        closeMediaModal() {
            this.showMediaModal = false;
            this.editorInstance.focus();
            this.editorInstance.selection.moveToBookmark(this.bookmark);
            this.dataTransfer = null;
        },
        drop(e, editor) {
            this.editorInstance = editor;
            this.editorInstance.focus();
            this.editorInstance.selection.select(e.target);
            this.editorInstance.selection.collapse(false);
            this.bookmark = this.editorInstance.selection.getBookmark();
            this.dataTransfer = e.dataTransfer;
            this.showMediaModal = true;
        },
    },
    mounted() {
        const replace = document.querySelector(`[name=${this.replace}]`);
        this.value = replace.value;
        this.name = this.replace;
        replace.remove();
        setupMediaPlugin(this);
    },
};
</script>

<style scoped>
    .tinymce img {
        max-width: 100%;
    }
</style>

<style>
    body {
        padding-top: 0 !important;
    }
    .navbar-fixed-top {
        position: static !important;
    }
</style>