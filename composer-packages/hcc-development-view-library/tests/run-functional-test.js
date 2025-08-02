const { spawn } = require('child_process');
const path = require('path');
const env = Object.create(process.env);
env.TEST_ENV = 'functional';

const phpunitPath = path.resolve(__dirname, '../lib/bin/phpunit');
const proc = spawn('php', [
  phpunitPath,
  '--display-warnings',
  '--testsuite', 'functional'
], { stdio: 'inherit', env });

proc.on('exit', code => process.exit(code));