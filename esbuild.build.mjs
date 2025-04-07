import * as esbuild from 'esbuild';
import { typecheckPlugin } from '@jgoz/esbuild-plugin-typecheck';
import copyStaticFiles from 'esbuild-copy-static-files';

await esbuild.build({
  entryPoints: ['_build/js/src/index.ts'],
  bundle: true,
  platform: 'browser',
  format: 'iife',
  globalName: 'ModAI',
  minify: true,
  outfile: 'assets/components/modai/js/modai.js',
  plugins: [
    typecheckPlugin(),
    copyStaticFiles({
      src: './node_modules/highlight.js/styles/default.min.css',
      dest: './assets/components/modai/css/highlight.css',
    })
  ],
});
