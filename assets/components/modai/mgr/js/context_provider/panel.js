modAIAdmin.panel.ContextProvider = function (config) {
  config = config || {};

  config.id = config.id || 'modai-panel-context_provider';

  const configItems = [];

  if (config.record.classConfig) {
    Object.entries(config.record.classConfig).map((([key, cfg]) => {
      configItems.push({
        fieldLabel: cfg.name,
        allowBlank: !cfg.required,
        xtype: cfg.type,
        name: `config_${key}`,
        value: config.record.config[key]
      });

      configItems.push({
        xtype: 'label',
        html: cfg.description,
        cls: 'desc-under'
      });
    }));
  } else {
    configItems.push({
      html: 'Select Context Provider Class to configure it.'
    });
  }


  this.configSection = new Ext.Panel({
    title: "Config",
    headerCfg: {
      cls: 'modai-admin-section_header x-panel-header',
    },
    defaults: {
      msgTarget: 'under',
      anchor: '100%'
    },
    layout: 'form',
    bodyCssClass: 'main-wrapper',
    autoHeight: true,
    hideMode: 'offsets',
    items: configItems
  });

  Ext.applyIf(config, {
    border: false,
    cls: 'container',
    baseCls: 'modx-formpanel',
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\ContextProviders\\Update'
    },
    items: this.getItems(config),
    listeners: {
      success: {
        fn: this.success,
        scope: this
      }
    }
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
      MODx.util.getHeaderBreadCrumbs({
        html: ((config.isUpdate === true) ? _('modai.admin.context_provider.update') : _('modai.admin.context_provider.create')),
        xtype: "modx-header"
      }, [
        {
          text: _('modai.admin.home.page_title'),
          href: '?a=home&namespace=modai',
        },
        {
          text: _('modai.admin.home.context_providers'),
          href: null,
        }
      ]),
      {
        name: 'id',
        xtype: 'hidden',
        value: config.record.id,
      },
      this.getGeneralFields(config)
    ];
  },

  getGeneralFields: function (config) {
    return [
      {
        layout: 'column',
        border: false,
        anchor: '100%',
        style: {
          marginTop: '30px'
        },
        defaults: {
          layout: 'form',
          labelAlign: 'top',
          labelSeparator: '',
          anchor: '100%',
          msgTarget: 'under',
          border: false
        },
        items: [
          {
            columnWidth: .6,
            border: false,
            defaults: {
              msgTarget: 'under',
              anchor: '100%'
            },
            items: [
              {
                title: "Context Provider",
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                defaults: {
                  msgTarget: 'under',
                  anchor: '100%'
                },
                layout: 'form',
                msgTarget: 'under',
                bodyCssClass: 'main-wrapper',
                autoHeight: true,
                hideMode: 'offsets',
                items: [
                  {
                    fieldLabel: 'Context Provider Class',
                    xtype: 'modai-combo-context_provider_class',
                    value: config.record.class,
                    listeners: {
                      select: (self, record) => {
                        this.configSection.removeAll();
                        Object.entries(record.data.config).forEach(([key, config]) => {
                          this.configSection.add({
                            fieldLabel: config.name,
                            allowBlank: !config.required,
                            xtype: config.type,
                            name: `config_${key}`,
                          });

                          this.configSection.add({
                            xtype: 'label',
                            html: config.description,
                            cls: 'desc-under'
                          });
                        })

                        this.configSection.doLayout();
                      }
                    }
                  },
                  {
                    fieldLabel: 'Name',
                    xtype: 'textfield',
                    name: 'name',
                    msgTarget: 'under',
                    allowBlank: false,
                    value: config.record.name,
                  },
                  {
                    fieldLabel: 'Description',
                    xtype: 'textarea',
                    name: 'description',
                    msgTarget: 'under',
                    value: config.record.description,
                    allowBlank: true
                  },
                  {
                    fieldLabel: 'Enabled',
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'enabled',
                    hiddenName: 'enabled',
                    value: config.record.enabled ?? 1,
                  }
                ]
              },
            ]
          },
          {
            columnWidth: .4,
            border: false,
            items: [
              this.configSection,
            ]
          }
        ]
      }
    ];
  }
});
Ext.reg('modai-panel-context_provider', modAIAdmin.panel.ContextProvider);
