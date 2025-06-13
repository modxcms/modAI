import { createElement, nlToBr } from '../utils';
import { checkScroll } from './scrollBottom';
import { globalState } from '../../globalState';

type ShadowElement = HTMLDivElement & {
  updateContent: (content: string) => void;
};

const shadowStyles = `
    html {
        width: 100%;
        max-width: 100%;
        padding: 0;
        margin: 0;
    }
    body {
        background-color: #F1F1F2;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        overflow-x: hidden;
        width: 100%;
        max-width: 100%;
        box-sizing: border-box;
    }
    * {
        box-sizing: border-box;
        max-width: 100%;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        overflow-wrap: break-word;
        max-width: 100%;
        padding: 12px;
        margin: 0;
        background-color: #f5f7fa;
        border-radius: 5px;
        font-size: 14px;
    }
    img {
        max-width: 100%;
        height: auto;
        display: block;
    }
    code {
        white-space: pre-wrap;
        word-wrap: break-word;
        max-width: 100%;
        display: block;
        overflow-x: hidden;
        font-size: 14px;
    }
    table {
        width: 100%;
        max-width: 100%;
        overflow-x: hidden;
        display: block;
        border-collapse: collapse;
    }
    div {
        max-width: 100%;
        overflow-wrap: break-word;
        word-wrap: break-word;
        box-sizing: border-box;
    }
    p {
        margin: 0 0 0.5em 0;
        max-width: 100%;
    }
    h1, h2, h3, h4, h5, h6 {
        margin: 0 0 0.1em 0;
        max-width: 100%;
    }
`;

export const createShadowElement = (content: string | HTMLElement, customCSS: string[] = []) => {
  const shadow = createElement('div') as ShadowElement;
  shadow.updateContent = (content) => {
    textDiv.innerHTML = nlToBr(content);
    checkScroll();
  };

  const textDiv = createElement('div');
  if (typeof content === 'string') {
    textDiv.innerHTML = nlToBr(content);
  } else {
    textDiv.append(content);
  }

  const shadowRoot = shadow.attachShadow({ mode: 'open' });

  const styleElement = createElement('style');
  styleElement.textContent = shadowStyles;
  shadowRoot.appendChild(styleElement);

  customCSS.forEach((href) => {
    shadowRoot.appendChild(
      createElement('link', undefined, '', {
        rel: 'stylesheet',
        type: 'text/css',
        href,
      }),
    );
  });

  const highlightStyles = createElement('link', undefined, '', {
    rel: 'stylesheet',
    type: 'text/css',
    href: `${globalState.config.assetsURL}css/highlight.css`,
  });

  shadowRoot.appendChild(highlightStyles);

  shadowRoot.append(textDiv);

  return shadow;
};
