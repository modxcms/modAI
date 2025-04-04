modAIAdmin.panel.Tool = function (config) {
  config = config || {};

  config.id = config.id || 'modai-panel-tool';

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
  }


  this.configSection = new Ext.Panel({
    defaults: {
      msgTarget: 'under',
      anchor: '100%'
    },
    layout: 'form',
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
      action: 'modAI\\Processors\\Tools\\Update'
    },
    items: this.getItems(config),
    listeners: {
      success: {
        fn: this.success,
        scope: this
      }
    }
  });

  modAIAdmin.panel.Tool.superclass.constructor.call(this, config);
};

Ext.extend(modAIAdmin.panel.Tool, MODx.FormPanel, {
  configSection: null,

  success: function (o, r) {
    if (this.config.isUpdate === false) {
      modAIAdmin.loadPage('tool/update', { id: o.result.object.id });
    }
  },

  getItems: function (config) {
    return [
      MODx.util.getHeaderBreadCrumbs({
        html: ((config.isUpdate === true) ? _('modai.admin.tool.update') : _('modai.admin.tool.create')),
        xtype: "modx-header"
      }, [
        {
          text: _('modai.admin.home.page_title'),
          href: '?a=home&namespace=modai',
        },
        {
          text: _('modai.admin.home.tools'),
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
                title: _('modai.admin.tool.tool'),
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
                    fieldLabel: _('modai.admin.tool.class'),
                    xtype: 'modai-combo-tool_class',
                    value: config.record.class,
                    listeners: {
                      select: (self, record) => {
                        const name = this.getForm().findField('name');
                        if (name && !name.getValue()) {
                          name.setValue(record.data.suggestedName);
                        }

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
                    fieldLabel: _('modai.admin.tool.name'),
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
                    allowBlank: true
                  }
                ]
              },

              config.isUpdate && {
                title: _('modai.admin.tool.agents'),
                headerCfg: {
                  cls: 'modai-admin-section_header x-panel-header',
                },
                style: {
                  marginTop: '20px'
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
                    xtype: 'modai-grid-related_agents',
                    relatedObject: {
                      tool_id: MODx.request.id
                    }
                  }
                ]
              },
            ]
          },
          {
            columnWidth: .4,
            border: false,
            items: [
              {
                title: _('modai.admin.tool.config'),
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
                items: [
                  {
                    fieldLabel: _('modai.admin.tool.enabled'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'enabled',
                    hiddenName: 'enabled',
                    value: config.record.enabled ?? 1,
                  },
                  {
                    fieldLabel: _('modai.admin.tool.default'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'default',
                    hiddenName: 'default',
                    value: config.record.default ?? 0,
                  },
                  this.configSection,
                ]
              }
            ]
          }
        ]
      }
    ];
  }
});
Ext.reg('modai-panel-tool', modAIAdmin.panel.Tool);
