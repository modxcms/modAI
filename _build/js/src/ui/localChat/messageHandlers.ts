import hljs from 'highlight.js';
import markdownit from 'markdown-it';

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
import type {
  AssistantMessage,
  Message,
  UpdatableHTMLElement,
  UserMessage,
} from '../../chatHistory';

export const addUserMessage = (msg: UserMessage) => {
  const messageElement: UpdatableHTMLElement<UserMessage> = createElement('div', 'message user');

  const textContent = msg.content;
  const imagesContent = [];

  for (const attachment of msg.attachments ?? []) {
    if (attachment.__type === 'image') {
      imagesContent.push(attachment.value);
    }
  }

  const textDiv = createElement('div');
  textDiv.innerHTML = nlToBr(textContent);
  messageElement.appendChild(textDiv);

  if (imagesContent) {
    const imageRow = createElement('div', 'imageRow');

    for (const imgContent of imagesContent) {
      const imageWrapper = createElement('div');

      const img = createElement('img');
      img.src = imgContent;

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
      icon: icon(14, copy),
      label: lng('modai.ui.copy'),
      completedText: lng('modai.ui.copied'),
      onClick: copyToClipboard,
    }),
  );

  actionsContainer.appendChild(
    createActionButton({
      message: msg,
      disabled: globalState.modal.isLoading,
      disableCompletedState: true,
      icon: icon(14, edit),
      label: lng('modai.ui.edit'),
      onClick: (msg) => {
        confirmDialog({
          title: lng('modai.ui.confirm_edit'),
          content: lng('modai.ui.confirm_edit_content'),
          confirmText: lng('modai.ui.edit_message'),
          onConfirm: () => {
            globalState.modal.messageInput.setValue(msg.content);

            if (msg.contexts) {
              msg.contexts.forEach((ctx) => {
                globalState.modal.context.addContext(ctx);
              });
            }

            if (msg.attachments) {
              msg.attachments.forEach((ctx) => {
                if (ctx.__type === 'image') {
                  globalState.modal.attachments.addImageAttachment(ctx.value);
                }
              });
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

export const addAssistantMessage = (msg: AssistantMessage, config: LocalChatConfig) => {
  const messageElement: UpdatableHTMLElement<AssistantMessage> = createElement('div', 'message ai');
  messageElement.dataset.id = msg.id;

  const md = markdownit({
    linkify: true,
    typographer: true,
    breaks: true,
    highlight: function (str, lang) {
      if (lang && hljs.getLanguage(lang)) {
        try {
          return hljs.highlight(str, { language: lang }).value;
        } catch {
          /* not needed */
        }
      }

      return ''; // use external default escaping
    },
  });

  let textContent = msg.content || '';
  if (msg.contentType === 'image') {
    textContent = `<img src="${textContent}" />`;
  } else {
    textContent = md.render(textContent);
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
          label: lng('modai.ui.copy'),
          completedText: lng('modai.ui.copied'),
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
          label: lng('modai.ui.insert'),
          completedText: lng('modai.ui.inserted'),
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
          label: lng('modai.ui.copy'),
          loadingText: lng('modai.ui.downloading'),
          completedText: lng('modai.ui.copied'),
          onClick: async (msg, modal) => {
            const handler =
              typeof config.textActions?.copy === 'function'
                ? config.textActions.copy
                : copyToClipboard;

            if (msg.ctx.downloaded === true) {
              handler(msg, modal);
              return;
            }
            const data = await executor.download.image({
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
          label: lng('modai.ui.insert'),
          completedText: lng('modai.ui.inserted'),
          loadingText: lng('modai.ui.downloading'),
          onClick: async (msg, modal) => {
            if (msg.ctx.downloaded === true) {
              insertCb(msg, modal);
              return;
            }
            const data = await executor.download.image({
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
    const content =
      msg.contentType === 'image' ? `<img src="${textContent}" />` : md.render(msg.content ?? '');
    shadow.updateContent(content);
  };

  return messageElement;
};

export const renderMessage = (msg: Message, config: LocalChatConfig) => {
  if (msg.hidden) {
    return;
  }

  if (msg.__type === 'UserMessage') {
    const userMsg = addUserMessage(msg);
    userMsg.scrollIntoView({
      behavior: 'smooth',
      block: 'start',
    });

    return userMsg;
  }

  if (msg.__type === 'AssistantMessage') {
    return addAssistantMessage(msg, config);
  }

  return;
};

export const copyToClipboard = async (message: AssistantMessage | UserMessage) => {
  if (!message.content) return;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    try {
      await navigator.clipboard.writeText(message.content);
    } catch {
      addErrorMessage(lng('modai.error.failed_copy'));
    }
  } else {
    try {
      const textarea = createElement('textarea');
      textarea.value = message.content;
      document.body.appendChild(textarea);
      textarea.select();

      document.execCommand('copy');
      document.body.removeChild(textarea);
    } catch {
      addErrorMessage(lng('modai.error.failed_copy'));
    }
  }
};
