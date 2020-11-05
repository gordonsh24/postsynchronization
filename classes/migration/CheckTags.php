<?php

namespace PostSynchronization\Migrations;

use PostSynchronization\Posts\API;
use PostSynchronization\RestUtils;
use PostSynchronization\SitesConfiguration;
use WPML\FP\Fns;
use WPML\FP\Lst;
use WPML\FP\Obj;
use function WPML\FP\partial;
use function WPML\FP\pipe;

class CheckTags {

	public static function run( $observer ) {
		RestUtils::$timeout = 15;
		RestUtils::$logFailedRequest = true;

		$appendProp = function ( $propName, $fn ) {
			return Fns::converge( Obj::assoc( $propName ), [ $fn, Fns::identity() ] );
		};

		$getSourcePostTitle = $appendProp( 'source_post', pipe( Obj::prop( 'source_id' ), '\get_post', Obj::prop( 'post_title' ) ) );

		$getSiteData   = pipe( Obj::prop( 'site_name' ), SitesConfiguration::getByName() );
		$getTargetPost = $appendProp( 'target_post', Fns::converge( API::getByID(), [ $getSiteData, Obj::prop( 'target_id' ), Obj::prop( 'type' ) ] ) );

		$getSourceTags = $appendProp( 'source_tags', function ( $data ) {
			return wp_get_post_tags( Obj::prop( 'source_id', $data ), [ 'fields' => 'names' ] );
		} );

		$appendData = pipe( $getSourcePostTitle, $getTargetPost, $getSourceTags );

		$displayPost = function ( $data ) use ( $observer ) {
			$observer( sprintf( 'Post %d - %s', Obj::prop( 'source_id', $data ), Obj::prop( 'source_post', $data ) ) );
		};

		$rowset = self::getAllPosts();
		foreach ( $rowset as $data ) {
			$data = $appendData( $data );

			Obj::prop( 'target_post', $data )
			   ->map( function ( $target_post ) use ( $data, $observer, $displayPost ) {
				   $source = Obj::prop( 'source_tags', $data );
				   $target = Obj::prop( 'tags', $target_post );

				   if ( count( $source ) > count( $target ) ) {
					   $displayPost( $data );
					   $observer( sprintf( 'Tags are missing: %s -> %s', implode( ', ', $source ), implode( ', ', $target ) ) );
				   }

				   return $data;
			   } )
			   ->getOrElse( function () use ( $observer, $displayPost, $data ) {
				   $displayPost( $data );
				   $observer( 'Target post could not be found!' );
			   } );

		}
	}

	private static function getAllPosts() {
		global $wpdb;

		$sql = "
			SELECT source_id, target_id, site_name, type 
			FROM {$wpdb->prefix}wp_ps_mapping
			WHERE `type` != 'media' 
		";

		return $wpdb->get_results( $sql );
	}
}