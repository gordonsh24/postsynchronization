<?php


namespace PostSynchronization\Migrations;


use PostSynchronization\OnPostSave;
use PostSynchronization\RestUtils;
use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Obj;
use function WPML\FP\pipe;

class Resync {

	public static function run( $observer ) {
		RestUtils::$timeout = 15;
		RestUtils::$logFailedRequest = true;
		$_POST['tmp'] = true;

		$getPost = Fns::unary( '\get_post' );

		$logPost = function ( $post, $index, $count ) use ( $observer ) {
			call_user_func( $observer, sprintf( '%d / %d - Post (%d) -> %s', $index, $count, $post->ID, $post->post_title ) );
		};

		$syncPost = Fns::converge( OnPostSave::onPostSave(), [ Obj::prop( 'ID' ), Fns::identity() ] );

		$posts = pipe( Fns::map( $getPost ), Fns::filter( Logic::isNotNull() ) )( self::getAllPosts() );
		foreach ( $posts as $index => $post ) {
			$logPost( $post, $index, count( $posts ) );
			$syncPost( $post );
		}
	}

	private static function getAllPosts() {
		global $wpdb;

		$sql = "
			SELECT DISTINCT source_id 
			FROM {$wpdb->prefix}wp_ps_mapping
			WHERE `type` != 'media' 
		";

		return $wpdb->get_col( $sql );
	}
}