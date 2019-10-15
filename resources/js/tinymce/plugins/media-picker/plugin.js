export default (component) => {
  tinymce.PluginManager.add('mediabrowser', function (editor, url) {
    var openDialog = function () {
      return component.handleClick(editor);
    };

    // Add a button that opens a window
    editor.ui.registry.addButton('mediabrowser', {
      text: '<svg width="24" height="24"><path d="M5 15.7l3.3-3.2c.3-.3.7-.3 1 0L12 15l4.1-4c.3-.4.8-.4 1 0l2 1.9V5H5v10.7zM5 18V19h3l2.8-2.9-2-2L5 17.9zm14-3l-2.5-2.4-6.4 6.5H19v-4zM4 3h16c.6 0 1 .4 1 1v16c0 .6-.4 1-1 1H4a1 1 0 0 1-1-1V4c0-.6.4-1 1-1zm6 8a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" fill-rule="nonzero"></path></svg>',
      onAction: function () {
        // Open window
        openDialog();
      }
    });

    // Adds a menu item, which can then be included in any menu via the menu/menubar configuration
    editor.ui.registry.addMenuItem('mediabrowser', {
      text: 'Media Browser',
      onAction: function () {
        // Open window
        openDialog();
      }
    });

    return {
      getMetadata: function () {
        return {
          name: "Media Browser",
          url: "/admin/media"
        };
      }
    };
  });
};