modAIAdmin.grid.Agents = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};
  config.permission_item = 'agent';

  Ext.applyIf(config, {
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\Agents\\GetList',
    },
    save_action: 'modAI\\Processors\\Agents\\UpdateFromGrid',
    autosave: true,
    preventSaveRefresh: false,
    fields: ['id', 'name', 'description', 'model', 'enabled'],
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
        header: _('modai.admin.agent.name'),
        dataIndex: 'name',
        width: 0.2,
        sortable: true,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },  {
        header: _('modai.admin.agent.description'),
        dataIndex: 'description',
        width: 0.7,
        sortable: true,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.agent.model'),
        dataIndex: 'model',
        width: 0.2,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.agent.enabled'),
        dataIndex: 'enabled',
        width: 0.1,
        hidden: false,
        renderer: this.rendYesNo,
        editor: {
          xtype: 'modx-combo-boolean',
          renderer: this.rendYesNo,
        },
      },
    ],
    tbar: this.getTbar(config),
  });
  modAIAdmin.grid.Agents.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.Agents, modAIAdmin.grid.ACLGrid, {
  getMenu: function () {
    return [
      {
        text: _('modai.admin.agent.update'),
        handler: this.updateAgent,
      },
      '-',
      {
        text: _('modai.admin.agent.remove'),
        handler: this.removeAgent,
        permission: 'delete',
      }
    ];
  },

  getTbar: function (config) {
    const tbar = [];

    tbar.push({
      text: _('modai.admin.agent.create'),
      handler: this.createAgent,
      permission: 'save',
    });

    tbar.push('->');

    tbar.push([
      {
        xtype: 'textfield',
        emptyText: _('modai.admin.agent.search'),
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
      {
        xtype: 'modai-combo-extended_boolean',
        dataLabel: _('modai.admin.agent.enabled'),
        emptyText: _('modai.admin.agent.enabled'),
        filterName: 'enabled',
        useInt: true,
        listeners: {
          select: this.filterCombo,
          scope: this,
        },
      },
    ]);

    return tbar;
  },

  createAgent: function (btn, e) {
    modAIAdmin.loadPage('agent/create');
  },

  updateAgent: function (btn, e) {
    modAIAdmin.loadPage('agent/update', { id: this.menu.record.id });
  },

  removeAgent: function (btn, e) {
    if (!this.menu.record) return false;

    MODx.msg.confirm({
      title: _('modai.admin.agent.remove'),
      text: _('modai.admin.agent.remove_confirm', { name: this.menu.record.name }),
      url: this.config.url,
      params: {
        action: 'modAI\\Processors\\Agents\\Remove',
        id: this.menu.record.id,
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
Ext.reg('modai-grid-agents', modAIAdmin.grid.Agents);
