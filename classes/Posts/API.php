<?php

namespace PostSynchronization\Posts;

use PostSynchronization\RestUtils;
use PostSynchronization\SiteData;
use WPML\FP\Curryable;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Maybe;
use function WPML\FP\pipe;

/**
 * Class API
 * @package PostSynchronization\Posts
 *
 * @method static callable|Maybe getByID( ...$siteData, ...$id, ...$type ): Curried :: \SiteData->id->string->Maybe
 */
class API {
	use Curryable;

	public static function init() {
		self::curryN( 'getByID', 3, function ( SiteData $site, $id, $type ) {
			$url = sprintf( '%s/wp-json/wp/v2/%ss', $site->url, $type );

			$response = RestUtils::request( $url, [
				'method' => 'GET',
				'headers' => [
					'Authorization' => RestUtils::buildAuth( $site ),
				],
				'body'   => [ 'include' => [ $id ], 'per_page' => 1 ],
			] );

			return Maybe::of( $response )
			            ->filter( RestUtils::checkResponseMsg( 'OK' ) )
			            ->map( RestUtils::getBody() )
			            ->filter( pipe( Logic::isEmpty(), Logic::not() ) )
			            ->map( Lst::nth( 0 ) );

		} );
	}

}

API::init();