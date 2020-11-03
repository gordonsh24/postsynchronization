<?php


namespace PostSynchronization\Migrations;


use PostSynchronization\OnPostSave;
use PostSynchronization\SiteData;
use PostSynchronization\SitesConfiguration;
use WPML\FP\Fns;
use WPML\FP\Maybe;
use WPML\FP\Obj;
use function WPML\FP\pipe;

class Resync {

	public static function run( $observer ) {
		$_POST['tmp'] = true;

		$logSite = Fns::tap( function ( SiteData $site ) use ( $observer ) {
			call_user_func( $observer, 'Site: ' . $site->name );
		} );

		$getPost = Fns::unary( '\get_post' );

		$isPostNotEmpty = function ( $post ) {
			return $post && Obj::prop( 'post_title', $post );
		};

		$logPost = Fns::tap( function ( $post ) use ( $observer ) {
			call_user_func( $observer, sprintf( 'Post (%d) -> %s', $post->ID, $post->post_title ) );
		} );

		$syncPost = pipe( $logPost, Fns::converge( OnPostSave::onPostSave(), [ Obj::prop( 'ID' ), Fns::identity() ] ) );

		$syncPosts = pipe(
			Fns::map( $getPost ),
			Fns::filter( $isPostNotEmpty ),
			Fns::map( $syncPost )
		);

		\wpml_collect( SitesConfiguration::get() )
			->map( $logSite )
			->map( self::getAllPostsFromSite() )
			->map( $syncPosts );


	}

	private static function getAllPostsFromSite() {
		return function ( SiteData $site ) {
			global $wpdb;

			$sql = "
			SELECT source_id 
			FROM {$wpdb->prefix}wp_ps_mapping
			WHERE site_name = %s
		";

			return $wpdb->get_col( $wpdb->prepare( $sql, $site->name ) );
		};
	}
}