import { ui } from '../ui';

export const initOnMediaBrowser = () => {
  Ext.override(MODx.tree.Directory, {
    _modAIOriginals: {
      initComponent: MODx.tree.Directory.prototype.initComponent,
    },
    initComponent: function () {
      this.on('afterrender', () => {
        const tbar = this.tbar.dom.querySelector('.x-toolbar-left-row');
        if (!tbar) {
          return;
        }

        const wrapper = document.createElement('td');
        wrapper.classList.add('x-toolbar-cell');

        const { shadow } = ui.generateButton.rawButton(
          () => {
            const node = this.cm && this.cm.activeNode ? this.cm.activeNode : false;
            const path = node && node.attributes.type == 'dir' ? node.attributes.pathRelative : '/';
            const filePath = (path.endsWith('/') ? path : path + '/') + '{hash}.png';

            ui.localChat.createModal({
              key: `media_browser/${this.config.id}`,
              type: 'image',
              image: {
                mediaSource: this.getSource(),
                path: filePath,
              },
              imageActions: {
                download: (_, modal) => {
                  this.fireEvent('afterUpload');
                  modal.api.closeModal();
                },
              },
            });
          },
          {
            iconSize: 16,
          },
        );

        wrapper.appendChild(shadow);
        tbar.appendChild(wrapper);
      });

      this._modAIOriginals.initComponent.call(this);
    },
  });
};
