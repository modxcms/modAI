modAIAdmin.window.AgentTools = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('modai.admin.agent_tool.create'),
        closeAction: 'close',
        url: MODx.config.connector_url,
        action: 'modAI\\Processors\\AgentTools\\Create',
        modal: true,
        autoHeight: true,
        fields: this.getFields(config)
    });
    modAIAdmin.window.AgentTools.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.window.AgentTools, MODx.Window, {
    getFields: function (config) {
        return [
            {
                xtype: 'hidden',
                name: 'agent_id'
            },
            {
                xtype: 'modai-combo-tools',
                agent: config.record.agent_id,
                fieldLabel: _('modai.admin.agent.tools'),
                name: 'tools[]',
                hiddenName: 'tools[]',
                anchor: '100%',

            },
        ];
    }
});
Ext.reg('modai-window-agent_tools', modAIAdmin.window.AgentTools);

