modAIAdmin.page.Home = function (config) {
    config = config || {};
    config.permissions = config.permissions || {};

    Ext.applyIf(config, {
        components: [
            {
                xtype: 'modai-panel-home',
                renderTo: 'modai-page-admin',
                permissions: config.permissions,
            }
        ]
    });
    modAIAdmin.page.Home.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.page.Home, MODx.Component, {
});

Ext.reg('modai-page-home', modAIAdmin.page.Home);
