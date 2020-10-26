<?php


namespace PostSynchronization;


use WPML\FP\Curryable;

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
			self::log( sprintf( 'Sync error for post %d -> %s', $post->ID, $error ) );
		} );
	}

	public static function log( $msg ) {
		file_put_contents( self::getFile(), $msg . PHP_EOL, FILE_APPEND );
	}

	private static function getFile(): string {
		return WP_CONTENT_DIR . '/ps-log.txt';
	}
}

Logger::init();