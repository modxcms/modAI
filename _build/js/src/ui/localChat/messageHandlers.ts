import { createActionButton } from './actionButton';
import { createShadowElement } from './shadow';
import { executor } from '../../executor';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { confirmDialog } from '../cofirmDialog';
import { icon } from '../dom/icon';
import { copy, edit, plus, triangleError } from '../icons';
import { createElement, nlToBr } from '../utils';

import type { LocalChatConfig } from './types';
import type { Message, UpdatableHTMLElement } from '../../chatHistory';

export const addUserMessage = (msg: Message) => {
  const messageElement: UpdatableHTMLElement = createElement('div', 'message user');

  let textContent;
  let imagesContent = null;

  if (Array.isArray(msg.content)) {
    const [text, ...images] = msg.content;
    textContent = text.value;
    imagesContent = images;
  } else {
    textContent = msg.content;
  }

  const textDiv = createElement('div');
  textDiv.innerHTML = nlToBr(textContent);
  messageElement.appendChild(textDiv);

  if (imagesContent) {
    const imageRow = createElement('div', 'imageRow');

    for (const imgContent of imagesContent) {
      const imageWrapper = createElement('div');

      const img = createElement('img');
      img.src = imgContent.value;

      imageWrapper.appendChild(img);
      imageRow.appendChild(imageWrapper);
    }
    messageElement.appendChild(imageRow);
  }

  const actionsContainer = createElement('div', 'actions');
  actionsContainer.appendChild(
    createActionButton({
      message: msg,
      disabled: globalState.modal.isLoading,
      disableCompletedState: true,
      icon: icon(14, edit),
      label: 'Edit',
      onClick: (msg) => {
        globalState.alertOpen = true;
        confirmDialog({
          title: 'Confirm Edit',
          content:
            'Editing this message will delete all responses that came after it. Do you want to proceed?',
          confirmText: 'Edit Message',
          onCancel: () => {
            globalState.alertOpen = false;
          },
          onConfirm: () => {
            globalState.alertOpen = false;
            globalState.modal.messageInput.setValue(
              typeof msg.content === 'string' ? msg.content : msg.content[0].value,
            );

            if (typeof msg.content !== 'string') {
              for (const el of msg.content) {
                if (el.type === 'text') {
                  continue;
                }

                if (el.type === 'image') {
                  globalState.modal.attachments.addImageAttachment(el.value);
                }
              }
            }

            globalState.modal.history.clearHistoryFrom(msg.id);
            if (globalState.modal.history.getMessages().length === 0) {
              globalState.modal.welcomeMessage.style.display = 'block';
            }
          },
        });
      },
    }),
  );

  messageElement.appendChild(actionsContainer);

  messageElement.update = (msg) => {
    const textContent = Array.isArray(msg.content) ? msg.content[0].value : msg.content;
    textDiv.innerHTML = nlToBr(textContent);
  };

  globalState.modal.chatMessages.appendChild(messageElement);

  return messageElement;
};

export const addErrorMessage = (content: string) => {
  globalState.modal.welcomeMessage.style.display = 'none';

  const messageElement: UpdatableHTMLElement = createElement('div', 'message error');

  messageElement.appendChild(icon(14, triangleError));

  const textSpan = createElement('span');
  textSpan.textContent = content;
  messageElement.appendChild(textSpan);

  globalState.modal.chatMessages.appendChild(messageElement);

  return messageElement;
};

export const addAssistantMessage = (msg: Message, config: LocalChatConfig) => {
  const messageElement: UpdatableHTMLElement = createElement('div', 'message ai');

  let textContent = Array.isArray(msg.content) ? msg.content[0].value : msg.content;
  if (msg.type === 'image') {
    textContent = `<img src="${textContent}" />`;
  }

  const shadow = createShadowElement(textContent, config.customCSS ?? []);
  messageElement.appendChild(shadow);

  const actionsContainer = createElement('div', 'actions');

  if (config.type === 'text') {
    if (config.textActions?.copy !== false) {
      actionsContainer.appendChild(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: icon(14, copy),
          label: 'Copy',
          completedText: 'Copied!',
          onClick:
            typeof config.textActions?.copy === 'function'
              ? config.textActions.copy
              : copyToClipboard,
        }),
      );
    }

    if (typeof config.textActions?.insert === 'function') {
      actionsContainer.append(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: icon(14, plus),
          label: 'Insert',
          completedText: 'Inserted!',
          onClick: config.textActions.insert,
        }),
      );
    }
  }

  if (config.type === 'image') {
    if (config.imageActions?.copy !== false) {
      actionsContainer.append(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: icon(14, copy),
          label: 'Copy',
          loadingText: 'Downloading...',
          completedText: 'Copied!',
          onClick: async (msg, modal) => {
            const handler =
              typeof config.textActions?.copy === 'function'
                ? config.textActions.copy
                : copyToClipboard;

            if (msg.ctx.downloaded === true) {
              handler(msg, modal);
              return;
            }
            const data = await executor.mgr.download.image({
              url: msg.content as string,
              field: config.field,
              namespace: config.namespace,
              resource: config.resource,
              mediaSource: config.image?.mediaSource,
            });

            msg.content = data.fullUrl;
            msg.ctx.downloaded = true;
            msg.ctx.url = data.url;
            msg.ctx.fullUrl = data.fullUrl;

            handler(msg, modal);
          },
        }),
      );
    }

    const insertCb = config.imageActions?.insert;
    if (typeof insertCb === 'function') {
      actionsContainer.append(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: icon(14, plus),
          label: 'Insert',
          completedText: 'Inserted!',
          loadingText: 'Downloading...',
          onClick: async (msg, modal) => {
            if (msg.ctx.downloaded === true) {
              insertCb(msg, modal);
              return;
            }
            const data = await executor.mgr.download.image({
              url: msg.content as string,
              field: config.field,
              namespace: config.namespace,
              resource: config.resource,
              mediaSource: config.image?.mediaSource,
            });

            msg.content = data.fullUrl;
            msg.ctx.downloaded = true;
            msg.ctx.url = data.url;
            msg.ctx.fullUrl = data.fullUrl;

            insertCb(msg, modal);
          },
        }),
      );
    }
  }

  messageElement.appendChild(actionsContainer);

  globalState.modal.chatMessages.appendChild(messageElement);

  messageElement.update = (msg) => {
    const textContent = Array.isArray(msg.content) ? msg.content[0].value : msg.content;
    const content = msg.type === 'image' ? `<img src="${textContent}" />` : nlToBr(textContent);
    shadow.updateContent(content);
  };

  return messageElement;
};

export const renderMessage = (msg: Message, config: LocalChatConfig) => {
  if (msg.hidden) {
    return;
  }

  if (msg.role === 'user') {
    const userMsg = addUserMessage(msg);
    userMsg.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });

    return userMsg;
  }

  if (msg.type === 'image') {
    return addAssistantMessage(msg, config);
  }

  return addAssistantMessage(msg, config);
};

export const copyToClipboard = async (message: Message) => {
  const textContent = Array.isArray(message.content) ? message.content[0].value : message.content;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    try {
      await navigator.clipboard.writeText(textContent);
    } catch {
      addErrorMessage(lng('modai.cmp.failed_copy'));
    }
  } else {
    try {
      const textarea = createElement('textarea');
      textarea.value = textContent;
      document.body.appendChild(textarea);
      textarea.select();

      document.execCommand('copy');
      document.body.removeChild(textarea);
    } catch {
      addErrorMessage(lng('modai.cmp.failed_copy'));
    }
  }
};
