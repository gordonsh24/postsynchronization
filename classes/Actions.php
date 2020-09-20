<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Either;

/**
 * Class Actions
 * @package PostSynchronization
 *
 * @method static callable|Either create( ...$siteData, ...$post ): Curried :: SiteData->\WP_Post->Either
 *
 * @method static callable|Either update( ...$siteData, $targetPostId, ...$post ): Curried :: SiteData->int->\WP_Post->Either
 *
 * @method static callable|Either delete( ...$siteData, $targetPostId, ...$post ): Curried :: SiteData->int->\WP_Post->Either
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

			if ( is_wp_error( $response ) || wp_remote_retrieve_response_message( $response ) !== 'Created' ) {
				return Either::left( $response );
			}

			$response['body'] = json_decode( $response['body'] );
			Mapper::savePostIdsMapping( $post->ID, [ $siteData->name => $response['body']->id ] );

			return Either::right( $response );
		} );

		self::curryN( 'update', 3, function ( SiteData $siteData, int $targetPostId , \WP_Post $post ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts/' . $targetPostId, [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'body'    => Mapper::postData( $post ),
			] );

			return is_wp_error( $response ) ? Either::left( $response ) : Either::right( $response );
		} );

		self::curryN( 'delete', 3, function ( SiteData $siteData, int $targetPostId, \WP_Post $post  ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts/' . $targetPostId, [
				'headers' => [
					'Authorization' => self::buildAuth( $siteData ),
				],
				'method'  => 'DELETE',
			] );

			return is_wp_error( $response ) ? Either::left( $response ) : Either::right( $response );
		} );
	}

}

Actions::init();