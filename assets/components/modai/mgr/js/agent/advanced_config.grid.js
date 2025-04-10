modAIAdmin.grid.AdvancedConfig = function (config) {
  config = config || {};

  Ext.applyIf(config, {
    preventSaveRefresh: false,
    fields: ['field', 'area', 'setting', 'value'],
    emptyText: _('modai.admin.global.no_records'),
    autoHeight: true,
    forceValidation: true,
    viewConfig: {
      forceFit: true,
      enableRowBody: true,
      autoFill: true,
      showPreview: true,
      scrollOffset: 0,
      emptyText: config.emptyText || _('ext_emptymsg'),
      getRowClass: function (record, index, rowParams, store) {
        if (record.invalid) {
          return 'modai-admin--invalid_row';
        }

        return '';
      },
    },
    columns: [
      {
        header: _('modai.admin.agent.advanced_config.field'),
        dataIndex: 'field',
        width: 30,
        sortable: true,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.agent.advanced_config.area'),
        dataIndex: 'area',
        width: 20,
        sortable: true,
        editor: {
          xtype: 'modai-combo-setting_area',
        },
      },
      {
        header: _('modai.admin.agent.advanced_config.setting'),
        dataIndex: 'setting',
        width: 40,
        sortable: true,
        editor: {
          xtype: 'textfield',
        },
      },
      {
        header: _('modai.admin.agent.advanced_config.value'),
        dataIndex: 'value',
        sortable: true,
        editor: {
          xtype: 'textarea',
        },
      },
    ],
    listeners: {
      beforerender: function (prepare) {
        if (this.config && this.config.initValue && Array.isArray(this.config.initValue)) {
          prepare.store.loadData(this.config.initValue);
        }
      },
      validateedit: function (data) {
        const gridView = data.grid.getView();
        const rowElement = gridView.getRow(data.row);

        var valid = true;

        if (!['field', 'area', 'setting'].includes(data.field)) {
          return true;
        }

        if (!data.value) {
          rowElement.classList.add('modai-admin--invalid_row');
          data.record.invalid = true;
          return true;
        }

        this.store.each(function (record, index) {
          const newData = {
            ...data.record.data,
            [data.field]: data.value,
          };

          if (
            index !== data.row &&
            record.data.field.toLowerCase() === newData.field.toLowerCase() &&
            record.data.area.toLowerCase() === newData.area.toLowerCase() &&
            record.data.setting.toLowerCase() === newData.setting.toLowerCase()
          ) {
            valid = false;
            return false;
          }
        });

        if (!valid) {
          rowElement.classList.add('modai-admin--invalid_row');
          data.record.invalid = true;
          return true;
        }

        data.record.invalid = false;
        return true;
      },
      afteredit: function (e) {
        e.grid.getView().refreshRow(e.row);
      },
    },
    tbar: [
      {
        text: _('modai.admin.agent.advanced_config.add_option'),
        handler: this.addOption,
      },
    ],
  });
  modAIAdmin.grid.AdvancedConfig.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.AdvancedConfig, MODx.grid.LocalGrid, {
  _loadStore: function (config) {
    return new Ext.data.JsonStore({
      fields: config.fields,
      remoteSort: false,
    });
  },

  getMenu: function () {
    return [
      {
        text: _('modai.admin.agent.advanced_config.remove_option'),
        handler: this.removeOption,
      },
    ];
  },

  addOption: function () {
    this.stopEditing();
    this.store.add(new this.store.recordType({ field: 'global', area: 'text' }));
    this.startEditing(this.store.getCount() - 1, 2);
  },

  removeOption: function () {
    this.store.removeAt(this.menu.recordIndex);
  },

  encode: function () {
    const store = this.getStore();
    const output = [];

    for (const item of store.data.items) {
      if (!item.data.field || !item.data.area || !item.data.setting) {
        continue;
      }

      if (item.invalid) {
        continue;
      }

      output.push({
        field: item.data.field,
        area: item.data.area,
        setting: item.data.setting,
        value: item.data.value,
      });
    }

    return output.length > 0 ? JSON.stringify(output) : null;
  },
});
Ext.reg('modai-grid-advanced_config', modAIAdmin.grid.AdvancedConfig);
