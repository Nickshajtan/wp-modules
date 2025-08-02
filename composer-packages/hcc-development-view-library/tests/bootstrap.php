<?php

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'lib/autoload.php';

if ( 'functional' === getenv('TEST_ENV') ) {
    require_once __DIR__ . '/functional/_dependencies/vendor/autoload.php';
}
