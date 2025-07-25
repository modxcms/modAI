import type { AvailableAgent, Config } from './index';
import type { Modal } from './ui/localChat/types';

export const globalState = {
  modalOpen: false,
  config: {} as Config,
  modal: {} as Modal,
  selectedAgent: {} as Record<string, AvailableAgent | undefined>,
  additionalControls: {} as Record<
    string,
    Record<string, { name: string; value: string } | undefined> | undefined
  >,
};
