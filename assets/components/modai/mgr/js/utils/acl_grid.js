modAIAdmin.grid.ACLGrid = function (config) {
  config = config || {};
  config.permissions = config.permissions || null;
  config.permission_item = config.permission_item || null;

  if (!config.permission_item || !config.permissions) {
    throw new Error('permission_item and permissions config properties are required');
  }

  if (!this.canSave(config)) {
    delete config.save_action;
    config.autosave = false;

    for (const column of config.columns) {
      column.editor = false;
    }

    if (config.tbar) {
      config.tbar = config.tbar.filter((item) => {
        if (!item.permission) {
          return true;
        }

        return this.hasPermission(config, item.permission);
      })
    }
  }

  modAIAdmin.grid.ACLGrid.superclass.constructor.call(this, config);
};
Ext.extend(modAIAdmin.grid.ACLGrid, MODx.grid.Grid, {

  _showMenu: function(g,ri,e) {
    e.stopEvent();
    e.preventDefault();
    this.menu.recordIndex = ri;
    this.menu.record = this.getStore().getAt(ri).data;
    if (!this.getSelectionModel().isSelected(ri)) {
      this.getSelectionModel().selectRow(ri);
    }
    this.menu.removeAll();
    const menu = this.cleanMenu(this.getMenu(g,ri).filter((item) => {
      if (!item.permission) {
        return true;
      }

      return this.hasPermission(this.config, item.permission);
    }));

    if (menu) {
      this.addContextMenuItem(menu);
      this.menu.showAt(e.xy);
    }
  },

  cleanMenu: function (arr) {
    if (!Array.isArray(arr) || arr.length === 0) {
      return [];
    }

    const reducedArray = arr.reduce((accumulator, currentItem) => {
      if (typeof currentItem === 'object' && currentItem !== null) {
        accumulator.push(currentItem);
      } else if (currentItem === '-') {
        if (accumulator.length === 0 || accumulator[accumulator.length - 1] !== '-') {
          accumulator.push(currentItem);
        }
      }

      return accumulator;
    }, []);

    if (reducedArray.length > 0 && reducedArray[0] === '-') {
      reducedArray.shift();
    }

    if (reducedArray.length > 0 && reducedArray[reducedArray.length - 1] === '-') {
      reducedArray.pop();
    }

    return reducedArray;
  },

  canSave: function(config) {
    return this.hasPermission(config, 'save');
  },

  canDelete: function(config) {
    return this.hasPermission(config, 'delete');
  },

  hasPermission: function(config, permission) {
    return !!config.permissions[`modai_admin_${config.permission_item}_${permission}`];
  }
});
