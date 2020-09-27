<?php


namespace PostSynchronization;

use WPML\FP\Either;
use WPML\FP\Fns;

class OnPostSave {

	public static function onPostSave( callable $createAction, callable $updateAction, callable $deleteAction, callable $getSiteConfiguration, callable $getTargetPostId ) {
		return function ( $postId, \WP_Post $post ) use ( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId ) {
			if ( $post->post_status === 'auto-draft' ) {
				return Either::of( 'nothing' );
			}

			$getAction = self::getAction( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId );
			$action = $getAction( $post );

			/** @var \WPML\FP\Either $result */
			$result = $action( $post );

			if ( Fns::isLeft( $result ) ) {
				error_log( $result->orElse( Fns::identity() )->get() );
			}

			return $result;
		};
	}

	private static function getAction( callable $createAction, callable $updateAction, callable $deleteAction, callable $getSiteConfiguration, callable $getTargetPostId ): callable {
		return function ( \WP_Post $post ) use ( $createAction, $updateAction, $deleteAction, $getSiteConfiguration, $getTargetPostId ) {
			$siteData = current( $getSiteConfiguration() );

			$targetId = $getTargetPostId( $post->ID, $siteData->name );
			if ( Fns::isJust( $targetId ) ) {
				if ( $post->post_status === 'trash' ) {
					return $deleteAction( $siteData, $targetId->get() );
				} else {
					return $updateAction( $siteData, $targetId->get() );
				}
			} else {
				return $createAction( $siteData );
			}
		};
	}

}