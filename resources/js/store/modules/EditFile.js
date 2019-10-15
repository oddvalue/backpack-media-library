import mediaApi from '../../api/media';

const state = {
  file: {},
  originalFile: {},
  updatedFile: {},
  error: null,
  hideFields: [],
};

const mutations = {
  editFile(state, file) {
    state.file = file;
  },
  setHiddenFields(state, fields) {
    state.hideFields = fields;
  },
  endEditing(state) {
    state.file = {};
  },
  storeOriginal(state, file) {
    state.originalFile = file;
  },
  storeUpdated(state, file) {
    state.updatedFile = file;
  },
  setError(state, error) {
    state.error = error;
  },
  clearError(state) {
    state.error = null;
  },
};

const actions = {
  update({ commit, dispatch }, file) {
    mediaApi.update(
      file,
      data => {
        commit('storeOriginal', data.original);
        commit('endEditing');
        dispatch('MediaList/refresh', null, { root: true });
      },
      error => commit('setError', error)
    );
  },
  edit({ commit }, { file, hiddenFields }) {
    commit('clearError');
    commit('editFile', file);
    commit('setHiddenFields', hiddenFields || []);
  },
  end({ commit }) {
    commit('endEditing');
  },
  setUpdatedFile({ commit }, file) {
    commit('storeUpdated', file);
    commit('endEditing');
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
