modAIAdmin.panel.Tool = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};

  config.id = config.id || 'modai-panel-tool';

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

  this.promptField = new Ext.form.TextArea({
    fieldLabel: _('modai.admin.tool.prompt'),
    name: 'prompt',
    msgTarget: 'under',
    value: config.record.prompt || config.record.defaultPrompt,
    disabled: !config.record.prompt,
    allowBlank: true,
    grow: true,
    defaultPrompt: config.record.defaultPrompt,
    editBtn: null,
    resetBtn: null,
    renderEditButton: function() {
      if (this.editBtn) {
        return;
      }

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'modai-admin-label_button';
      btn.title = 'Edit Prompt';
      btn.addEventListener('click', () => {
        this.enable();
        btn.remove();
        this.editBtn = null;

        this.renderResetButton();
      });

      const i = document.createElement('i');
      i.className = 'icon icon-edit';
      btn.appendChild(i);

      this.editBtn = btn;
      this.label.dom.append(btn);
    },
    renderResetButton: function() {
      if (this.resetBtn) {
        return;
      }

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'modai-admin-label_button';
      btn.title = 'Reset to the default Prompt';
      btn.addEventListener('click', () => {
        this.setValue(this.defaultPrompt)
        this.disable();
        btn.remove();
        this.resetBtn = null;

        this.renderEditButton();
      });

      const i = document.createElement('i');
      i.className = 'icon icon-refresh';
      btn.appendChild(i);

      this.resetBtn = btn;
      this.label.dom.append(btn);
    },
    initPrompt: function(prompt, defaultPrompt) {
      this.defaultPrompt = defaultPrompt;
      if (this.editBtn) {
        this.editBtn.remove();
        this.editBtn = null;
      }

      if (this.resetBtn) {
        this.resetBtn.remove();
        this.resetBtn = null;
      }

      if (prompt) {
        this.setValue(prompt);
        this.renderResetButton();
        return;
      }

      this.disable();
      this.setValue(this.defaultPrompt);
      this.renderEditButton();
    },
    listeners: {
      afterrender: function () {
        this.initPrompt(config.record.prompt, config.record.defaultPrompt);
      }
    }
  });

  Ext.applyIf(config, {
    border: false,
    cls: 'container',
    baseCls: 'modx-formpanel',
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\Tools\\Update',
    },
    items: this.getItems(config),
    listeners: {
      success: {
        fn: this.success,
        scope: this,
      },
    },
  });

  modAIAdmin.panel.Tool.superclass.constructor.call(this, config);
};

Ext.extend(modAIAdmin.panel.Tool, MODx.FormPanel, {
  configSection: null,
  promptField: null,

  success: function (o, r) {
    if (this.config.isUpdate === false) {
      modAIAdmin.loadPage('tool/update', { id: o.result.object.id });
    }
  },

  getItems: function (config) {
    return [
      MODx.util.getHeaderBreadCrumbs(
        {
          html:
            config.isUpdate === true ? _('modai.admin.tool.update') : _('modai.admin.tool.create'),
          xtype: 'modx-header',
        },
        [
          {
            text: _('modai.admin.home.page_title'),
            href: '?a=home&namespace=modai',
          },
          {
            text: _('modai.admin.home.tools'),
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
                title: _('modai.admin.tool.tool'),
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
                    fieldLabel: _('modai.admin.tool.class'),
                    xtype: 'modai-combo-tool_class',
                    value: config.record.class,
                    listeners: {
                      beforeselect: (self, record) => {
                        const name = this.getForm().findField('name');
                        if (name && (self.getValue() !== record.data.class || !name.getValue())) {
                          name.setValue(record.data.suggestedName);
                        }

                        const description = this.getForm().findField('description');
                        if (description && (self.getValue() !== record.data.class ||!description.getValue())) {
                          description.setValue(record.data.description);
                        }

                        this.promptField.initPrompt(undefined, record.data.defaultPrompt);

                        this.configSection.removeAll();
                        Object.entries(record.data.config).forEach(([key, config]) => {
                          this.configSection.add(modAIAdmin.formatConfigItem(key, config));
                        });

                        this.configSection.doLayout();
                      },
                    },
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
                    fieldLabel: _('modai.admin.tool.description'),
                    xtype: 'textarea',
                    name: 'description',
                    msgTarget: 'under',
                    value: config.record.description,
                    allowBlank: true,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.tool.description_desc'),
                    cls: 'desc-under',
                  },
                  this.promptField,
                  {
                    xtype: 'label',
                    html: _('modai.admin.tool.prompt_desc'),
                    cls: 'desc-under',
                  },
                ],
              },

              (config.permissions.modai_admin_agents && config.isUpdate) && {
                title: _('modai.admin.tool.agents'),
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
                      tool_id: MODx.request.id,
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
                title: _('modai.admin.tool.config'),
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
                    fieldLabel: _('modai.admin.tool.enabled'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'enabled',
                    hiddenName: 'enabled',
                    value: config.record.enabled ?? 1,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.tool.enabled_desc'),
                    cls: 'desc-under',
                  },
                  {
                    fieldLabel: _('modai.admin.tool.default'),
                    xtype: 'modai-combo-boolean',
                    useInt: true,
                    name: 'default',
                    hiddenName: 'default',
                    value: config.record.default ?? 0,
                  },
                  {
                    xtype: 'label',
                    html: _('modai.admin.tool.default_desc'),
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
Ext.reg('modai-panel-tool', modAIAdmin.panel.Tool);
