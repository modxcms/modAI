modAIAdmin.combo.ExtendedBoolean = function (config) {
    config.useInt = config.useInt || false;

    var data = [
        [
            _('modai.admin.global.any'),
            null,
            _('modai.admin.global.any')
        ],
        [
            _('yes'),
            (config.useInt ? 1 : true),
            _('yes')
        ],
        [
            _('no'),
            (config.useInt ? 0 : false),
            _('no')
        ]
    ];

    if (config.dataLabel) {
        data = [
            [
                config.dataLabel + ': ' + _('modai.admin.global.any'),
                null,
                _('modai.admin.global.any')
            ],
            [
                config.dataLabel + ': ' + _('yes'),
                (config.useInt ? 1 : true),
                _('yes')
            ],
            [
                config.dataLabel + ': ' + _('no'),
                (config.useInt ? 0 : false),
                _('no')
            ]
        ];
    }

    config = config || {};
    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ['d', 'v', 'cleanLabel'],
            data: data
        }),
        displayField: 'd',
        valueField: 'v',
        mode: 'local',
        triggerAction: 'all',
        editable: false,
        selectOnFocus: false,
        preventRender: true,
        forceSelection: true,
        enableKeyEvents: true,
        tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{cleanLabel}</div></tpl>')
    });
    modAIAdmin.combo.ExtendedBoolean.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.combo.ExtendedBoolean, MODx.combo.ComboBox);
Ext.reg('modai-combo-extended_boolean', modAIAdmin.combo.ExtendedBoolean);

modAIAdmin.combo.Boolean = function (config) {
    config.useInt = config.useInt || false;

    var data = [
        [
            _('yes'),
            (config.useInt ? 1 : true),
            _('yes')
        ],
        [
            _('no'),
            (config.useInt ? 0 : false),
            _('no')
        ]
    ];

    if (config.dataLabel) {
        data = [
            [
                config.dataLabel + ': ' + _('yes'),
                (config.useInt ? 1 : true), _('yes')
            ],
            [
                config.dataLabel + ': ' + _('no'),
                (config.useInt ? 0 : false), _('no')
            ]
        ];
    }

    config = config || {};
    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ['d', 'v', 'cleanLabel'],
            data: data
        }),
        displayField: 'd',
        valueField: 'v',
        mode: 'local',
        triggerAction: 'all',
        editable: false,
        selectOnFocus: false,
        preventRender: true,
        forceSelection: true,
        enableKeyEvents: true,
        tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item">{cleanLabel}</div></tpl>')
    });
    modAIAdmin.combo.Boolean.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.combo.Boolean, MODx.combo.ComboBox, {
    setValue: function (value) {
        if ((value !== undefined) && (this.config.useInt === true)) {
            if (value === '') {
                value = null;
            }

            if (value !== '') {
                value = +value;
            }
        }

        modAIAdmin.combo.Boolean.superclass.setValue.call(this, value);
    }
});
Ext.reg('modai-combo-boolean', modAIAdmin.combo.Boolean);

modAIAdmin.combo.ContextProviderClass = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'class',
        hiddenName: 'class',
        displayField: 'class',
        valueField: 'class',
        fields: ['class', 'config', 'suggestedName', 'description'],
        typeAhead: false,
        editable: true,
        forceSelection: true,
        pageSize: 0,
        minChars: 0,
        url: MODx.config.connector_url,
        baseParams: {
            action: 'modAI\\Processors\\Combos\\ContextProviderClass',
        },
        tpl: new Ext.XTemplate('<tpl for=".">' +
          '<div class="x-combo-list-item x-combo-list-item-grouped">' +
          '<div class="x-combo-list-title">{class:htmlEncode}</div>' +
          '{description:htmlEncode()}' +
          '</div>' +
          '</tpl>')
    });
    modAIAdmin.combo.ContextProviderClass.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.combo.ContextProviderClass, MODx.combo.ComboBox);
Ext.reg('modai-combo-context_provider_class', modAIAdmin.combo.ContextProviderClass);

