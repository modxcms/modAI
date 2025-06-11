modAIAdmin.tree.PromptLibraryCategories = function(config) {
  config = config || {};

  this.grid = config.grid;

  Ext.applyIf(config,{
    title: _('modai.admin.prompt_library.category'),
    url: MODx.config.connector_url,
    action: 'modAI\\Processors\\PromptLibrary\\Categories\\GetNodes',
    sortAction: 'modAI\\Processors\\PromptLibrary\\Categories\\Sort',
    rootIconCls: 'icon-sitemap',
    root_name: _('modai.admin.prompt_library.category'),
    rootVisible: false,
    enableDD: true,
    tbar: [{
      text: _('modai.admin.prompt_library.category.create'),
      handler: this.createCategory,
      scope: this
    }],
    useDefaultToolbar: true
  });
  modAIAdmin.tree.PromptLibraryCategories.superclass.constructor.call(this,config);
  this.on('click',this.loadGrid,this);
};
Ext.extend(modAIAdmin.tree.PromptLibraryCategories,MODx.tree.Tree,{
  loadGrid: function(n,e) {
    if (n.attributes.pseudoroot) {
      return;
    }

    this.grid.filterByCategory(n.attributes.data.id);
  },

  _handleDrag: function(dropEvent) {
    const simplifyNodes = (node) => {
      const resultNode = {};
      const kids = node.childNodes;
      const len = kids.length;

      for (let i = 0; i < len; i++) {
        resultNode[kids[i].id] = simplifyNodes(kids[i]);
      }

      return resultNode;
    }

    const encNodes = Ext.encode(simplifyNodes(dropEvent.tree.root));

    MODx.Ajax.request({
      url: this.config.url,
      params: {
        target: dropEvent.target.attributes.id,
        source: dropEvent.source.dragData.node.attributes.id,
        point: dropEvent.point,
        data: encodeURIComponent(encNodes),
        action: this.config.sortAction || 'sort'
      },
      listeners: {
        success: {
          fn: function(r) {
            const el = dropEvent.dropNode.getUI().getTextEl();
            if (el) {
              Ext.get(el).frame();
            }

            this.fireEvent('afterSort', {
              event: dropEvent,
              result: r
            });
          },
          scope:this
        },
        failure: {
          fn:function(r) {
            MODx.form.Handler.errorJSON(r);
            this.refresh();
            return false;
          },
          scope:this
        }
      }
    });
  },

  createCategory: function(btn,e) {
    const record = {
      parent_id: btn.cat_id || 0,
      enabled: false,
    };

    if (btn.cat_id !== undefined) {
      record.type = this.cm.activeNode.attributes.data.type;
    }

    const win = MODx.load({
      xtype: 'modai-window-prompt_library_categories',
      record: record,
      listeners: {
        success: {
          fn: function () {
            this.refreshNode(this.cm.activeNode.id);
          },
          scope: this,
        },
      },
    });

    win.fp.getForm().setValues(record);
    win.show(e.target);

    return true;
  },

  updateCategory: function(btn,e) {
    const record = this.cm.activeNode.attributes.data;

    const win = MODx.load({
      xtype: 'modai-window-prompt_library_categories',
      record: record,
      isUpdate: true,
      listeners: {
        success: {
          fn: function () {
            this.refreshNode(this.cm.activeNode.id);
          },
          scope: this,
        },
      },
    });

    win.fp.getForm().setValues(record);
    win.show(e.target);
  },

  removeCategory: function(btn,e) {
    const data = this.cm.activeNode.attributes.data;

    MODx.msg.confirm({
      title: _('modai.admin.prompt_library.category.remove'),
      text: _('modai.admin.prompt_library.category.remove_confirm', { name: data.name }),
      url: MODx.config.connector_url,
      params: {
        action: 'modAI\\Processors\\PromptLibrary\\Categories\\Remove',
        id: data.id
      },
      listeners: {
        success: {
          fn: function() {
            this.refreshNode(this.cm.activeNode.id);
            this.grid.filterByCategory(0);
          },
          scope:this
        }
      }
    });
  },

  handleCreateClick: function(node) {
    this.cm.activeNode = node;

    const record = {
      parent_id:  0,
      enabled: false,
      type: node.attributes.type
    };

    const win = MODx.load({
      xtype: 'modai-window-prompt_library_categories',
      record: record,
      listeners: {
        success: {
          fn: function () {
            this.refreshNode(this.cm.activeNode.id);
          },
          scope: this,
        },
      },
    });

    win.fp.getForm().setValues(record);
    win.show();

    return true;
  },

  getInlineButtonsLang: function (node) {
    return {
      add: _('modai.admin.prompt_library.category.create'),
      refresh: _('ext_refresh')
    };
  }

});
Ext.reg('modai-tree-prompt_library_categories', modAIAdmin.tree.PromptLibraryCategories);
