import * as esbuild from 'esbuild';
import { typecheckPlugin } from '@jgoz/esbuild-plugin-typecheck';
import copyStaticFiles from 'esbuild-copy-static-files';

const ctx = await esbuild.context({
  entryPoints: ['_build/js/src/index.ts'],
  bundle: true,
  platform: 'browser',
  format: 'iife',
  globalName: 'ModAI',
  sourcemap: 'inline',
  outfile: 'assets/components/modai/js/modai.js',
  plugins: [
    typecheckPlugin({
      watch: true,
    }),
    copyStaticFiles({
      src: './node_modules/highlight.js/styles/default.min.css',
      dest: './assets/components/modai/css/highlight.css',
    })
  ],
});

await ctx.watch();

process.on('beforeExit', () => ctx.dispose());
