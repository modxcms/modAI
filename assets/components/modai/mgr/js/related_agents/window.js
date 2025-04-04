modAIAdmin.window.RelatedAgents = function (config) {
  config = config || {};
  Ext.applyIf(config, {
    title: _('modai.admin.agent_context_provider.create'),
    closeAction: 'close',
    url: MODx.config.connector_url,
    action: 'modAI\\Processors\\RelatedAgents\\Create',
    modal: true,
    autoHeight: true,
    fields: this.getFields(config)
  });
  modAIAdmin.window.RelatedAgents.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.window.RelatedAgents, MODx.Window, {
  getFields: function (config) {
    return [
      {
        xtype: 'hidden',
        name: 'tool_id'
      },
      {
        xtype: 'hidden',
        name: 'context_provider_id'
      },
      {
        xtype: 'modai-combo-agents',
        tool: config.record.tool_id,
        context_provider: config.record.context_provider_id,
        fieldLabel: _('modai.admin.related_agent.agents'),
        name: 'agents[]',
        hiddenName: 'agents[]',
        anchor: '100%',

      },
    ];
  }
});
Ext.reg('modai-window-related_agents', modAIAdmin.window.RelatedAgents);

