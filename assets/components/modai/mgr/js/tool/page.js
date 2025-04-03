modAIAdmin.page.Tool = function (config) {
  config = config || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-tool',
    buttons: [
      {
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
        xtype: 'modai-panel-tool',
        isUpdate: config.isUpdate,
        record: config.record || {},
      }
    ]
  });
  modAIAdmin.page.Tool.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Tool, MODx.Component);
Ext.reg('modai-page-tool', modAIAdmin.page.Tool);
