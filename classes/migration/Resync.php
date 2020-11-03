<?php


namespace PostSynchronization\Migrations;


use PostSynchronization\OnPostSave;
use WPML\FP\Fns;
use WPML\FP\Obj;
use function WPML\FP\pipe;

class Resync {

	public static function run( $observer ) {
		$_POST['tmp'] = true;

		$getPost = Fns::unary( '\get_post' );

		$logPost = Fns::tap( function ( $post ) use ( $observer ) {
			call_user_func( $observer, sprintf( 'Post (%d) -> %s', $post->ID, $post->post_title ) );
		} );

		$syncPost = pipe( $logPost, Fns::converge( OnPostSave::onPostSave(), [ Obj::prop( 'ID' ), Fns::identity() ] ) );


		\wpml_collect( self::getAllPosts() )
			->map( $getPost )
			->filter()
			->map( $syncPost );
	}

	private static function getAllPosts() {
		global $wpdb;

		$sql = "
			SELECT DISTINCT source_id 
			FROM {$wpdb->prefix}wp_ps_mapping
		";

		return $wpdb->get_col( $sql );
	}
}