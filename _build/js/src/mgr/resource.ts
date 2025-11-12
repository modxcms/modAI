import { ui } from '../ui';

const attachImagePlus = (imgPlusPanel: Element, fieldName: string) => {
  const imagePlus = Ext.getCmp(imgPlusPanel.firstElementChild?.id);
  const label =
    imagePlus.el.dom.parentElement?.parentElement?.parentElement?.querySelector('label');

  if (!label) {
    return;
  }

  ui.generateButton.localChat({
    targetEl: label,
    key: `resource/${MODx.request.id}/${fieldName}`,
    field: fieldName,
    type: 'image',
    resource: MODx.request.id,
    image: {
      mediaSource: imagePlus.imageBrowser.source,
    },
    imageActions: {
      insert: (msg, modal) => {
        imagePlus.imageBrowser.setValue(msg.ctx.url);
        imagePlus.onImageChange(msg.ctx.url);
        modal.api.closeModal();
      },
    },
  });

  const altTextWand = ui.generateButton.vision({
    targetEl: imagePlus.altTextField.el.dom,
    input: imagePlus.altTextField.items.items[0].el.dom,
    field: fieldName,
    resource: MODx.request.id,
    image: imagePlus.imagePreview.el.dom,
    onUpdate: (data) => {
      imagePlus.altTextField.items.items[0].setValue(data.content);
      imagePlus.image.altTag = data.content;
      imagePlus.updateValue();
    },
  });

  if (altTextWand) {
    altTextWand.style.marginTop = '6px';
  }

  imagePlus.altTextField.el.dom.style.display = 'flex';
  imagePlus.altTextField.el.dom.style.justifyItems = 'center';
  imagePlus.altTextField.el.dom.style.alignItems = 'center';
};

const attachContent = () => {
  const cmp = Ext.getCmp('modx-resource-content');
  const label = cmp.el.dom.querySelector('label');

  if (!label) {
    return;
  }

  ui.generateButton.localChat({
    targetEl: label,
    key: `resource/${MODx.request.id}/res.content`,
    field: 'res.content',
    type: 'text',
    availableTypes: ['text', 'image'],
    resource: MODx.request.id,
  });
};

const attachTVs = (config: Config) => {
  const form = Ext.getCmp('modx-panel-resource').getForm();
  for (const [tvId, tvName] of config.tvs || []) {
    const wrapper = Ext.get(`tv${tvId}-tr`);
    if (!wrapper) {
      continue;
    }

    const field = form.findField(`tv${tvId}`);
    const fieldName = `tv.${tvName}`;

    if (!field) {
      const imgPlusPanel = wrapper.dom.querySelector('.imageplus-panel-input');
      if (imgPlusPanel) {
        attachImagePlus(imgPlusPanel, fieldName);
      }
      continue;
    }

    if (field.xtype === 'textfield' || field.xtype === 'textarea') {
      const prompt = MODx.config[`modai.tv.${tvName}.text.prompt`];

      const label = wrapper.dom.querySelector('label');
      if (!label) return;

      if (prompt) {
        ui.generateButton.forcedText({
          targetEl: label,
          input: field.el.dom,
          resourceId: MODx.request.id,
          field: fieldName,
          initialValue: field.getValue(),
          onChange: (data, noStore) => {
            const prevValue = field.getValue();
            field.setValue(data.value);
            field.fireEvent('change', field, data.value, prevValue);

            if (noStore) {
              field.el.dom.scrollTop = field.el.dom.scrollHeight;
            }
          },
        });
      } else {
        ui.generateButton.localChat({
          targetEl: label,
          key: `resource/${MODx.request.id}/${fieldName}`,
          field: fieldName,
          type: 'text',
          availableTypes: ['text', 'image'],
          resource: MODx.request.id,
        });
      }
    }

    if (field.xtype === 'modx-panel-tv-image' || field.xtype === 'imagecropper-combo-browser') {
      const label = wrapper.dom.querySelector('label');
      if (!label) return;

      ui.generateButton.localChat({
        targetEl: label,
        key: `resource/${MODx.request.id}/${fieldName}`,
        field: fieldName,
        type: 'image',
        resource: MODx.request.id,
        image: {
          mediaSource: field.source,
        },
        imageActions: {
          insert: (msg, modal) => {
            const eventData = {
              fullRelativeUrl: msg.ctx.fullUrl,
              relativeUrl: msg.ctx.url,
              url: msg.ctx.url,
            };

            if (field.xtype === 'imagecropper-combo-browser') {
              field.onSelectImage(eventData.fullRelativeUrl, field.onTrigger1Click, `tv${tvId}`);
            } else {
              field.items.items[1].fireEvent('select', eventData);
            }

            field.fireEvent('select', eventData);
            modal.api.closeModal();
          },
        },
      });
    }
  }
};

const attachResourceFields = (config: Config) => {
  const fieldsMap: Record<string, string[]> = {
    pagetitle: ['modx-resource-pagetitle'],
    longtitle: ['modx-resource-longtitle', 'seosuite-longtitle'],
    introtext: ['modx-resource-introtext'],
    description: ['modx-resource-description', 'seosuite-description'],
    content: ['modx-resource-content'],
  };

  for (const field of config.resourceFields || []) {
    if (!fieldsMap[field]) {
      continue;
    }

    if (field === 'content') {
      attachContent();
      continue;
    }

    fieldsMap[field].forEach((cmpId) => {
      const fieldEl = Ext.getCmp(cmpId);
      if (fieldEl) {
        ui.generateButton.forcedText({
          targetEl: fieldEl.label,
          resourceId: MODx.request.id,
          field: `res.${field}`,
          input: fieldEl.el.dom,
          initialValue: fieldEl.getValue(),
          onChange: (data, noStore) => {
            const prevValue = fieldEl.getValue();
            fieldEl.setValue(data.value);
            fieldEl.fireEvent('change', fieldEl, data.value, prevValue);

            if (noStore) {
              fieldEl.el.dom.scrollTop = fieldEl.el.dom.scrollHeight;
            }
          },
        });
      }
    });
  }
};

type Config = { tvs: [number, string][]; resourceFields: string[] };

export const initOnResource = (config: Config) => {
  attachResourceFields(config);
  attachTVs(config);
};
