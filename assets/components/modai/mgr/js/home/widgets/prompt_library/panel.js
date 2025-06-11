modAIAdmin.panel.PromptLibrary = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  const grid = new modAIAdmin.grid.PromptLibraryPrompts({
    permissions: config.permissions
  });

  Ext.applyIf(config,{
    items: [{
      layout: 'column',
      border: false,
      cls: 'main-wrapper',
      items: [
        {
          columnWidth: .3,
          cls: 'left-col',
          border: false,
          layout: 'anchor',
          items: [
            {
              xtype: 'modai-tree-prompt_library_categories',
              permissions: config.permissions,
              grid,
            }
          ]
        },
        {
          columnWidth: .7,
          layout: 'form',
          border: false,
          autoHeight: true,
          items: [
            grid
          ]
        }
      ]
    }]
  });

  modAIAdmin.panel.PromptLibrary.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.panel.PromptLibrary, MODx.FormPanel, {

});
Ext.reg('modai-panel-prompt_library', modAIAdmin.panel.PromptLibrary);
