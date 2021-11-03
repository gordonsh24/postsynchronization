<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Obj;

/**
 * Class Logger
 * @package PostSynchronization
 *
 * @method static callable|void logSyncError( ...$post, ...$error ): Curried :: \WP_Post->string->void
 */
class Logger {
	use Curryable;

	public static function init() {
		self::curryN( 'logSyncError', 2, function ( $post,  $error ) {
			self::log( sprintf( 'Sync error for post %d -> %s', $post->ID, json_encode( $error ) ) );
		} );
	}

	public static function log( $msg ) {
		file_put_contents( self::getFile(), $msg . PHP_EOL, FILE_APPEND );
	}

	public static function logResponse( $url, $params, $response ) {
		$errMsg = is_wp_error($response) ? 'Timeout' : Obj::prop('body', $response);
		$code = Obj::pathOr(500, ['response', 'code'], $response);

		self::log( sprintf( '%s - [%d] %s %s', date( 'Y-m-d H:i:s' ), $code, $url, $errMsg ) );
		self::log( json_encode( $params ) );
	}

	private static function getFile(): string {
		return WP_CONTENT_DIR . '/ps-log.txt';
	}
}

Logger::init();