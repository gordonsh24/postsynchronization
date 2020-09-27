<?php


// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/../../vendor/autoload.php';

define( 'WP_PLUGIN_DIR', realpath( dirname( __FILE__ ) . '/../../' ) );

// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

use tad\FunctionMocker\FunctionMocker;

FunctionMocker::init(
	[
		'blacklist' => [
			realpath( WP_PLUGIN_DIR ),
		],
		'whitelist' => [
			realpath( WP_PLUGIN_DIR . '/classes' ),
		],
		'redefinable-internals' => [
			'error_log',
		],
	]
);