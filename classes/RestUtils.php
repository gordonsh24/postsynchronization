<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Json;
use WPML\FP\Obj;
use WPML\FP\Relation;
use function WPML\FP\pipe;

/**
 * Class RestUtils
 * @package PostSynchronization
 *
 * @method static callable|bool checkResponseMsg(...$expectedMsg, ...$response): Curried :: string->object|array->bool
 *
 * @method static callable|mixed getBody(...$response): Curried :: object|array->mixed
 *
 * @method static callable|mixed request(...$url, ...$params): Curried :: string->array->mixed
 */
class RestUtils {
	use Curryable;

	public static $timeout = 5;

	public static function buildAuth( SiteData $siteData ): string {
		return 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password );
	}

	public static function init() {

		self::curryN( 'checkResponseMsg', 2, function ( $expectedMsg, $response ) {
			$fn = pipe( 'wp_remote_retrieve_response_message', Relation::equals( $expectedMsg ) );

			return $fn( $response );
		} );

		self::curryN( 'getBody', 1, pipe( Obj::prop( 'body' ), Json::toArray() ) );

		self::curryN( 'request', 2, function ( $url, $params ) {
			$default = [ 'timeout' => self::$timeout ];

			return wp_remote_post( $url, array_merge( $default, $params ) );
		} );

	}
}

RestUtils::init();