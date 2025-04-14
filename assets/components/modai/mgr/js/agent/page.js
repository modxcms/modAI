modAIAdmin.page.Agent = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-agent',
    buttons: this.getButtons(config),
    components: [
      {
        xtype: 'modai-panel-agent',
        isUpdate: config.isUpdate,
        record: config.record || {},
        permissions: config.permissions,
      }
    ]
  });
  modAIAdmin.page.Agent.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Agent, MODx.Component, {
  getButtons: function(config) {
    const buttons = [];

    if (config.permissions.modai_admin_agent_save) {
      buttons.push({
        text: _('save'),
        method: 'remote',
        cls:'primary-button',
        process: config.isUpdate ? 'modAI\\Processors\\Agents\\Update' : 'modAI\\Processors\\Agents\\Create',
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
Ext.reg('modai-page-agent', modAIAdmin.page.Agent);
