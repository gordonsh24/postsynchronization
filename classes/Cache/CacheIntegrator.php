<?php


namespace PostSynchronization\Cache;

class CacheIntegrator {

	const TARGET_URL = 'target-url-for-';

	public static function addHooks() {
		add_action( 'postsync-post-id-mapping-saved', [ self::class, 'clearTargetUrl' ], 10, 1 );
		add_action( 'postsync-post-sites-updated', [ self::class, 'clearTargetUrl' ], 10, 1 );
	}


	public static function targetUrl( int $postId, $fn ) {
		return Cache::get( self::TARGET_URL . $postId, $fn );
	}

	public static function clearTargetUrl( int $postId ) {
		Cache::remove( self::TARGET_URL . $postId );
	}
}