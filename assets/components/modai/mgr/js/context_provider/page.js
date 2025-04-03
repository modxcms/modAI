modAIAdmin.page.Element = function (config) {
  config = config || {};

  config.isUpdate = (MODx.request.id) ? true : false;

  Ext.applyIf(config, {
    formpanel: 'modai-panel-context_provider',
    buttons: [
      {
        text: _('save'),
        method: 'remote',
        cls:'primary-button',
        process: config.isUpdate ? 'modAI\\Processors\\ContextProviders\\Update' : 'modAI\\Processors\\ContextProviders\\Create',
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
        xtype: 'modai-panel-context_provider',
        isUpdate: config.isUpdate,
        record: config.record || {},
      }
    ]
  });
  modAIAdmin.page.Element.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Element, MODx.Component);
Ext.reg('modai-page-context_provider', modAIAdmin.page.Element);
