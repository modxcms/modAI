modAIAdmin.panel.Home = function (config) {
    config = config || {};
    config.permission = config.permission || {};

    this.state = Ext.state.Manager.getProvider();

    Ext.apply(config, {
        border: false,
        cls: 'container',
        id: 'modai-home-panel',
        items: this.getItems(config)
    });
    modAIAdmin.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.panel.Home, MODx.Panel, {
    getItems: function(config) {
        return [
            {
                html: '<h2>' + _('modai.admin.home.page_title') + '</h2>',
                border: false,
                cls: 'modx-page-header'
            },
            {
                xtype: 'modx-tabs',
                stateful: true,
                stateId: 'modai-tab-home',
                stateEvents: ['tabchange'],
                getState: function () {
                    return {
                        activeItem: this.items.indexOf(this.getActiveTab())
                    };
                },
                defaults: {
                    border: false,
                    autoHeight: true
                },
                border: true,
                activeItem: 0,
                hideMode: 'offsets',
                items: this.getTopTabs(config)
            }
        ];
    },

    getTopTabs: function(config) {
        var output = [];

        output.push({
            title: _('modai.admin.home.agents'),
            items: []
        });

        output.push({
            title: _('modai.admin.home.tools'),
            items: [
                {
                    xtype: 'modai-grid-tools',
                    preventRender: true,
                    cls: 'main-wrapper'
                }
            ]
        });

        output.push({
            title: _('modai.admin.home.context_providers'),
            items: [
                {
                    xtype: 'modai-grid-context_providers',
                    preventRender: true,
                    cls: 'main-wrapper'
                }
            ]
        });

        return output;
    },
});
Ext.reg('modai-panel-home', modAIAdmin.panel.Home);
