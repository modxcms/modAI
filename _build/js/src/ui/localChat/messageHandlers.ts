import hljs from 'highlight.js';
import markdownit from 'markdown-it';

import { createActionButton } from './actionButton';
import { createShadowElement } from './shadow';
import { executor } from '../../executor';
import { globalState } from '../../globalState';
import { lng } from '../../lng';
import { confirmDialog } from '../cofirmDialog';
import { icon } from '../dom/icon';
import { copy, download, edit, plus, refresh, textSelect, triangleError } from '../icons';
import { createElement, nlToBr } from '../utils';
import { scrollToBottom, sendMessage } from './modalActions';

import type {
  AssistantMessage,
  Message,
  UpdatableHTMLElement,
  UserAttachment,
  UserMessage,
  UserMessageContext,
} from '../../chatHistory/types';

const contextRenderers: Record<string, undefined | ((context: UserMessageContext) => HTMLElement)> =
  {
    selection: (ctx) => {
      const tooltipEl = createElement('span', 'tooltip', ctx.value, { tabIndex: -1 });
      const btn = createElement('div', 'context', [icon(24, textSelect), tooltipEl], {
        tabIndex: 0,
      });

      btn.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
          e.preventDefault();
          const scrollAmount = 30;
          tooltipEl.scrollTop += e.key === 'ArrowDown' ? scrollAmount : -scrollAmount;
        }
      });

      return btn;
    },
  };

const attachmentRenderers: Record<
  string,
  undefined | ((attachment: UserAttachment) => HTMLElement)
> = {
  image: (attachment) => {
    return createElement(
      'div',
      'attachment imagePreview',
      createElement('img', undefined, undefined, { src: attachment.value }),
    );
  },
};

