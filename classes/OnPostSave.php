<?php


namespace PostSynchronization;

use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Relation;

class OnPostSave {

	public static function onPostSave( callable $createAction, callable $updateAction, callable $deleteAction, callable $getSiteConfiguration, callable $getTargetPostId ) {
		return function ( $postId, \WP_Post $post ) use ( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId ) {
			if ( Lst::includes( $post->post_status, [ 'auto-draft', 'revision' ] ) ) {
				return null;
			}

			$getAction = self::getAction( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId );
			$action    = $getAction( $post );

			/** @var \WPML\FP\Either $result */
			$result = $action( $post );

			return $result->getOrElse( Fns::tap( function ( $error ) {
				error_log( $error );
			} ) );
		};
	}

	private static function getAction( callable $createAction, callable $updateAction, callable $deleteAction, callable $getSiteConfiguration, callable $getTargetPostId ): callable {
		return function ( \WP_Post $post ) use ( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId ) {
			$siteData = current( $getSiteConfiguration() );

			$deleteOrUpdate = Logic::cond( [
				[ Fns::always( Relation::equals( 'trash', $post->post_status ) ), $deleteAction( $siteData ) ],
				[ Fns::always( true ), $updateAction( $siteData ) ],
			] );

			return $getTargetPostId( $post->ID, $siteData->name )->map( $deleteOrUpdate )->getOrElse( Fns::always( $createAction( $siteData ) ) );
		};
	}

}