import { globalState } from './globalState';

export type Permissions =
  | 'modai_client'
  | 'modai_client_chat_text'
  | 'modai_client_chat_image'
  | 'modai_client_text'
  | 'modai_client_vision';

export const checkPermissions = (permissions: Permissions[]) => {
  return permissions.every((permission) => {
    return globalState.config.permissions[permission];
  });
};
