modAIAdmin.grid.AgentContextProviders = function (config) {
  config = config || {};

  Ext.applyIf(config, {
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\ContextProviders\\GetList',
      agent: MODx.request.id,
    },
    preventSaveRefresh: false,
    fields: ['id', 'name', 'description', 'enabled'],
    paging: true,
    remoteSort: true,
    emptyText: _('modai.admin.global.no_records'),
    columns: [
      {
        header: _('id'),
        dataIndex: 'id',
        width: 0.05,
        sortable: true,
        hidden: true,
      },
      {
        header: _('modai.admin.context_provider.name'),
        dataIndex: 'name',
        width: 0.2,
        sortable: true,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.context_provider.description'),
        dataIndex: 'description',
        width: 0.6,
        sortable: true,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.context_provider.enabled'),
        dataIndex: 'enabled',
        renderer: this.rendYesNo,
        width: 0.1,
        hidden: false,
      },
    ],
    tbar: this.getTbar(config),
  });
  modAIAdmin.grid.AgentContextProviders.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.AgentContextProviders, MODx.grid.Grid, {
  getMenu: function () {
    var m = [];

    m.push({
      text: _('modai.admin.agent_context_provider.view'),
      handler: this.viewContextProvider,
    });

    m.push('-');

    m.push({
      text: _('modai.admin.agent_context_provider.remove'),
      handler: this.removeContextProvider,
    });

    return m;
  },

  getTbar: function (config) {
    return [
      {
        text: _('modai.admin.agent_context_provider.create'),
        handler: this.createContextProvider,
      },
      '->',
      {
        xtype: 'textfield',
        emptyText: _('modai.admin.context_provider.search'),
        listeners: {
          change: {
            fn: this.search,
            scope: this,
          },
          render: {
            fn: function (cmp) {
              new Ext.KeyMap(cmp.getEl(), {
                key: Ext.EventObject.ENTER,
                fn: function () {
                  this.blur();
                  return true;
                },
                scope: cmp,
              });
            },
            scope: this,
          },
        },
      },
    ];
  },

  createContextProvider: function (btn, e) {
    const record = {
      agent_id: MODx.request.id,
    };

    const win = MODx.load({
      xtype: 'modai-window-agent_context_providers',
      record: record,
      listeners: {
        success: {
          fn: function () {
            this.refresh();
          },
          scope: this,
        },
      },
    });

    win.fp.getForm().setValues(record);
    win.show(e.target);

    return true;
  },

  removeContextProvider: function (btn, e) {
    if (!this.menu.record) return false;

    MODx.msg.confirm({
      title: _('modai.admin.agent_context_provider.remove'),
      text: _('modai.admin.agent_context_provider.remove_confirm', { name: this.menu.record.name }),
      url: this.config.url,
      params: {
        action: 'modAI\\Processors\\AgentContextProviders\\Remove',
        agent_id: MODx.request.id,
        context_provider_id: this.menu.record.id,
      },
      listeners: {
        success: {
          fn: function (r) {
            this.refresh();
          },
          scope: this,
        },
      },
    });

    return true;
  },

  viewContextProvider: function (btn, e) {
    modAIAdmin.loadPage('context_provider/update', { id: this.menu.record.id });
  },

  filterCombo: function (combo, record) {
    const s = this.getStore();
    s.baseParams[combo.filterName] = record.data[combo.valueField];
    this.getBottomToolbar().changePage(1);
  },

  search: function (field, value) {
    const s = this.getStore();
    s.baseParams.search = value;
    this.getBottomToolbar().changePage(1);
  },
});
Ext.reg('modai-grid-agent_context_providers', modAIAdmin.grid.AgentContextProviders);
