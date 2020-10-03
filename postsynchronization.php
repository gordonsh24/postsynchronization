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

\PostSynchronization\Initializer::addHooks();
