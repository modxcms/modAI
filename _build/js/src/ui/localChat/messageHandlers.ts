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
  const messageWrapper: UpdatableHTMLElement<UserMessage> = createElement(
    'div',
    `message-wrapper user ${msg.init ? '' : 'new'}`,
  );
  messageWrapper.style.setProperty('--user-msg-height', '-10px');

  const messageElement = createElement('div', 'message user');

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

  if (imagesContent.length > 0) {
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

  messageWrapper.update = (msg) => {
    const textContent = Array.isArray(msg.content) ? msg.content[0].value : msg.content;
    textDiv.innerHTML = nlToBr(textContent);
  };

  messageWrapper.appendChild(messageElement);
  globalState.modal.chatMessages.appendChild(messageWrapper);

  return messageWrapper;
};

export const addErrorMessage = (content: string) => {
  globalState.modal.welcomeMessage.style.display = 'none';

  const messageWrapper: UpdatableHTMLElement = createElement('div', 'message-wrapper error new');
  const messageElement = createElement('div', 'message error');

  messageElement.appendChild(icon(14, triangleError));

  const textSpan = createElement('span');
  textSpan.textContent = content;
  messageElement.appendChild(textSpan);

  messageWrapper.appendChild(messageElement);

  globalState.modal.chatMessages.appendChild(messageWrapper);

  return messageWrapper;
};

export const addAssistantMessage = (msg: AssistantMessage, config: LocalChatConfig) => {
  const messageWrapper: UpdatableHTMLElement<AssistantMessage> = createElement(
    'div',
    `message-wrapper ai ${msg.init ? '' : 'new'}`,
  );
  if (globalState.modal.chatMessages.lastElementChild?.firstElementChild) {
    messageWrapper.style.setProperty(
      '--user-msg-height',
      `${globalState.modal.chatMessages.lastElementChild.firstElementChild.clientHeight}px`,
    );
  }

  const messageElement = createElement('div', 'message ai');
  messageElement.dataset.id = msg.id;
  const md = markdownit({
    html: true,
    xhtmlOut: true,
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

            msg = globalState.modal.history.updateMessage(msg, {
              content: data.fullUrl,
              ctx: { downloaded: true, url: data.url, fullUrl: data.fullUrl },
            });

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

  if (msg.metadata?.model) {
    const modelInfo = createElement(
      'div',
      'info',
      lng('modai.ui.model_info', { model: msg.metadata.model }),
    );
    messageElement.appendChild(modelInfo);
  }

  messageElement.appendChild(actionsContainer);

  messageWrapper.appendChild(messageElement);

  globalState.modal.chatMessages.appendChild(messageWrapper);

  messageWrapper.update = (msg) => {
    const content =
      msg.contentType === 'image' ? `<img src="${textContent}" />` : md.render(msg.content ?? '');
    shadow.updateContent(content);
  };

  return messageWrapper;
};

export const renderMessage = (msg: Message, config: LocalChatConfig) => {
  if (msg.hidden) {
    return;
  }

  if (msg.__type === 'UserMessage') {
    const el = addUserMessage(msg);
    el.firstElementChild?.scrollIntoView({
      behavior: msg.init === true ? 'instant' : 'smooth',
      block: 'start',
    });

    return el;
  }

  if (msg.__type === 'AssistantMessage') {
    const el = addAssistantMessage(msg, config);

    if (msg.init === true) {
      el.firstElementChild?.scrollIntoView({
        behavior: 'instant',
        block: 'start',
      });
    }

    return el;
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
