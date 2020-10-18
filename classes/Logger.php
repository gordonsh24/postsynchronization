<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Either;

/**
 * Class Logger
 * @package PostSynchronization
 *
 * @method static callable|void logSyncError( ...$post, ...$error ): Curried :: \WP_Post->string->void
 */
class Logger {
	use Curryable;

	public static function init() {
		self::curryN( 'logSyncError', 2, function ( $post, string $error ) {
			update_post_meta( $post->ID, 'post-sync-error', $error );
		} );
	}
}

Logger::init();