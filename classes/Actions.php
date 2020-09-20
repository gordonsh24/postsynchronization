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
 * @method static callable|Either update( ...$siteData, ...$post ): Curried :: SiteData->\WP_Post->Either
 */
class Actions {
	use Curryable;

	public static function init() {

		self::curryN( 'create', 2, function ( SiteData $siteData, \WP_Post $post ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts', [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password )
				],
				'body'    => [
					'title'      => $post->post_title,
					'status'     => $post->post_status,
					'content'    => $post->post_content,
					'categories' => 3,
					'excerpt'    => $post->post_excerpt,
				]
			] );

			return is_wp_error( $response ) ? Either::left( $response ) : Either::right( $response );
		} );

		self::curryN( 'update', 2, function ( SiteData $siteData, \WP_Post $post ) {
			$response = wp_remote_post( $siteData->url . '/wp-json/wp/v2/posts/' . $post->ID, [
				'headers' => [
					'Authorization' => 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password )
				],
				'body'    => [
					'title'      => $post->post_title,
					'status'     => $post->post_status,
					'content'    => $post->post_content,
					'categories' => 3,
					'excerpt'    => $post->post_excerpt,
				]
			] );

			return is_wp_error( $response ) ? Either::left( $response ) : Either::right( $response );
		} );
	}

}

Actions::init();