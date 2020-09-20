<?php


namespace PostSynchronization;


class Initializer {

	public static function addHooks() {
		add_action( 'save_post_post', [ self::class, 'onPostSave' ], 10, 2 );
	}

	public static function onPostSave( $postId, \WP_Post $post ) {
		if ( $post->post_status === 'auto-draft' ) {
			return;
		}

		$action = self::getAction( $post );

		/** @var \WPML\FP\Either $result */
		$result = $action( $post );

		if ( \WPML\FP\Fns::isLeft( $result ) ) {
			error_log( $result->orElse( \WPML\FP\Fns::identity() )->get() );
		}
	}

	private static function getAction( \WP_Post $post ): callable {
		$siteData = current( self::getSitesConfiguration() );

		$targetId = Mapper::getTargetPostId( $post->ID, $siteData->name );
		if ( \WPML\FP\Fns::isJust( $targetId ) ) {
			if ( $post->post_status === 'trash' ) {
				$action = function () use ( $siteData, $targetId ) {
					return Actions::delete( $siteData, $targetId->get() );
				};
			} else {
				$action = Actions::update( $siteData, $targetId->get() );
			}
		} else {
			$action = Actions::create( $siteData );
		}

		return $action;
	}

	/**
	 * @return SiteData[]
	 */
	private static function getSitesConfiguration(): array {
		$siteData           = new SiteData();
		$siteData->name     = 'gdzienazabieg';
		$siteData->url      = 'http://gdzienazabieg.test/';
		$siteData->user     = 'admin';
		$siteData->password = 'password';

		return [
			$siteData
		];
	}
}