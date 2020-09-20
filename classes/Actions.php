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
 * @method static callable|Either update( ...$siteData, $targetPostId, ...$post ): Curried :: SiteData->int->\WP_Post->Either
 *
 * @method static callable|Either delete( ...$siteData, $targetPostId ): Curried :: SiteData->int->Either
 */
class Actions {
	use Curryable;

	private static function buildAuth( SiteData $siteData ): string {
		return 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password );
	}

	public static function init() {

		self::curryN( 'create', 2, function ( SiteData $siteData, \WP_Post $post ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts', [
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
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts/' . $targetPostId, [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'body'    => Mapper::postData( $post ),
			] );

			return wp_remote_retrieve_response_message( $response ) !== 'OK' ? Either::left( $response ) : Either::right( $response );
		} );

		self::curryN( 'delete', 2, function ( SiteData $siteData, int $targetPostId ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts/' . $targetPostId, [
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