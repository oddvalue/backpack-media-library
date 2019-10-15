export default {
  /**
   * Get the paginated listing of media and folders
   *
   * @param {
   *   type: String 'image|pdf|misc'
   *   page: Int
   *   folderId: Int
   * } options
   */
  async index({
    type = 'image',
    page = 1,
    folderId = null,
    search = null,
    tags = [],
  } = {}, callback, errorCallback) {
    // try {
      const data = await (await window.axios.get('/admin/media/' + type, {
        params: {
          page: (page || ''),
          folder: (folderId || ''),
          search: (search && search.length >= 1 ? search : null),
          tags: tags,
        },
      })).data;
      callback(data);
  },

  async update(file, callback, errorCallback) {
    const formData = new FormData();
    formData.append('caption', file.caption || '');
    if (file.file) {
      formData.set("file", file.file, file.name);
    }
    if (file.hasOwnProperty('tags')) {
      formData.append('tags[]', '');
      file.tags.map(tag => {
        formData.append('tags[]', tag);
      });
    }
    if (file.hasOwnProperty('folder')) {
      formData.append('folder_id', file.folder.id || '');
    }
    if (file.hasOwnProperty('folder_id')) {
      formData.append('folder_id', file.folder_id || '');
    }

    try {
      const data = await (await window.axios.post('/admin/media/update/' + file.id, formData)).data;
      callback(data);
    } catch (error) {
      console.log(error); // eslint-disable-line
      errorCallback(error);
    }
  }
};
