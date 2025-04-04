modAIAdmin.page.Agent = function (config) {
  config = config || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-agent',
    buttons: [
      {
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
      },
      {
        text: _('cancel'),
        params: {
          a: 'home',
          namespace: 'modai'
        }
      }
    ],
    components: [
      {
        xtype: 'modai-panel-agent',
        isUpdate: config.isUpdate,
        record: config.record || {},
      }
    ]
  });
  modAIAdmin.page.Agent.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Agent, MODx.Component);
Ext.reg('modai-page-agent', modAIAdmin.page.Agent);
