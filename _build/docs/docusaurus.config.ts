import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

const config: Config = {
  title: 'modAI',
  tagline: 'A generative AI Extra for MODX Revolution that helps you create content faster and optimize SEO effortlessly.',
  favicon: 'https://modx.com/favicon.svg',
  future: {
    v4: true,
  },

  url: 'https://modxcms.github.io',
  baseUrl: '/modAI/',
  organizationName: 'modxcms',
  projectName: 'modai',

  onBrokenLinks: 'throw',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

   headTags: [
    {
      tagName: 'meta',
      attributes: {
        name: 'algolia-site-verification',
        content: 'F19BA35AF4F36AA6',
      },
    },
  ],

  presets: [
    [
      'classic',
      {
        docs: {
          path: '../../docs',
          routeBasePath: '/',
          sidebarPath: './sidebars.ts',
          editUrl:
            'https://github.com/modxcms/modai/tree/main/_build/docs/',
        },
        blog: false,
        pages: false,
      } satisfies Preset.Options,
    ],
  ],

  themeConfig: {
    colorMode: {
      respectPrefersColorScheme: true,
    },
    navbar: {
      title: 'modAI',
      logo: {
        alt: 'modAI',
        src: 'https://modx.com/assets/themes/manana/images/logo-icon.svg',
      },
      items: [
        {
          href: 'https://github.com/modxcms/modai',
          label: 'GitHub',
          position: 'right',
        },
        {
          href: 'https://modx.com/get-help/',
          label: 'Get Help',
          position: 'right',
        },
      ],
    },
    footer: {
      links: [
        {
          label: 'X',
          href: 'https://x.com/modx',
        },
        {
          label: 'YouTube',
          href: 'https://www.youtube.com/modx',
        },
        {
          label: "Blog",
          href: "https://modx.com/blog/"
        },
        {
          label: "Support",
          href:"mailto:help@modx.com"
        }
      ],
      copyright: `Copyright © ${new Date().getFullYear()} MODX, LLC.`,
    },
    prism: {
      theme: prismThemes.github,
      darkTheme: prismThemes.palenight,
    },
    algolia: {
      appId: '46HN39MS5K',
      apiKey: '5ad56659f6fd5f2a0ad167fb33bb82f4',
      indexName: 'modai',
      contextualSearch: true,
      searchParameters: {},
      searchPagePath: 'search',
      insights: false,
    },
  } satisfies Preset.ThemeConfig,
};

export default config;
