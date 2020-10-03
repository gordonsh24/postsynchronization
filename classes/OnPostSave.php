<?php


namespace PostSynchronization;

use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Relation;

class OnPostSave {

	public static function onPostSave() {
		return function ( $postId, \WP_Post $post ) {
			if ( empty( $_POST )  || Lst::includes( $post->post_status, [ 'auto-draft', 'revision' ] ) ) {
				return null;
			}

			\wpml_collect( PostSynchronizationSettings::getSites( $postId ) )
				->map( [ SitesConfiguration::class, 'getByName' ] )
				->filter()
				->map( function ( $siteData ) use ( $post ) {
					$getAction = self::getAction();
					$action    = $getAction( $post, $siteData );

					/** @var \WPML\FP\Either $result */
					$result = $action( $post );

					return $result->getOrElse( Fns::tap( function ( $error ) use ( $post ) {
						update_post_meta( $post->ID, 'post-sync-error', $error );
					} ) );
				} );
		};
	}

	private static function getAction(): callable {
		return function ( \WP_Post $post, $siteData ) {
			$deleteOrUpdate = Logic::cond( [
				[ Fns::always( Relation::equals( 'trash', $post->post_status ) ), Actions::delete( $siteData ) ],
				[ Fns::always( true ), Actions::update( $siteData ) ],
			] );

			return Mapper::getTargetPostId( $post->ID, $siteData->name )
			             ->map( $deleteOrUpdate )
			             ->getOrElse( Fns::always( Actions::create( $siteData ) ) );
		};
	}

}