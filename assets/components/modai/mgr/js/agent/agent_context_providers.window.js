modAIAdmin.window.AgentContextProviders = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('modai.admin.agent_context_provider.create'),
        closeAction: 'close',
        url: MODx.config.connector_url,
        action: 'modAI\\Processors\\AgentContextProviders\\Create',
        modal: true,
        autoHeight: true,
        fields: this.getFields(config)
    });
    modAIAdmin.window.AgentContextProviders.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.window.AgentContextProviders, MODx.Window, {
    getFields: function (config) {
        return [
            {
                xtype: 'hidden',
                name: 'agent_id'
            },
            {
                xtype: 'modai-combo-context_providers',
                agent: config.record.agent_id,
                fieldLabel: _('modai.admin.agent.context_providers'),
                name: 'context_providers[]',
                hiddenName: 'context_providers[]',
                anchor: '100%',

            },
        ];
    }
});
Ext.reg('modai-window-agent_context_providers', modAIAdmin.window.AgentContextProviders);

