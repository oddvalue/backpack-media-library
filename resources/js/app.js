import './bootstrap';

import Vue from 'vue';
import Toasted from 'vue-toasted';
// import { loadProgressBar } from 'axios-progress-bar';

import BozUploader from './components/Uploader';
import BozMedia from './components/MediaBrowser';
import MediaModal from './components/MediaModal';
import EditModal from './components/EditModal';
import TinymceEditor from './components/TinymceEditor';

import store from './store';

/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
* building robust, powerful web applications using Vue and Laravel.
*/

window.Vue = Vue;
window.vuexStore = store;

/**
 * Next, we will create a fresh Vue application instance and attach it to
* the page. Then, you may begin adding components to this application
* or customize the JavaScript scaffolding to fit your unique needs.
*/

Vue.use(Toasted);

Vue.filter('formatSize', function (size) {
  if (size > 1024 * 1024 * 1024 * 1024) {
    return (size / 1024 / 1024 / 1024 / 1024).toFixed(2) + ' TB'
  } else if (size > 1024 * 1024 * 1024) {
    return (size / 1024 / 1024 / 1024).toFixed(2) + ' GB'
  } else if (size > 1024 * 1024) {
    return (size / 1024 / 1024).toFixed(2) + ' MB'
  } else if (size > 1024) {
    return (size / 1024).toFixed(2) + ' KB'
  }
  return size.toString() + ' B'
});

new Vue({
  el: '#app',
  store,
  components: {
    BozUploader,
    BozMedia,
    MediaModal,
    EditModal,
    TinymceEditor,
  },
});

const confirmExitIfModified = (function() {
  let formIsDirty = false;

  return function(form, message) {
    form.addEventListener('change', () => formIsDirty = true);
    form.addEventListener('keydown', () => formIsDirty = true);
    form.addEventListener('submit', () => formIsDirty = false);
    window.onbeforeunload = function(e) {
      e = e || window.event;
      if (formIsDirty) {
        // For IE and Firefox
        if (e) {
          e.returnValue = message;
        }
        // For Safari
        return message;
      }
    };
  };
})();
const form = document.querySelector('.js-warn-unsaved');
if (form) {
  confirmExitIfModified(form, 'You have unsaved changes, are you sure you wish to exit this page?');
}