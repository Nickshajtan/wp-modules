module.exports = {
  root: true,
  env: { es2023: true, node: true },
  extends: [
    'eslint:recommended',
    'plugin:import/recommended',
    'plugin:n/recommended',
    'plugin:promise/recommended',
    'plugin:prettier/recommended'
  ],
  parserOptions: { sourceType: 'module', ecmaVersion: 'latest' },
  rules: {
    'no-console': 'off',
    'import/no-unresolved': 'off',
    'n/no-unsupported-features/es-syntax': 'off',
    'prettier/prettier': ['error']
  },
  ignorePatterns: ['node_modules/', 'dist/', 'coverage/']
};
