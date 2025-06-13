import { ModeButton, UserInput } from './modalInput';
import { Select } from '../dom/select';

import type { AttachmentsWrapper } from './modalInputAttachments';
import type { ContextWrapper } from './modalInputContext';
import type { Sidebar } from './sidebar';
import type { ChatHistory, Message, UserMessageContext } from '../../chatHistory/types';
import type { Button } from '../dom/button';

export type ModalType = 'text' | 'image';

export type Modal = HTMLDivElement & {
  config: LocalChatConfig;
  modal: HTMLDivElement;
  welcomeMessage: HTMLDivElement;
  chatMessages: HTMLDivElement;
  chatContainer: HTMLDivElement;
  scrollWrapper: HTMLDivElement;
  portal: HTMLDivElement;
  sidebar?: Sidebar;

  attachments: AttachmentsWrapper;
  context: ContextWrapper;
  messageInput: UserInput;

  modeButtons: ModeButton[];
  modalButtons: (Button | Select)[];
  controlButtons: (Button | Select)[];
  actionButtons: (Button | Select)[];
  closeModalBtn: Button;

  reloadChatControls: () => void;
  setTitle: (title?: string) => void;
  disableSending: () => void;
  enableSending: () => void;

  isDragging: boolean;
  isLoading: boolean;
  offsetX: number;
  offsetY: number;

  chatId?: number;
  chatPublic: boolean;

  abortController?: AbortController;
  history: ChatHistory;

  api: {
    sendMessage: (providedMessage?: string, hidePrompt?: boolean) => Promise<void>;
    closeModal: () => void;
  };
};

export type LocalChatConfig = {
  key: string;
  persist?: boolean;
  type: ModalType;
  availableTypes?: ModalType[];
  namespace?: string;
  /**
   * @deprecated use 'withContexts'
   */
  context?: string;
  withContexts?: UserMessageContext[];
  field?: string;
  resource?: number | string;
  customCSS?: string[];
  textActions?: {
    copy?: boolean | ((message: Message, modal: Modal) => void);
    insert?: (message: Message, modal: Modal) => void;
  };
  imageActions?: {
    copy?: boolean | ((message: Message, modal: Modal) => void);
    insert?: (message: Message, modal: Modal) => void;
    download?: boolean | ((message: Message, modal: Modal) => void);
  };
  image?: {
    mediaSource?: number | string;
    path?: string;
  };
};

export type ModalState = {
  position?: {
    width?: string;
    height?: string;
    left?: string;
    top?: string;
  };
};
