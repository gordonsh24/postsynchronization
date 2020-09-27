<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Either;
use WPML\FP\Obj;
use WPML\FP\Relation;
use function WPML\FP\pipe;

/**
 * Class Actions
 * @package PostSynchronization
 *
 * @method static callable|Either create( ...$siteData, ...$post ): Curried :: SiteData->\WP_Post->Either
 *
 * @method static callable|Either update( ...$siteData, ...$targetPostId, ...$post ): Curried :: SiteData->int->\WP_Post->Either
 *
 * @method static callable|Either delete( ...$siteData, ...$targetPostId ): Curried :: SiteData->int->Either
 */
class Actions {
	use Curryable;

	private static function buildAuth( SiteData $siteData ): string {
		return 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password );
	}

	private static function createUrl( SiteData $siteData, \WP_Post $post ) {
		return sprintf( '%s/wp-json/wp/v2/%ss', $siteData->url, $post->post_type );
	}

	private static function updateUrl( SiteData $siteData, int $targetPostId, \WP_Post $post ) {
		return sprintf( '%s/wp-json/wp/v2/%ss/%d', $siteData->url, $post->post_type, $targetPostId );
	}

	public static function init() {

		self::curryN( 'create', 2, function ( SiteData $siteData, \WP_Post $post ) {
			$response = wp_remote_post( self::createUrl( $siteData, $post ), [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'body'    => Mapper::postData( $post ),
			] );

			$saveInMap = function ( $body ) use ( $post, $siteData ) {
				Mapper::savePostIdsMapping( $post->ID, [ $siteData->name => $body->id ] );

				return $body;
			};

			return Either::of( $response )
			             ->filter( pipe( 'wp_remote_retrieve_response_message', Relation::equals( 'Created' ) ) )
			             ->map( Obj::prop( 'body' ) )
			             ->map( 'json_decode' )
			             ->map( $saveInMap );
		} );

		self::curryN( 'update', 3, function ( SiteData $siteData, int $targetPostId, \WP_Post $post ) {
			$response = wp_remote_post( self::updateUrl( $siteData, $targetPostId, $post ), [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'body'    => Mapper::postData( $post ),
			] );

			return wp_remote_retrieve_response_message( $response ) !== 'OK' ? Either::left( $response ) : Either::right( $response );
		} );

		self::curryN( 'delete', 3, function ( SiteData $siteData, int $targetPostId, \WP_Post $post) {
			$response = wp_remote_post( self::updateUrl( $siteData, $targetPostId, $post ), [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'method'  => 'DELETE',
			] );

			return wp_remote_retrieve_response_message( $response ) !== 'OK' ? Either::left( $response ) : Either::right( $response );
		} );
	}

}

Actions::init();