import { lng } from '../lng';
import { ui } from '../ui';
import { button } from '../ui/dom/button';
import { icon } from '../ui/dom/icon';
import { createModAIShadow } from '../ui/dom/modAIShadow';
import { messageBot } from '../ui/icons';
import { createElement } from '../ui/utils';

import type { LocalChatConfig } from '../ui/localChat/types';

export const initGlobalButton = () => {
  const config: LocalChatConfig = {
    key: '_global',
    persist: true,
    availableTypes: ['text', 'image'],
    type: 'text',
  };

  if (!ui.localChat.verifyPermissions(config)) {
    return;
  }

  const li = createElement('li');
  const { shadow, shadowRoot } = createModAIShadow();

  const trigger = button(
    icon(24, messageBot),
    () => {
      ui.localChat.createModal(config);
    },
    'global-button',
    {
      title: lng('modai.ui.modai_assistant'),
    },
  );

  shadowRoot.appendChild(trigger);

  li.appendChild(shadow);

  const leftbarTrigger = document.getElementById('modx-leftbar-trigger');
  leftbarTrigger?.parentNode?.insertBefore(li, leftbarTrigger);
};
