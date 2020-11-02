<?php
/**
 * @package PostSynchronization
 */
/*
Plugin Name: Post synchronization
Description: It allows syncing posts between different sites
Version: 0.0.1
Author: Jakub Bis
Text Domain: postsynchronization
*/

require_once 'vendor/autoload.php';
require_once 'classes/wrapper-functions.php';

\PostSynchronization\Initializer::addHooks();

if ( defined( 'WP_CLI' ) ) {
	\PostSynchronization\Migrations\Migrations::defineCommand();
}

//$r = \PostSynchronization\Tags\Sync::createIfNotExist( \PostSynchronization\SitesConfiguration::getByName( 'gdzienazabieg.test' ), 'TagM' );
//var_dump( $r );
//die;