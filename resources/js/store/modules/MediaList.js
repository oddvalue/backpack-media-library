import mediaApi from '../../api/media';

const state = {
  data: {
    data: {},
    folders: [],
    pagination: {},
  },
  loading: false,
  params: {
    type: null,
    folderId: null,
    page: 1,
    search: null,
    tags: [],
  },
  error: null,
};

const mutations = {
  setList(state, data) {
    state.data = data;
  },
  setParams(state, params) {
    state.params = {...state.params, ...params};
  },
  setPage(state, page) {
    state.params.page = page;
  },
  setError(state, error) {
    state.error = error;
  },
  clearError(state) {
    state.error = null;
  },
  setLoading(state, value) {
    state.loading = value;
  },
};

const actions = {
  get({ commit, dispatch }, params) {
    commit('setParams', params);
    dispatch('refresh');
  },
  refresh({ commit, state }) {
    if (!state.params.type) {
      return;
    }
    commit('setLoading', true);
    mediaApi.index(
      state.params,
      data => commit('setList', data) + commit('setLoading', false),
      error => commit('setError', error) + commit('setLoading', false)
    );
  },
  setPage({ commit, dispatch }, { current_page }) {
    commit('setPage', current_page);
    dispatch('refresh');
  },
};

export default {
  namespaced: true,
  state,
  mutations,
  actions,
};
