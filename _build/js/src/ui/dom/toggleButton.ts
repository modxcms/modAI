import { createElement } from '../utils';
import { icon } from './icon';
import { tooltip } from './tooltip';

type State<T extends string = string> = { icon: string; label: string; name: T };

type Props<T extends readonly State[]> = {
  states: T;
  defaultState: T[number]['name'];
};

export const toggleButton = <T extends readonly [State, State, ...State[]]>(
  props: Props<T>,
  onToggle: (state: T[number]) => void | Promise<void>,
) => {
  const indexMap = new Map<string, number>();
  props.states.forEach((state, index) => {
    indexMap.set(state.name, index);
  });

  const defaultIndex = indexMap.get(props.defaultState);
  if (defaultIndex === undefined) {
    throw new Error(`Default state "${props.defaultState}" not found in states.`);
  }

  let currentState = defaultIndex;
  const nextIndex = (currentState + 1) % props.states.length;

  const toggleIcon = icon(24, props.states[defaultIndex].icon);
  const toggleTooltip = tooltip(props.states[nextIndex].label);

  const btn = createElement('button', '', [toggleIcon, toggleTooltip], {
    ariaLabel: props.states[nextIndex].label,
  });

  btn.addEventListener('click', () => {
    currentState = (currentState + 1) % props.states.length;
    const nextIndex = (currentState + 1) % props.states.length;
    const newState = props.states[currentState];

    toggleIcon.innerHTML = newState.icon;
    toggleTooltip.textContent = props.states[nextIndex].label;
    btn.ariaLabel = props.states[nextIndex].label;

    Promise.resolve(onToggle(newState));
  });

  return btn;
};
