modAIAdmin.panel.Agent = function (config) {
  config = config || {};

  config.id = config.id || 'modai-panel-agent';

  this.advancedConfigField = new Ext.form.Hidden({
    name: 'advanced_config',
  });
  this.advancedConfig = new modAIAdmin.grid.AdvancedConfig({
    fieldLabel: _('modai.admin.agent.advanced_config'),
    initValue: config.record.advanced_config || [],
  });

  Ext.applyIf(config, {
    border: false,
    cls: 'container',
    baseCls: 'modx-formpanel',
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\Agents\\Update',
    },
    items: this.getItems(config),
    listeners: {
      success: {
        fn: this.success,
        scope: this,
      },
    },
  });

  modAIAdmin.panel.Agent.superclass.constructor.call(this, config);

  this.on(
    'beforeSubmit',
    function () {
      this.advancedConfigField.setValue(this.advancedConfig.encode());
    },
    this,
  );
};

Ext.extend(modAIAdmin.panel.Agent, MODx.FormPanel, {
  advancedConfig: null,
  advancedConfigField: null,

  success: function (o, r) {
    if (this.config.isUpdate === false) {
      modAIAdmin.loadPage('agent/update', { id: o.result.object.id });
    }
  },

  getItems: function (config) {
    return [
      MODx.util.getHeaderBreadCrumbs(
        {
          html:
            config.isUpdate === true
              ? _('modai.admin.agent.update')
              : _('modai.admin.agent.create'),
          xtype: 'modx-header',
        },
        [
          {
            text: _('modai.admin.home.page_title'),
            href: '?a=home&namespace=modai',
          },
          {
            text: _('modai.admin.home.agents'),
            href: null,
          },
        ],
      ),
      {
        name: 'id',
        xtype: 'hidden',
        value: config.record.id,
      },
      this.advancedConfigField,
      this.getGeneralFields(config),
      config.isUpdate && this.getUpdateFields(config),
    ].filter(Boolean);
  },

  getGeneralFields: function (config) {
    return [
      {
        layout: 'column',
        border: false,
        anchor: '100%',
        style: {
          marginTop: '30px',
        },
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
            columnWidth: 0.5,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%',
            },
            items: [
              {
                title: _('modai.admin.agent.agent'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%',
                },
                layout: 'form',
                msgTarget: 'under',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                collapsible: true,
                hideMode: 'offsets',
                items: [
                  {
                    fieldLabel: _('modai.admin.agent.name'),
                    xtype: 'textfield',
                    name: 'name',
                    msgTarget: 'under',
                    allowBlank: false,
                    value: config.record.name,
                  },
                  {
                    fieldLabel: _('modai.admin.agent.description'),
                    xtype: 'textarea',
                    name: 'description',
                    msgTarget: 'under',
                    value: config.record.description,
                    allowBlank: true,
                  },
                ],
              },
            ],
          },
          {
            columnWidth: 0.5,
            border: false,
            items: [
              {
                title: _('modai.admin.agent.config'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%',
                },
                layout: 'form',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                collapsible: true,
                hideMode: 'offsets',
                items: [
                  {
                    fieldLabel: _('modai.admin.agent.enabled'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'enabled',
                    hiddenName: 'enabled',
                    value: config.record.enabled ?? 1,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.agent.enabled_desc'),
                    cls: 'desc-under',
                  },
                  {
                    fieldLabel: _('modai.admin.agent.model'),
                    xtype: 'textfield',
                    name: 'model',
                    msgTarget: 'under',
                    value: config.record.model,
                    allowBlank: true,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.agent.model_desc'),
                    cls: 'desc-under',
                  },
                  {
                    fieldLabel: _('modai.admin.agent.prompt'),
                    xtype: 'textarea',
                    name: 'prompt',
                    msgTarget: 'under',
                    value: config.record.prompt,
                    allowBlank: true,
                    grow: true,
                    growMax: 300,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.agent.prompt_desc'),
                    cls: 'desc-under',
                  },
                  this.advancedConfig,
                  {
                    xtype: 'label',
                    html: _('modai.admin.agent.advanced_config_desc'),
                    cls: 'desc-under',
                  },
                ],
              },
            ],
          },
        ],
      },
    ];
  },

  getUpdateFields: function (config) {
    return [
      {
        layout: 'column',
        border: false,
        anchor: '100%',
        style: {
          marginTop: '30px',
        },
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
            columnWidth: 0.5,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%',
            },
            items: [
              {
                title: _('modai.admin.agent.tools'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%',
                },
                layout: 'form',
                msgTarget: 'under',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                collapsible: true,
                hideMode: 'offsets',
                items: [
                  {
                    xtype: 'modai-grid-agent_tools',
                  },
                ],
              },
            ],
          },
          {
            columnWidth: 0.5,
            border: false,
            items: [
              {
                title: _('modai.admin.agent.context_providers'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%',
                },
                layout: 'form',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                collapsible: true,
                hideMode: 'offsets',
                items: [
                  {
                    xtype: 'modai-grid-agent_context_providers',
                  },
                ],
              },
            ],
          },
        ],
      },
    ];
  },
});
Ext.reg('modai-panel-agent', modAIAdmin.panel.Agent);