modAIAdmin.combo.ToolClass = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        name: 'class',
        hiddenName: 'class',
        displayField: 'class',
        valueField: 'class',
        fields: ['class', 'config', 'suggestedName', 'description'],
        typeAhead: false,
        editable: true,
        forceSelection: true,
        pageSize: 0,
        minChars: 0,
        url: MODx.config.connector_url,
        baseParams: {
            action: 'modAI\\Processors\\Combos\\ToolClass',
        },
        tpl: new Ext.XTemplate('<tpl for=".">' +
          '<div class="x-combo-list-item x-combo-list-item-grouped">' +
          '<div class="x-combo-list-title">{class:htmlEncode}</div>' +
          '{description:htmlEncode()}' +
          '</div>' +
          '</tpl>')
    });
    modAIAdmin.combo.ToolClass.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.combo.ToolClass, MODx.combo.ComboBox);
Ext.reg('modai-combo-tool_class', modAIAdmin.combo.ToolClass);

modAIAdmin.combo.Tools = function (config, getStore) {
    config = config || {};
    const hideUsed = config.agent || 0;

    Ext.applyIf(config, {
        name: 'tools',
        hiddenName: 'tools[]',
        displayField: 'name',
        valueField: 'id',
        fields: ['name', 'id', 'description'],
        mode: 'remote',
        triggerAction: 'all',
        typeAhead: false,
        editable: true,
        forceSelection: true,
        pageSize: 20,
        queryParam: 'search',
        minChars: 0,
        url: MODx.config.connector_url,
        clearBtnCls: 'x-form-trigger',
        expandBtnCls: 'x-form-trigger',
        baseParams: {
            action: 'modAI\\Processors\\Tools\\GetList',
            hideUsed: hideUsed
        },
        tpl: new Ext.XTemplate('<tpl for=".">' +
          '<div class="x-combo-list-item x-combo-list-item-grouped">' +
          '<div class="x-combo-list-title">{name:htmlEncode}</div>' +
          '{description:htmlEncode()}' +
          '</div>' +
          '</tpl>')
    });
    Ext.applyIf(config, {
        store: new Ext.data.JsonStore({
            url: config.url,
            root: 'results',
            totalProperty: 'total',
            fields: config.fields,
            errorReader: MODx.util.JSONReader,
            baseParams: config.baseParams || {},
            remoteSort: config.remoteSort || false,
            autoDestroy: true
        })
    });
    if (getStore === true) {
        config.store.load();
        return config.store;
    }
    modAIAdmin.combo.Tools.superclass.constructor.call(this, config);
    this.config = config;
    return this;
};
Ext.extend(modAIAdmin.combo.Tools, Ext.ux.form.SuperBoxSelect);
Ext.reg('modai-combo-tools', modAIAdmin.combo.Tools);

modAIAdmin.combo.ContextProviders = function (config, getStore) {
    config = config || {};
    const hideUsed = config.agent || 0;

    Ext.applyIf(config, {
        name: 'context_providers',
        hiddenName: 'context_providers[]',
        displayField: 'name',
        valueField: 'id',
        fields: ['name', 'id', 'description'],
        mode: 'remote',
        triggerAction: 'all',
        typeAhead: false,
        editable: true,
        forceSelection: true,
        queryParam: 'search',
        minChars: 0,
        pageSize: 20,
        url: MODx.config.connector_url,
        clearBtnCls: 'x-form-trigger',
        expandBtnCls: 'x-form-trigger',
        baseParams: {
            action: 'modAI\\Processors\\ContextProviders\\GetList',
            hideUsed: hideUsed
        },
        tpl: new Ext.XTemplate('<tpl for=".">' +
          '<div class="x-combo-list-item x-combo-list-item-grouped">' +
          '<div class="x-combo-list-title">{name:htmlEncode}</div>' +
          '{description:htmlEncode()}' +
          '</div>' +
          '</tpl>')
    });
    Ext.applyIf(config, {
        store: new Ext.data.JsonStore({
            url: config.url,
            root: 'results',
            totalProperty: 'total',
            fields: config.fields,
            errorReader: MODx.util.JSONReader,
            baseParams: config.baseParams || {},
            remoteSort: config.remoteSort || false,
            autoDestroy: true
        })
    });
    if (getStore === true) {
        config.store.load();
        return config.store;
    }
    modAIAdmin.combo.ContextProviders.superclass.constructor.call(this, config);
    this.config = config;
    return this;
};
Ext.extend(modAIAdmin.combo.ContextProviders, Ext.ux.form.SuperBoxSelect);
Ext.reg('modai-combo-context_providers', modAIAdmin.combo.ContextProviders);

