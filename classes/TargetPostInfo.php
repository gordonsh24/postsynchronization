<?php

namespace PostSynchronization;


use WPML\FP\Obj;

class TargetPostInfo {

	public static function getTargetUrl( string $siteName, int $targetId ): string {
		$url = SitesConfiguration::getByName( $siteName )->url;
		$url .= '/wp-json/wp/v2/posts?_fields=link&include[]=' . $targetId;

		$response = wp_remote_post( $url, [ 'method' => 'GET' ] );
		if ( wp_remote_retrieve_response_message( $response ) === 'OK' ) {
			$response = json_decode( Obj::prop( 'body', $response ) );

			return Obj::pathOr( '', [ 0, 'link' ], $response );
		}

		return '';
	}

}