export default {
  '*.{js,mjs,cjs,json,yml,yaml,css,scss,md}': ['prettier --write'],
  '*.{js,mjs,cjs}': ['eslint --fix --config ./eslint.config.js'],
  '*.md': ['markdown-toc -i', 'prettier --write'],
};
