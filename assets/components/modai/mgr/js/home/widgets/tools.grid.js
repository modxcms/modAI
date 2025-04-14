modAIAdmin.grid.Tools = function (config) {
    config = config || {};
    config.permissions = config.permissions || {};
    config.permission_item = 'tool';

    Ext.applyIf(config, {
        url: MODx.config.connector_url,
        baseParams: {
            action: 'modAI\\Processors\\Tools\\GetList',
        },
        save_action: 'modAI\\Processors\\Tools\\UpdateFromGrid',
        autosave: true,
        preventSaveRefresh: false,
        fields: ['id', 'name', 'description', 'default', 'enabled'],
        paging: true,
        remoteSort: true,
        emptyText: _('modai.admin.global.no_records'),
        columns: [
            {
                header: _('id'),
                dataIndex: 'id',
                width: 0.05,
                sortable: true,
                hidden: true
            },
            {
                header: _('modai.admin.tool.name'),
                dataIndex: 'name',
                width: 0.2,
                sortable: true,
                hidden: false,
                editor: {
                    xtype: 'textfield',
                }
            },
            {
                header: _('modai.admin.tool.description'),
                dataIndex: 'description',
                width: 0.7,
                sortable: true,
                hidden: false,
                editor: {
                    xtype: 'textfield',
                }
            },
            {
                header: _('modai.admin.tool.default'),
                dataIndex: 'default',
                width: 0.1,
                hidden: false,
                renderer: this.rendYesNo,
                editor: {
                    xtype: 'modx-combo-boolean',
                    renderer: this.rendYesNo
                }
            },
            {
                header: _('modai.admin.tool.enabled'),
                dataIndex: 'enabled',
                width: 0.1,
                hidden: false,
                renderer: this.rendYesNo,
                editor: {
                    xtype: 'modx-combo-boolean',
                    renderer: this.rendYesNo
                }
            }
        ],
        tbar: this.getTbar(config)
    });
    modAIAdmin.grid.Tools.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.Tools, modAIAdmin.grid.ACLGrid, {

    getMenu: function () {
        return [
            {
                text: _('modai.admin.tool.update'),
                handler: this.updateTool
            },
            '-',
            {
                text: _('modai.admin.tool.remove'),
                handler: this.removeTool,
                permission: 'delete'
            }
        ];
    },

    getTbar: function(config) {
        return [
            {
                text: _('modai.admin.tool.create'),
                handler: this.createTool,
                permission: 'save',
            },
            '->',
            {
                xtype: 'textfield',
                emptyText: _('modai.admin.tool.search'),
                listeners: {
                    change: {
                        fn: this.search,
                        scope: this
                    },
                    render: {
                        fn: function (cmp) {
                            new Ext.KeyMap(cmp.getEl(), {
                                key: Ext.EventObject.ENTER,
                                fn: function () {
                                    this.blur();
                                    return true;
                                },
                                scope: cmp
                            });
                        },
                        scope: this
                    }
                }
            },
            {
                xtype: 'modai-combo-extended_boolean',
                dataLabel: _('modai.admin.tool.default'),
                emptyText: _('modai.admin.tool.default'),
                filterName: 'default',
                useInt: true,
                listeners: {
                    select: this.filterCombo,
                    scope: this
                }
            },
            {
                xtype: 'modai-combo-extended_boolean',
                dataLabel: _('modai.admin.tool.enabled'),
                emptyText: _('modai.admin.tool.enabled'),
                filterName: 'enabled',
                useInt: true,
                listeners: {
                    select: this.filterCombo,
                    scope: this
                }
            }
        ];
    },

    createTool: function(btn, e) {
        modAIAdmin.loadPage('tool/create');
    },

    updateTool: function (btn, e) {
        modAIAdmin.loadPage('tool/update', { id: this.menu.record.id });
    },

    removeTool: function (btn, e) {
        if (!this.menu.record) return false;

        MODx.msg.confirm({
            title: _('modai.admin.tool.remove'),
            text: _('modai.admin.tool.remove_confirm', { name: this.menu.record.name }),
            url: this.config.url,
            params: {
                action: 'modAI\\Processors\\Tools\\Remove',
                id: this.menu.record.id
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
Ext.reg('modai-grid-tools', modAIAdmin.grid.Tools);
