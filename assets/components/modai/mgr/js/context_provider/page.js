modAIAdmin.page.ContextProvider = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-context_provider',
    buttons: this.getButtons(config),
    components: [
      {
        xtype: 'modai-panel-context_provider',
        isUpdate: config.isUpdate,
        record: config.record || {},
        permissions: config.permissions,
      }
    ]
  });
  modAIAdmin.page.ContextProvider.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.ContextProvider, MODx.Component, {
  getButtons: function(config) {
    const buttons = [];

    if (config.permissions.modai_admin_context_provider_save) {
      buttons.push({
        text: _('save'),
        method: 'remote',
        cls: 'primary-button',
        process: config.isUpdate ? 'modAI\\Processors\\ContextProviders\\Update' : 'modAI\\Processors\\ContextProviders\\Create',
        keys: [
          {
            key: MODx.config.keymap_save || 's',
            ctrl: true
          }
        ]
      });
    }

    buttons.push({
      text: _('cancel'),
      params: {
        a: 'home',
        namespace: 'modai'
      }
    });

    return buttons;
  }
});
Ext.reg('modai-page-context_provider', modAIAdmin.page.ContextProvider);
