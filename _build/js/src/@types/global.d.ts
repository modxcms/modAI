declare const modAI: {
  apiURL: string;
  resourceFields?: string[];
  tvs?: string[];
};

declare const MODx: {
  config: Record<string, string>;
  request: Record<string, string>;
};

declare namespace Ext {
  export function onReady(fn: () => void): void;
  export function defer(fn: () => void, timeout: number): void;
  export function get(id: string): Ext.Element;
  export function getCmp(id?: string): Ext.form.Field;

  class Msg {
    static alert(title: string, description: string): void;
  }
  class Component {
    el: Ext.Element;
    xtype: string;
    getForm(): Ext.form.BasicForm;
  }

  class Element {
    dom: HTMLElement;
  }

  namespace form {
    class BasicForm extends Component {
      findField(id: string): Ext.form.Field | undefined;
    }

    class Field extends Component {
      getValue(): string | undefined;
      setValue(value: unknown): void;
      fireEvent(event: string, ...args: unknown[]): void;

      label: HTMLElement;

      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      [key: string]: any;
    }
  }
}
