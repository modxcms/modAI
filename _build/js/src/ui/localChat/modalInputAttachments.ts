import { globalState } from '../../globalState';
import { applyStyles, createElement } from '../utils';

export type AttachmentsWrapper = HTMLDivElement & {
  visible: boolean;
  show: () => void;
  hide: () => void;
  addImageAttachment: (src: string) => void;
  addAttachment: (attachment: Attachment) => void;
  removeAttachment: (attachment: Attachment) => void;
  removeAttachments: () => void;
  attachments: Attachment[];
};

export type Attachment = HTMLDivElement & {
  type: 'image';
  value: string;
};

export const buildModalInputAttachments = () => {
  const attachmentsWrapper = createElement('div', 'attachmentsWrapper') as AttachmentsWrapper;
  attachmentsWrapper.visible = false;
  attachmentsWrapper.attachments = [];

  attachmentsWrapper.show = () => {
    if (attachmentsWrapper.visible) {
      return;
    }

    attachmentsWrapper.visible = true;
    applyStyles(attachmentsWrapper, 'attachmentsWrapper visible');
  };

  attachmentsWrapper.hide = () => {
    if (!attachmentsWrapper.visible) {
      return;
    }

    attachmentsWrapper.visible = false;
    applyStyles(attachmentsWrapper, 'attachmentsWrapper');
  };

  attachmentsWrapper.addImageAttachment = (src) => {
    addImageAttachment(src);
  };

  attachmentsWrapper.removeAttachments = () => {
    globalState.modal.attachments.attachments.forEach((el) => {
      globalState.modal.attachments.removeAttachment(el);
    });
  };

  attachmentsWrapper.addAttachment = (attachment) => {
    attachmentsWrapper.show();
    attachmentsWrapper.appendChild(attachment);
    attachmentsWrapper.attachments.push(attachment);
  };

  attachmentsWrapper.removeAttachment = (attachment) => {
    const index = attachmentsWrapper.attachments.indexOf(attachment);
    if (index === -1) {
      return;
    }

    attachment.remove();
    attachmentsWrapper.attachments.splice(index, 1);

    if (attachmentsWrapper.attachments.length === 0) {
      attachmentsWrapper.hide();
    }
  };

  globalState.modal.attachments = attachmentsWrapper;

  return attachmentsWrapper;
};

const addImageAttachment = (src: string) => {
  if (globalState.modal.attachments.attachments.length > 0) {
    globalState.modal.attachments.removeAttachments();
  }

  const attachment = createElement('div', 'imagePreview') as Attachment;
  attachment.type = 'image';
  attachment.value = src;

  const img = createElement('img', undefined, '', { src });
  const removeBtn = createElement('button', undefined, '×');

  removeBtn.addEventListener('click', (e: MouseEvent) => {
    e.stopPropagation();
    globalState.modal.attachments.removeAttachment(attachment);
  });

  attachment.append(img, removeBtn);

  globalState.modal.attachments.addAttachment(attachment);
};
