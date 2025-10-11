// eslint.config.js (flat config для ESLint v9+)
import js from '@eslint/js';
import importPlugin from 'eslint-plugin-import';
import nPlugin from 'eslint-plugin-n';
import promisePlugin from 'eslint-plugin-promise';
import prettierPlugin from 'eslint-plugin-prettier';

export default [
  js.configs.recommended,
  {
    files: ['**/*.{js,mjs,cjs}'],
    ignores: ['**/node_modules/**', '**/dist/**', '**/coverage/**'],
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
    },
    plugins: {
      import: importPlugin,
      n: nPlugin,
      promise: promisePlugin,
      prettier: prettierPlugin,
    },
    rules: {
      'no-console': 'off',
      'n/no-unsupported-features/es-syntax': 'off',
      'import/no-unresolved': 'off',
      'promise/always-return': 'off',
      'prettier/prettier': 'error',
    },
  },
  {
    files: ['**/*.{test,spec}.{js,mjs,cjs}'],
    languageOptions: {
      globals: {
        vi: 'readonly',
        describe: 'readonly',
        it: 'readonly',
        expect: 'readonly',
        beforeEach: 'readonly',
        afterEach: 'readonly',
      },
    },
  },
];