export const addUserMessage = (msg: UserMessage) => {
  const messageWrapper: UpdatableHTMLElement<UserMessage> = createElement(
    'div',
    `message-wrapper user ${msg.init ? '' : 'new'}`,
  );

  const messageElement = createElement('div', 'message user');

  const textContent = msg.content;

  const textDiv = createElement('div');
  textDiv.innerHTML = nlToBr(textContent);
  messageElement.appendChild(textDiv);

  const attachmentsWrapper = createElement('div', 'attachmentsWrapper');
  const contextsWrapper = createElement('div', 'contextsWrapper');

  for (const attachment of msg.attachments ?? []) {
    const renderer = attachmentRenderers[attachment.__type];
    if (!renderer) {
      continue;
    }

    attachmentsWrapper.appendChild(renderer(attachment));
    if (!attachmentsWrapper.classList.contains('visible')) {
      attachmentsWrapper.classList.add('visible');
    }
  }

  for (const context of msg.contexts ?? []) {
    const renderer = context.renderer && contextRenderers[context.renderer];
    if (!renderer) {
      continue;
    }

    contextsWrapper.appendChild(renderer(context));
    if (!contextsWrapper.classList.contains('visible')) {
      contextsWrapper.classList.add('visible');
    }
  }

  const inputAddons = createElement('div', 'inputAddons', [attachmentsWrapper, contextsWrapper]);
  messageElement.appendChild(inputAddons);

  const actionsContainer = createElement('div', 'actions');

  actionsContainer.appendChild(
    createActionButton({
      message: msg,
      disabled: globalState.modal.isLoading,
      disableCompletedState: true,
      icon: refresh,
      label: lng('modai.ui.retry_message'),
      onClick: (msg) => {
        confirmDialog({
          title: lng('modai.ui.confirm_retry_message'),
          content: lng('modai.ui.confirm_edit_retry_message'),
          confirmText: lng('modai.ui.retry_message'),
          onConfirm: async () => {
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

            await globalState.modal.history.clearHistoryFrom(msg.id);
            if (globalState.modal.history.getMessages().length === 0) {
              globalState.modal.welcomeMessage.style.display = 'block';
            }

            void sendMessage();
          },
        });
      },
    }),
  );

  actionsContainer.appendChild(
    createActionButton({
      message: msg,
      disabled: globalState.modal.isLoading,
      disableCompletedState: true,
      icon: edit,
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

  actionsContainer.appendChild(
    createActionButton({
      message: msg,
      disabled: globalState.modal.isLoading,
      icon: copy,
      label: lng('modai.ui.copy'),
      completedText: lng('modai.ui.copied'),
      onClick: copyToClipboard,
    }),
  );

  messageElement.appendChild(actionsContainer);

  messageWrapper.update = (msg) => {
    const textContent = Array.isArray(msg.content) ? msg.content[0].value : msg.content;
    textDiv.innerHTML = nlToBr(textContent);
  };

  messageWrapper.appendChild(messageElement);
  const lastMsg = globalState.modal.chatMessages.lastElementChild;
  globalState.modal.chatMessages.appendChild(messageWrapper);

  if (lastMsg) {
    lastMsg.classList.remove('new');
  }

  messageWrapper.syncHeight = () => {
    const userMsgHeight = messageElement.clientHeight;
    const msgHeight = userMsgHeight < 100 ? -10 : -1 * (userMsgHeight - 62);
    messageWrapper.style.setProperty(
      '--user-msg-height',
      `${globalState.modal.chatContainer.clientHeight - msgHeight - 50}px`,
    );
  };

  messageWrapper.syncHeight?.();

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

export const addAssistantMessage = (msg: AssistantMessage) => {
  const config = globalState.modal.config;

  const messageWrapper: UpdatableHTMLElement<AssistantMessage> = createElement(
    'div',
    `message-wrapper ai ${msg.init ? '' : 'new'}`,
  );

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

      return '';
    },
  });

  let textContent: string | HTMLElement = msg.content || '';
  if (msg.contentType === 'image') {
    const img = createElement('img', '', '', {
      src: textContent || `${globalState.config.assetsURL}images/no-image.png`,
    });

    const urls = Array.isArray(msg.ctx.allUrls) ? msg.ctx.allUrls : [];
    let needSync = false;

    img.onerror = () => {
      needSync = true;
      if (urls.length > 0) {
        const nextUrl = urls.pop();
        msg.content = nextUrl;
        img.src = nextUrl;
      } else {
        msg.content = '';
        img.src = `${globalState.config.assetsURL}images/no-image.png`;
        actionsContainer.innerHTML = '';
      }
    };

    img.onload = () => {
      if (!needSync) {
        return;
      }

      msg.ctx.allUrls = urls;
      msg = globalState.modal.history.updateMessage(msg, {
        content: msg.content,
        ctx: msg.ctx,
      });

      if (config.persist && globalState.modal.chatId) {
        void executor.chat.storeMessage(globalState.modal.chatId, msg);
      }
    };

    textContent = img;
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
          icon: copy,
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
          icon: plus,
          label: lng('modai.ui.insert'),
          completedText: lng('modai.ui.inserted'),
          onClick: config.textActions.insert,
        }),
      );
    }
  }

  if (config.type === 'image' && msg.content) {
    if (config.imageActions?.copy !== false) {
      actionsContainer.append(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: copy,
          label: lng('modai.ui.copy'),
          loadingText: lng('modai.ui.downloading'),
          completedText: lng('modai.ui.copied'),
          onClick: async (msg, modal) => {
            const handler =
              typeof config.textActions?.copy === 'function'
                ? config.textActions.copy
                : copyToClipboard;

            if (!msg.content) {
              return;
            }

            const data = await executor.download.image({
              messageId: msg.id,
              field: config.field,
              namespace: config.namespace,
              resource: config.resource,
              mediaSource: config.image?.mediaSource,
              path: config.image?.path,
            });

            if (!msg.ctx.allUrls) {
              msg.ctx.allUrls = [];
            }

            const allUrls = Array.isArray(msg.ctx.allUrls) ? msg.ctx.allUrls : [];
            if (data.fullUrl !== msg.content && allUrls.indexOf(msg.content) === -1) {
              allUrls.push(msg.content);
            }

            msg.content = data.fullUrl;
            msg.ctx.downloaded = true;
            msg.ctx.url = data.url;
            msg.ctx.fullUrl = data.fullUrl;
            msg.ctx.allUrls = allUrls;

            msg = globalState.modal.history.updateMessage(msg, {
              content: data.fullUrl,
              ctx: { downloaded: true, url: data.url, fullUrl: data.fullUrl, allUrls },
            });

            if (config.persist && globalState.modal.chatId) {
              void executor.chat.storeMessage(globalState.modal.chatId, msg);
            }

            handler(msg, modal);
          },
        }),
      );
    }

    if (config.imageActions?.download) {
      actionsContainer.append(
        createActionButton({
          message: msg,
          disabled: globalState.modal.isLoading,
          icon: download,
          label: lng('modai.ui.download'),
          loadingText: lng('modai.ui.downloading'),
          completedText: lng('modai.ui.downloaded'),
          onClick: async (msg, modal) => {
            if (!msg.content) {
              return;
            }

            const handler =
              typeof config.imageActions?.download === 'function'
                ? config.imageActions.download
                : null;

            const data = await executor.download.image({
              messageId: msg.id,
              forceDownload: true,
              field: config.field,
              namespace: config.namespace,
              resource: config.resource,
              mediaSource: config.image?.mediaSource,
              path: config.image?.path,
            });

            if (!msg.ctx.allUrls) {
              msg.ctx.allUrls = [];
            }

            const allUrls = Array.isArray(msg.ctx.allUrls) ? msg.ctx.allUrls : [];
            if (data.fullUrl !== msg.content && allUrls.indexOf(msg.content) === -1) {
              allUrls.push(msg.content);
            }

            msg.content = data.fullUrl;
            msg.ctx.downloaded = true;
            msg.ctx.url = data.url;
            msg.ctx.fullUrl = data.fullUrl;
            msg.ctx.allUrls = allUrls;

            msg = globalState.modal.history.updateMessage(msg, {
              content: data.fullUrl,
              ctx: { downloaded: true, url: data.url, fullUrl: data.fullUrl, allUrls },
            });

            if (config.persist && globalState.modal.chatId) {
              void executor.chat.storeMessage(globalState.modal.chatId, msg);
            }

            handler?.(msg, modal);
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
          icon: plus,
          label: lng('modai.ui.insert'),
          completedText: lng('modai.ui.inserted'),
          loadingText: lng('modai.ui.downloading'),
          onClick: async (msg, modal) => {
            if (!msg.content) {
              return;
            }

            const data = await executor.download.image({
              messageId: msg.id,
              field: config.field,
              namespace: config.namespace,
              resource: config.resource,
              mediaSource: config.image?.mediaSource,
              path: config.image?.path,
            });

            if (!msg.ctx.allUrls) {
              msg.ctx.allUrls = [];
            }

            const allUrls = Array.isArray(msg.ctx.allUrls) ? msg.ctx.allUrls : [];
            if (data.fullUrl !== msg.content && allUrls.indexOf(msg.content) === -1) {
              allUrls.push(msg.content);
            }

            msg.content = data.fullUrl;
            msg.ctx.downloaded = true;
            msg.ctx.url = data.url;
            msg.ctx.fullUrl = data.fullUrl;
            msg.ctx.allUrls = allUrls;

            msg = globalState.modal.history.updateMessage(msg, {
              content: data.fullUrl,
              ctx: { downloaded: true, url: data.url, fullUrl: data.fullUrl, allUrls },
            });

            if (config.persist && globalState.modal.chatId) {
              void executor.chat.storeMessage(globalState.modal.chatId, msg);
            }

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

  const lastMsg = globalState.modal.chatMessages.lastElementChild;

  globalState.modal.chatMessages.appendChild(messageWrapper);

  if (lastMsg) {
    lastMsg.classList.remove('new');
  }

  messageWrapper.syncHeight = () => {
    if (lastMsg?.firstElementChild) {
      const msgHeight = lastMsg.classList.contains('user')
        ? lastMsg.firstElementChild.clientHeight < 100
          ? lastMsg.firstElementChild.clientHeight
          : lastMsg.firstElementChild.clientHeight -
            (lastMsg.firstElementChild.clientHeight - 62) +
            10
        : -10;
      messageWrapper.style.setProperty(
        '--user-msg-height',
        `${globalState.modal.chatContainer.clientHeight - msgHeight - 50}px`,
      );
    }
  };

  messageWrapper.update = (msg) => {
    const content =
      msg.contentType === 'image' ? `<img src="${textContent}" />` : md.render(msg.content ?? '');
    shadow.updateContent(content);
  };

  messageWrapper.syncHeight?.();

  return messageWrapper;
};

export const renderMessage = (msg: Message) => {
  if (msg.hidden) {
    return;
  }

  if (msg.__type === 'UserMessage') {
    const el = addUserMessage(msg);
    if (!msg.init) {
      scrollToBottom('smooth');
    }

    return el;
  }

  if (msg.__type === 'AssistantMessage') {
    const el = addAssistantMessage(msg);
    if (!msg.init && !el.previousElementSibling?.classList.contains('user')) {
      scrollToBottom('smooth');
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
