modAIAdmin.grid.AgentTools = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};
  config.permission_item = 'agent_tool';

  Ext.applyIf(config, {
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\Tools\\GetList',
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
        header: _('modai.admin.tool.name'),
        dataIndex: 'name',
        width: 0.2,
        sortable: true,
        hidden: false,
      },
      {
        header: _('modai.admin.tool.description'),
        dataIndex: 'description',
        width: 0.6,
        sortable: true,
        hidden: false,
      },
      {
        header: _('modai.admin.tool.enabled'),
        dataIndex: 'enabled',
        renderer: this.rendYesNo,
        width: 0.1,
        hidden: false,
      },
    ],
    tbar: this.getTbar(config),
  });
  modAIAdmin.grid.AgentTools.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.AgentTools, modAIAdmin.grid.ACLGrid, {
  getMenu: function () {
    return [
      {
        text: _('modai.admin.agent_tool.view'),
        handler: this.viewTool,
      },
      '-',
      {
        text: _('modai.admin.agent_tool.remove'),
        handler: this.removeTool,
        permission: 'delete'
      }
    ];
  },

  getTbar: function (config) {
    return [
      {
        text: _('modai.admin.agent_tool.create'),
        handler: this.createTool,
        permission: 'save'
      },
      '->',
      {
        xtype: 'textfield',
        emptyText: _('modai.admin.tool.search'),
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

  createTool: function (btn, e) {
    const record = {
      agent_id: MODx.request.id,
    };

    const win = MODx.load({
      xtype: 'modai-window-agent_tools',
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

  removeTool: function (btn, e) {
    if (!this.menu.record) return false;

    MODx.msg.confirm({
      title: _('modai.admin.agent_tool.remove'),
      text: _('modai.admin.agent_tool.remove_confirm', { name: this.menu.record.name }),
      url: this.config.url,
      params: {
        action: 'modAI\\Processors\\AgentTools\\Remove',
        agent_id: MODx.request.id,
        tool_id: this.menu.record.id,
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

  viewTool: function (btn, e) {
    modAIAdmin.loadPage('tool/update', { id: this.menu.record.id });
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
Ext.reg('modai-grid-agent_tools', modAIAdmin.grid.AgentTools);
