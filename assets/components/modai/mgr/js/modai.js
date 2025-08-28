const ModAIAdmin = function (config) {
    config = config || {};
    ModAIAdmin.superclass.constructor.call(this, config);
};
Ext.extend(ModAIAdmin, Ext.Component, {
    page: {},
    window: {},
    grid: {},
    panel: {},
    combo: {},
    tree: {},
    config: {},
    loadPage: function (action, parameters) {
        if (!parameters) {
            parameters = 'namespace=modai';
        } else {
            if (typeof parameters === 'object') {
                var stringParams = [];

                for (var key in parameters) {
                    if (parameters.hasOwnProperty(key)) {
                        stringParams.push(key + '=' + parameters[key]);
                    }
                }

                parameters = stringParams.join('&');
            }
            parameters += '&namespace=modai';
        }

        MODx.loadPage(action, parameters);
    },

    getPageUrl: function(action, parameters) {
        if (!parameters) {
            parameters = 'namespace=modai';
        } else {
            if (typeof parameters === 'object') {
                var stringParams = [];

                for (var key in parameters) {
                    if (parameters.hasOwnProperty(key)) {
                        stringParams.push(key + '=' + parameters[key]);
                    }
                }

                parameters = stringParams.join('&');
            }
            parameters += '&namespace=modai';
        }

        // Handles url, passed as first argument
        var parts = [];
        if (action) {
            if (isNaN(parseInt(action)) && (action.substr(0, 1) == '?' || (action.substr(0, "index.php?".length) == 'index.php?'))) {
                parts.push(action);
            } else {
                parts.push('?a=' + action);
            }
        }

        if (parameters) {
            parts.push(parameters);
        }

        return parts.join('&');
    },

    formatConfigItem: function (key, cfg, value = undefined) {
        if (cfg.type === 'combo-boolean' || cfg.type === 'modx-combo-boolean') {
            cfg.type = 'modai-combo-boolean';
            cfg.extraProperties = cfg.extraProperties || {};
            cfg.extraProperties.useInt = true;
        }

        return [
            {
                fieldLabel: cfg.name,
                allowBlank: !cfg.required,
                xtype: cfg.type,
                name: `config_${key}`,
                hiddenName: `config_${key}`,
                value: value ?? cfg.defaultValue,
                ...(cfg.extraProperties || {}),
            },
            {
                xtype: 'label',
                html: cfg.description,
                cls: 'desc-under'
            }
        ];
    },

    getResponsiveColumnLayout: function() {}
});
Ext.reg('modai-admin', ModAIAdmin);
modAIAdmin = new ModAIAdmin();
