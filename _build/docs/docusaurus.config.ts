import {themes as prismThemes} from 'prism-react-renderer';
import type {Config} from '@docusaurus/types';
import type * as Preset from '@docusaurus/preset-classic';

const config: Config = {
  title: 'modAI',
  tagline: 'A generative AI Extra for MODX Revolution that helps you create content faster and optimize SEO effortlessly.',
  favicon: 'https://modx.com/favicon.svg',

  // Future flags, see https://docusaurus.io/docs/api/docusaurus-config#future
  future: {
    v4: true, // Improve compatibility with the upcoming Docusaurus v4
  },

  // Set the production url of your site here
  url: 'https://modxcms.github.io',
  // Set the /<baseUrl>/ pathname under which your site is served
  // For GitHub pages deployment, it is often '/<projectName>/'
  baseUrl: '/modAI/',
  organizationName: 'modxcms',
  projectName: 'modai',

  onBrokenLinks: 'throw',

  i18n: {
    defaultLocale: 'en',
    locales: ['en'],
  },

  presets: [
    [
      'classic',
      {
        docs: {
          path: '../../docs',
          routeBasePath: '/',
          sidebarPath: './sidebars.ts',
          editUrl:
            'https://github.com/modxcms/modai/tree/main/docs/',
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
  } satisfies Preset.ThemeConfig,
};

export default config;
