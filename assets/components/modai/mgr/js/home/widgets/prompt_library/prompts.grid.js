modAIAdmin.grid.PromptLibraryPrompts = function (config) {
  config = config || {};
  config.permissions = config.permissions || {};
  config.permission_item = 'prompt_library_category';

  Ext.applyIf(config, {
    url: MODx.config.connector_url,
    baseParams: {
      action: 'modAI\\Processors\\PromptLibrary\\Prompts\\GetList',
    },
    save_action: 'modAI\\Processors\\PromptLibrary\\Prompts\\UpdateFromGrid',
    autosave: true,
    preventSaveRefresh: false,
    fields: ['id', 'name', 'prompt', 'category_id', 'enabled', 'rank'],
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
        header: _('modai.admin.prompt_library.prompt.name'),
        dataIndex: 'name',
        width: 0.7,
        sortable: true,
        hidden: false,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.prompt_library.prompt.enabled'),
        dataIndex: 'enabled',
        width: 0.1,
        hidden: false,
        renderer: this.rendYesNo,
        editor: {
          xtype: 'modx-combo-boolean',
          renderer: this.rendYesNo,
        },
      },
      {
        header: _('modai.admin.prompt_library.prompt.rank'),
        dataIndex: 'rank',
        width: 0.1,
        hidden: false,
        editor: {
          xtype: 'numberfield',
          allowDecimals: false
        },
      },
    ],
    tbar: this.getTbar(config),
  });
  modAIAdmin.grid.PromptLibraryPrompts.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.PromptLibraryPrompts, modAIAdmin.grid.ACLGrid, {
  getMenu: function () {
    return [
      {
        text: _('modai.admin.prompt_library.prompt.update'),
        handler: this.updatePrompt,
      },
      '-',
      {
        text: _('modai.admin.prompt_library.prompt.remove'),
        handler: this.removePrompt,
        permission: 'delete',
      }
    ];
  },

  getTbar: function (config) {
    const tbar = [];

    tbar.push({
      text: _('modai.admin.prompt_library.prompt.create'),
      handler: this.createPrompt,
      permission: 'save',
    });

    tbar.push('->');

    tbar.push([
      {
        xtype: 'textfield',
        emptyText: _('modai.admin.prompt_library.prompt.search'),
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
        dataLabel: _('modai.admin.prompt_library.prompt.enabled'),
        emptyText: _('modai.admin.prompt_library.prompt.enabled'),
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

  createPrompt: function (btn, e) {
    const store = this.getStore();

    const record = {
      category_id: store.baseParams.category || 0,
      enabled: false
    };

    const win = MODx.load({
      xtype: 'modai-window-prompt_library_prompts',
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

  updatePrompt: function (btn, e) {

    const win = MODx.load({
      xtype: 'modai-window-prompt_library_prompts',
      record: this.menu.record,
      isUpdate: true,
      listeners: {
        success: {
          fn: function () {
            this.refresh();
          },
          scope: this,
        },
      },
    });

    win.fp.getForm().setValues(this.menu.record);
    win.show(e.target);
  },

  removePrompt: function (btn, e) {
    if (!this.menu.record) return false;

    MODx.msg.confirm({
      title: _('modai.admin.prompt_library.prompt.remove'),
      text: _('modai.admin.prompt_library.prompt.remove_confirm', { name: this.menu.record.name }),
      url: this.config.url,
      params: {
        action: 'modAI\\Processors\\PromptLibrary\\Prompts\\Remove',
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

  filterByCategory: function (category) {
    const s = this.getStore();
    s.baseParams['category'] = category;
    this.getBottomToolbar().changePage(1);
  }
});
Ext.reg('modai-grid-prompt_library_prompts', modAIAdmin.grid.PromptLibraryPrompts);
