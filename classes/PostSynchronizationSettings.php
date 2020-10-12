<?php


namespace PostSynchronization;


use WPML\FP\Logic;
use WPML\FP\Lst;

class PostSynchronizationSettings {

	const OPTION_NAME = 'postsynchronization_post_sites';

	public static function getSites( $postId ) {
		return get_post_meta( $postId, self::OPTION_NAME, true ) ?: [];
	}

	public static function saveSites( $postId, $sites ) {
		update_post_meta( $postId, self::OPTION_NAME, $sites );

		do_action( 'postsync-post-sites-updated', $postId, $sites );
	}

	public static function shouldSynchronize( $postId, $siteName ) {
		$sites = self::getSites( $postId );

		return Lst::includes( $siteName, $sites );
	}

	public static function hasAnyActiveSynchronization( int $postId ): bool {
		return ! Logic::isEmpty( self::getSites( $postId ) );
	}
}