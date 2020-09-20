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

$siteData           = new \PostSynchronization\SiteData();
$siteData->url      = 'http://gdzienazabieg.test/';
$siteData->user     = 'admin';
$siteData->password = 'password';

add_action( 'save_post_post', function ( $postId, \WP_Post $post, $update ) use ( $siteData ) {

	$action = $update ?
		\PostSynchronization\Actions::update( $siteData ) :
		\PostSynchronization\Actions::create( $siteData );

	/** @var \WPML\FP\Either $result */
	$result = $action( $post );

	if ( \WPML\FP\Fns::isLeft( $result ) ) {
		error_log( $result->orElse( \WPML\FP\Fns::identity() )->get() );
	}

}, 10, 3 );


if ( isset( $_GET['jakub'] ) ) {

	$create = \GdzieNaZabieg\PostSynchronization\Actions::create( $siteData );

//	$api_response = wp_remote_post( 'http://gdzienazabieg.test/wp-json/wp/v2/posts', [
//		'headers' => [
//			'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
//		],
//		'body'    => [
//			'title'      => 'My test',
//			'status'     => 'draft', // ok, we do not want to publish it immediately
//			'content'    => 'lalala',
//			'categories' => 3, // category ID
//			'excerpt'    => 'Read this awesome post',
//			'slug'       => 'new-test-post',
//		]
//	] );

//	$post = new \WP_Post()
//
//	$api_response = $create()

	$body = json_decode( $api_response['body'] );

	print_r( $body ); // or print_r( $api_response );

	if ( wp_remote_retrieve_response_message( $api_response ) === 'Created' ) {
		echo 'The post ' . $body->title->rendered . ' has been created successfully';
	}
	die;
}