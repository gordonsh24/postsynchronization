<?php


namespace PostSynchronization\Tags;


use PostSynchronization\RestUtils;
use PostSynchronization\SiteData;
use WPML\FP\Curryable;
use WPML\FP\Either;
use WPML\FP\Fns;
use WPML\FP\Logic;
use WPML\FP\Lst;
use WPML\FP\Obj;
use WPML\FP\Relation;
use function WPML\FP\pipe;

/**
 * Class API
 * @package PostSynchronization\Tags
 *
 * @method static callable|Either find(...$siteData, ...$name): Curried :: \SiteData -> string -> Either
 *
 * @method static callable|Either create(...$siteData, ...$name): Curried :: \SiteData -> string -> Either
 *
 *
 */
class API {
	use Curryable;

	public static function init() {

		self::curryN( 'find', 2, function ( SiteData $siteData, string $name ) {
			$response = RestUtils::request( self::createUrl( $siteData ), [
				'method' => 'GET',
				'body'   => [ 'search' => $name ],
			] );

			$findExact = Lst::find( Relation::propEq( 'name', $name ) ) ;

			return Either::of( $response )
			             ->filter( RestUtils::checkResponseMsg( 'OK' ) )
			             ->map( RestUtils::getBody() )
			             ->filter( pipe( Logic::isEmpty(), Logic::not() ) )
			             ->bimap( Fns::always( 'Tag not found' ), $findExact );
		} );

		self::curryN( 'create', 2, function ( SiteData $siteData, string $name ) {
			$response = RestUtils::request( self::createUrl( $siteData ), [
				'headers' => [
					'Authorization' => RestUtils::buildAuth( $siteData ),
				],
				'body'    => [ 'name' => $name ],
			] );

			return Either::of( $response )
			             ->filter( RestUtils::checkResponseMsg( 'Created' ) )
			             ->bimap( RestUtils::getBody(), RestUtils::getBody() );
		} );
	}


	private static function createUrl( SiteData $siteData ): string {
		return sprintf( '%s/wp-json/wp/v2/tags', $siteData->url );
	}
}

API::init();