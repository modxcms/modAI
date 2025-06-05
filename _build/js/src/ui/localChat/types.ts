import { AvailableAgent } from '../../index';
import { Select } from '../dom/select';

import type { UserInput } from './modalInput';
import type { AttachmentsWrapper } from './modalInputAttachments';
import type { ContextWrapper } from './modalInputContext';
import type { ChatHistory, Message, UserMessageContext } from '../../chatHistory';
import type { Button } from '../dom/button';

export type ModalType = 'text' | 'image';

export type Modal = HTMLDivElement & {
  modal: HTMLDivElement;
  welcomeMessage: HTMLDivElement;
  chatMessages: HTMLDivElement;
  chatContainer: HTMLDivElement;
  scrollWrapper: HTMLDivElement;
  loadingIndicator: HTMLDivElement;

  attachments: AttachmentsWrapper;
  context: ContextWrapper;
  messageInput: UserInput;

  modeButtons: Button[];
  controlButtons: (Button | Select)[];
  actionButtons: (Button | Select)[];
  stopBtn: Button;
  sendBtn: Button;
  closeModalBtn: Button;

  reloadChatControls: () => void;

  isDragging: boolean;
  isLoading: boolean;
  offsetX: number;
  offsetY: number;

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
