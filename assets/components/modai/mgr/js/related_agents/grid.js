modAIAdmin.grid.RelatedAgents = function (config) {
  config = config || {};

  if (!config.relatedObject) {
    console.error('relatedObject property is required for modAIAdmin.grid.RelatedAgents.')
  }

  Ext.applyIf(config, {
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\Agents\\GetList',
      ...config.relatedObject,
    },
    preventSaveRefresh: false,
    fields: ['id', 'name', 'description', 'model'],
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
      },  {
        header: _('modai.admin.agent.description'),
        dataIndex: 'description',
        width: 0.7,
        sortable: true,
        hidden: false,
      },
      {
        header: _('modai.admin.agent.model'),
        dataIndex: 'model',
        width: 0.2,
        hidden: false,
      },
    ],
    tbar: this.getTbar(config),
  });
  modAIAdmin.grid.RelatedAgents.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.RelatedAgents, MODx.grid.Grid, {
  getMenu: function () {
    var m = [];

    m.push({
      text: _('modai.admin.related_agent.view'),
      handler: this.viewRelatedAgent
    });

    m.push('-');

    m.push({
      text: _('modai.admin.related_agent.remove'),
      handler: this.removeRelatedAgent
    });

    return m;
  },

  getTbar: function (config) {
    return [
      {
        text: _('modai.admin.related_agent.create'),
        handler: this.createRelatedAgent,
      },
      '->',
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
      }
    ];
  },

  createRelatedAgent: function (btn, e) {
    const record = {
      ...this.config.relatedObject,
    };

    const win = MODx.load({
      xtype: 'modai-window-related_agents',
      record: record,
      listeners: {
        success: {
          fn: function () {
            this.refresh();
          },
          scope: this
        }
      }
    });

    win.fp.getForm().setValues(record);
    win.show(e.target);

    return true;
  },

  viewRelatedAgent: function(btn, e) {
    modAIAdmin.loadPage('agent/update', { id: this.menu.record.id });
  },

  removeRelatedAgent: function (btn, e) {
    if (!this.menu.record) return false;

    MODx.msg.confirm({
      title: _('modai.admin.related_agent.remove'),
      text: _('modai.admin.related_agent.remove_confirm', { name: this.menu.record.name }),
      url: this.config.url,
      params: {
        action: 'modAI\\Processors\\RelatedAgents\\Remove',
        agent_id: this.menu.record.id,
        ...this.config.relatedObject,
      },
      listeners: {
        success: {
          fn: function (r) {
            this.refresh();
          },
          scope: this
        }
      }
    });

    return true;
  },

  search: function (field, value) {
    const s = this.getStore();
    s.baseParams.search = value;
    this.getBottomToolbar().changePage(1);
  },
});
Ext.reg('modai-grid-related_agents', modAIAdmin.grid.RelatedAgents);
