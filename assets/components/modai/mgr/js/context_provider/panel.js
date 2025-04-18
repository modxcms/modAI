modAIAdmin.panel.ContextProvider = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  config.id = config.id || 'modai-panel-context_provider';

  const configItems = [];

  if (config.record.classConfig && config.record.config) {
    Object.entries(config.record.classConfig).map(([key, cfg]) => {
      configItems.push(...modAIAdmin.formatConfigItem(key, cfg, config.record.config[key]));
    });
  }

  this.configSection = new Ext.Panel({
    defaults: {
      msgTarget: 'under',
      anchor: '100%',
    },
    layout: 'form',
    autoHeight: true,
    hideMode: 'offsets',
    items: configItems,
  });

  Ext.applyIf(config, {
    border: false,
    cls: 'container',
    baseCls: 'modx-formpanel',
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\ContextProviders\\Update',
    },
    items: this.getItems(config),
    listeners: {
      success: {
        fn: this.success,
        scope: this,
      },
    },
  });

  modAIAdmin.panel.ContextProvider.superclass.constructor.call(this, config);
};

Ext.extend(modAIAdmin.panel.ContextProvider, MODx.FormPanel, {
  configSection: null,

  success: function (o, r) {
    if (this.config.isUpdate === false) {
      modAIAdmin.loadPage('context_provider/update', { id: o.result.object.id });
    }
  },

  getItems: function (config) {
    return [
      MODx.util.getHeaderBreadCrumbs(
        {
          html:
            config.isUpdate === true
              ? _('modai.admin.context_provider.update')
              : _('modai.admin.context_provider.create'),
          xtype: 'modx-header',
        },
        [
          {
            text: _('modai.admin.home.page_title'),
            href: '?a=home&namespace=modai',
          },
          {
            text: _('modai.admin.home.context_providers'),
            href: null,
          },
        ],
      ),
      {
        name: 'id',
        xtype: 'hidden',
        value: config.record.id,
      },
      this.getGeneralFields(config),
    ];
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
            columnWidth: 0.6,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%',
            },
            items: [
              {
                title: _('modai.admin.context_provider.context_provider'),
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
                hideMode: 'offsets',
                items: [
                  {
                    fieldLabel: _('modai.admin.context_provider.class'),
                    xtype: 'modai-combo-context_provider_class',
                    value: config.record.class,
                    listeners: {
                      beforeselect: (self, record) => {
                        const name = this.getForm().findField('name');
                        if (name && (self.getValue() !== record.data.class || !name.getValue())) {
                          name.setValue(record.data.suggestedName);
                        }

                        const description = this.getForm().findField('description');
                        if (description && (self.getValue() !== record.data.class || !description.getValue())) {
                          description.setValue(record.data.description);
                        }

                        this.configSection.removeAll();
                        Object.entries(record.data.config).forEach(([key, config]) => {
                          this.configSection.add(modAIAdmin.formatConfigItem(key, config));
                        });

                        this.configSection.doLayout();
                      },
                    },
                  },
                  {
                    fieldLabel: _('modai.admin.context_provider.name'),
                    xtype: 'textfield',
                    name: 'name',
                    msgTarget: 'under',
                    allowBlank: false,
                    value: config.record.name,
                  },
                  {
                    fieldLabel: _('modai.admin.context_provider.description'),
                    xtype: 'textarea',
                    name: 'description',
                    msgTarget: 'under',
                    value: config.record.description,
                    allowBlank: true,
                  },
                ],
              },

              (config.permissions.modai_admin_agents && config.isUpdate) && {
                title: _('modai.admin.context_provider.agents'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                style: {
                  marginTop: '20px',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%',
                },
                layout: 'form',
                msgTarget: 'under',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                hideMode: 'offsets',
                items: [
                  {
                    xtype: 'modai-grid-related_agents',
                    permissions: config.permissions,
                    relatedObject: {
                      context_provider_id: MODx.request.id,
                    },
                  },
                ],
              },
            ],
          },
          {
            columnWidth: 0.4,
            border: false,
            items: [
              {
                title: _('modai.admin.context_provider.config'),
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
                hideMode: 'offsets',
                items: [
                  {
                    fieldLabel: _('modai.admin.context_provider.enabled'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'enabled',
                    hiddenName: 'enabled',
                    value: config.record.enabled ?? 1,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.context_provider.enabled_desc'),
                    cls: 'desc-under',
                  },
                  this.configSection,
                ],
              },
            ],
          },
        ],
      },
    ];
  },
});
Ext.reg('modai-panel-context_provider', modAIAdmin.panel.ContextProvider);
