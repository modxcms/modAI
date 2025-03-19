import type { Config } from './index';
import type { Modal } from './ui/localChat/types';

export const globalState = {
  modalOpen: false,
  alertOpen: false,
  config: {} as Config,
  modal: {} as Modal,
};
