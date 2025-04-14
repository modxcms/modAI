modAIAdmin.grid.ContextProviders = function (config) {
    config = config || {};
    config.permissions = config.permissions || {};
    config.permission_item = 'context_provider';

    Ext.applyIf(config, {
        url: MODx.config.connector_url,
        baseParams: {
            action: 'modAI\\Processors\\ContextProviders\\GetList',
        },
        save_action: 'modAI\\Processors\\ContextProviders\\UpdateFromGrid',
        autosave: true,
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
                hidden: true
            },
            {
                header: _('modai.admin.context_provider.name'),
                dataIndex: 'name',
                width: 0.2,
                sortable: true,
                hidden: false,
                editor: {
                    xtype: 'textfield',
                }
            },
            {
                header: _('modai.admin.context_provider.description'),
                dataIndex: 'description',
                width: 0.6,
                hidden: false,
                editor: {
                    xtype: 'textarea',
                }
            },
            {
                header: _('modai.admin.context_provider.enabled'),
                dataIndex: 'enabled',
                width: 0.05,
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
    modAIAdmin.grid.ContextProviders.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.ContextProviders, modAIAdmin.grid.ACLGrid, {

    getMenu: function () {
        return [
            {
                text: _('modai.admin.context_provider.update'),
                handler: this.updateContextProvider
            },
            '-',
            {
                text: _('modai.admin.context_provider.remove'),
                handler: this.removeContextProvider,
                permission: 'delete',
            }
        ];

    },

    getTbar: function(config) {
        return [
            {
                text: _('modai.admin.context_provider.create'),
                handler: this.createContextProvider,
                permission: 'save'
            },
            '->',
            {
                xtype: 'textfield',
                emptyText: _('modai.admin.context_provider.search'),
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
                dataLabel: _('modai.admin.context_provider.enabled'),
                emptyText: _('modai.admin.context_provider.enabled'),
                filterName: 'enabled',
                useInt: true,
                listeners: {
                    select: this.filterCombo,
                    scope: this
                }
            }
        ];
    },

    createContextProvider: function(btn, e) {
        modAIAdmin.loadPage('context_provider/create');
    },

    updateContextProvider: function (btn, e) {
        modAIAdmin.loadPage('context_provider/update', { id: this.menu.record.id });
    },

    removeContextProvider: function (btn, e) {
        if (!this.menu.record) return false;

        MODx.msg.confirm({
            title: _('modai.admin.context_provider.remove'),
            text: _('modai.admin.context_provider.remove_confirm', { name: this.menu.record.name }),
            url: this.config.url,
            params: {
                action: 'modAI\\Processors\\ContextProviders\\Remove',
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
Ext.reg('modai-grid-context_providers', modAIAdmin.grid.ContextProviders);