modAIAdmin.combo.Agents = function (config, getStore) {
    config = config || {};
    const hideUsedTool = config.tool || 0;
    const hideUsedContextProvider = config.context_provider || 0;

    Ext.applyIf(config, {
        name: 'agents',
        hiddenName: 'agents[]',
        displayField: 'name',
        valueField: 'id',
        fields: ['name', 'id', 'description'],
        mode: 'remote',
        triggerAction: 'all',
        typeAhead: false,
        editable: true,
        forceSelection: true,
        queryParam: 'search',
        minChars: 0,
        pageSize: 20,
        url: MODx.config.connector_url,
        clearBtnCls: 'x-form-trigger',
        expandBtnCls: 'x-form-trigger',
        baseParams: {
            action: 'modAI\\Processors\\Agents\\GetList',
            hideUsedTool: hideUsedTool,
            hideUsedContextProvider: hideUsedContextProvider,
        },
        tpl: new Ext.XTemplate('<tpl for=".">' +
          '<div class="x-combo-list-item x-combo-list-item-grouped">' +
          '<div class="x-combo-list-title">{name:htmlEncode}</div>' +
          '{description:htmlEncode()}' +
          '</div>' +
          '</tpl>')
    });
    Ext.applyIf(config, {
        store: new Ext.data.JsonStore({
            url: config.url,
            root: 'results',
            totalProperty: 'total',
            fields: config.fields,
            errorReader: MODx.util.JSONReader,
            baseParams: config.baseParams || {},
            remoteSort: config.remoteSort || false,
            autoDestroy: true
        })
    });
    if (getStore === true) {
        config.store.load();
        return config.store;
    }
    modAIAdmin.combo.Agents.superclass.constructor.call(this, config);
    this.config = config;
    return this;
};
Ext.extend(modAIAdmin.combo.Agents, Ext.ux.form.SuperBoxSelect);
Ext.reg('modai-combo-agents', modAIAdmin.combo.Agents);

modAIAdmin.combo.SettingArea = function (config) {
    const data = [
        [
            'text'
        ],
        [
            'image'
        ],
        [
            'vision'
        ]
    ];

    config = config || {};
    Ext.applyIf(config, {
        store: new Ext.data.SimpleStore({
            fields: ['v'],
            data: data
        }),
        displayField: 'v',
        valueField: 'v',
        mode: 'local',
        triggerAction: 'all',
        editable: false,
        selectOnFocus: false,
        preventRender: true,
        forceSelection: true,
        enableKeyEvents: true,
    });
    modAIAdmin.combo.SettingArea.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.combo.SettingArea, MODx.combo.ComboBox);
Ext.reg('modai-combo-setting_area', modAIAdmin.combo.SettingArea);

modAIAdmin.combo.UserGroups = function (config, getStore) {
    config = config || {};

    Ext.applyIf(config, {
        name: 'user_groups',
        hiddenName: 'user_groups[]',
        displayField: 'name',
        valueField: 'id',
        fields: ['name', 'id', 'description'],
        mode: 'remote',
        triggerAction: 'all',
        typeAhead: false,
        editable: true,
        forceSelection: true,
        queryParam: 'query',
        queryValuesDelimiter: ',',
        minChars: 0,
        pageSize: 20,
        url: MODx.config.connector_url,
        clearBtnCls: 'x-form-trigger',
        expandBtnCls: 'x-form-trigger',
        baseParams: {
            action: 'modAI\\Processors\\Combos\\UserGroups'
        },
        tpl: new Ext.XTemplate('<tpl for="."><div class="x-combo-list-item"><span style="font-weight: bold">{name:htmlEncode}</span>'
          ,'<br />{description:htmlEncode}</div></tpl>')
    });
    Ext.applyIf(config, {
        store: new Ext.data.JsonStore({
            url: config.url,
            root: 'results',
            totalProperty: 'total',
            fields: config.fields,
            errorReader: MODx.util.JSONReader,
            baseParams: config.baseParams || {},
            remoteSort: config.remoteSort || false,
            autoDestroy: true
        })
    });
    if (getStore === true) {
        config.store.load();
        return config.store;
    }
    modAIAdmin.combo.Agents.superclass.constructor.call(this, config);
    this.config = config;
    return this;
};
Ext.extend(modAIAdmin.combo.UserGroups, Ext.ux.form.SuperBoxSelect);
Ext.reg('modai-combo-user_groups', modAIAdmin.combo.UserGroups);
