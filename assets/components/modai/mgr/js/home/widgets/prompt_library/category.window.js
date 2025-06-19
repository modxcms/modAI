modAIAdmin.window.PromptLibraryCategories = function (config) {
  config = config || {};
  config.isUpdate = config.isUpdate || false;
  config.canCreatePublic = config.canCreatePublic || false;

  Ext.applyIf(config, {
    title: config.isUpdate ? _('modai.admin.prompt_library.category.update') : _('modai.admin.prompt_library.category.create'),
    closeAction: 'close',
    url: MODx.config.connector_url,
    action: config.isUpdate ? 'modAI\\Processors\\PromptLibrary\\Categories\\Update' : 'modAI\\Processors\\PromptLibrary\\Categories\\Create',
    modal: true,
    autoHeight: true,
    fields: this.getFields(config)
  });
  modAIAdmin.window.PromptLibraryCategories.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.window.PromptLibraryCategories, MODx.Window, {
  getFields: function (config) {
    return [
      {
        xtype: 'hidden',
        name: 'id'
      },
      {
        xtype: 'hidden',
        name: 'parent_id'
      },
      {
        xtype: 'textfield',
        fieldLabel: _('modai.admin.prompt_library.category.name'),
        name: 'name',
        anchor: '100%',
        allowBlank: false,
      },
      {
        xtype: 'modx-combo-boolean',
        fieldLabel: _('modai.admin.prompt_library.category.enabled'),
        name: 'enabled',
        hiddenName: 'enabled',
        anchor: '100%',
      },
      {
        xtype: 'modx-combo-boolean',
        fieldLabel: _('modai.admin.prompt_library.category.public'),
        name: 'public',
        hiddenName: 'public',
        anchor: '100%',
        disabled: !config.canCreatePublic
      },
      {
        xtype: 'modai-combo-prompt_library_category_type',
        fieldLabel: _('modai.admin.prompt_library.category.type'),
        name: 'type',
        hiddenName: 'type',
        anchor: '100%',
        allowBlank: false,
      },
    ];
  }
});
Ext.reg('modai-window-prompt_library_categories', modAIAdmin.window.PromptLibraryCategories);

