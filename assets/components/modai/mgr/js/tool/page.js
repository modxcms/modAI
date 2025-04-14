modAIAdmin.page.Tool = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-tool',
    buttons: this.getButtons(config),
    components: [
      {
        xtype: 'modai-panel-tool',
        isUpdate: config.isUpdate,
        record: config.record || {},
        permissions: config.permissions,
      }
    ]
  });
  modAIAdmin.page.Tool.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Tool, MODx.Component, {
  getButtons: function(config) {
    const buttons = [];

    if (config.permissions.modai_admin_tool_save) {
      buttons.push({
        text: _('save'),
        method: 'remote',
        cls:'primary-button',
        process: config.isUpdate ? 'modAI\\Processors\\Tools\\Update' : 'modAI\\Processors\\Tools\\Create',
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
Ext.reg('modai-page-tool', modAIAdmin.page.Tool);
