<?php


// First we need to load the composer autoloader so we can use WP Mock
require_once __DIR__ . '/../../vendor/autoload.php';

define( 'WP_PLUGIN_DIR', realpath( dirname( __FILE__ ) . '/../../' ) );

// Now call the bootstrap method of WP Mock
WP_Mock::bootstrap();

use tad\FunctionMocker\FunctionMocker;

require_once WP_PLUGIN_DIR . '/vendor/wpml/wp/tests/mocks/OptionMock.php';
require_once WP_PLUGIN_DIR . '/vendor/wpml/wp/tests/mocks/PostMock.php';

require_once './mocks/RemotePostMock.php';
require_once './mocks/MediaMock.php';
require_once './mocks/TaxonomiesMock.php';
require_once './mocks/MapperMock.php';
require_once './mocks/TransientMock.php';

! defined( 'POST_SYNC_SITES' ) && define( 'POST_SYNC_SITES', [
	[
		'name'          => 'gdzienazabieg',
		'url'           => 'http://gdzienazabieg.test/',
		'user'          => 'admin',
		'password'      => 'password',
		'categoriesMap' => [
			2 => 4,
			3 => 2,
		],
		'tagsMap'       => [
			6  => 26,
			8  => 28,
			11 => 31,
		],
		'authorsMap'    => [
			1 => 11,
			2 => 12,
		],
	],
	[
		'name'          => 'develop',
		'url'           => 'http://develop.test/',
		'user'          => 'admin',
		'password'      => 'password',
		'categoriesMap' => [],
	]
] );

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