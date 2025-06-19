modAIAdmin.window.PromptLibraryPrompts = function (config) {
  config = config || {};
  config.isUpdate = config.isUpdate || false;
  config.canCreatePublic = config.canCreatePublic || false;

  Ext.applyIf(config, {
    title: config.isUpdate ? _('modai.admin.prompt_library.prompt.update') : _('modai.admin.prompt_library.prompt.create'),
    closeAction: 'close',
    url: MODx.config.connector_url,
    action: config.isUpdate ? 'modAI\\Processors\\PromptLibrary\\Prompts\\Update' : 'modAI\\Processors\\PromptLibrary\\Prompts\\Create',
    modal: true,
    autoHeight: true,
    width: 800,
    fields: this.getFields(config)
  });
  modAIAdmin.window.PromptLibraryPrompts.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.window.PromptLibraryPrompts, MODx.Window, {
  getFields: function (config) {
    return [
      {
        xtype: 'hidden',
        name: 'id'
      },
      {
        xtype: 'hidden',
        name: 'category_id'
      },
      {
        xtype: 'textfield',
        fieldLabel: _('modai.admin.prompt_library.prompt.name'),
        name: 'name',
        anchor: '100%',
        allowBlank: false
      },
      {
        xtype: 'modai-combo-prompt_library_category',
        fieldLabel: _('modai.admin.prompt_library.prompt.category'),
        name: 'category_id',
        hiddenName: 'category_id',
        anchor: '100%',
        allowBlank: false
      },
      {
        layout: 'column',
        border: false,
        anchor: '100%',
        defaults: {
          layout: 'form',
          labelAlign: 'top',
          labelSeparator: '',
          anchor: '100%',
          msgTarget: 'under',
          border: false,
        },
        items: [
          {
            columnWidth: 0.33,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%',
            },
            items: [
              {
                xtype: 'modx-combo-boolean',
                fieldLabel: _('modai.admin.prompt_library.prompt.enabled'),
                name: 'enabled',
                hiddenName: 'enabled',
                anchor: '100%',
              },
            ],
          },
          {
            columnWidth: 0.33,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%',
            },
            items: [
              {
                xtype: 'modx-combo-boolean',
                fieldLabel: _('modai.admin.prompt_library.prompt.public'),
                name: 'public',
                hiddenName: 'public',
                anchor: '100%',
                disabled: !config.canCreatePublic
              },
            ],
          },
          {
            columnWidth: 0.33,
            border: false,
            items: [
              {
                xtype: 'numberfield',
                allowDecimals: false,
                fieldLabel: _('modai.admin.prompt_library.prompt.rank'),
                name: 'rank',
                anchor: '100%',
              },
            ],
          },
        ],
      },
      {
        xtype: 'textarea',
        fieldLabel: _('modai.admin.prompt_library.prompt.prompt'),
        grow: true,
        growMin: 100,
        growMax: 300,
        name: 'prompt',
        anchor: '100%',
        allowBlank: false
      },
    ];
  }
});
Ext.reg('modai-window-prompt_library_prompts', modAIAdmin.window.PromptLibraryPrompts);

