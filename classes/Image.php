<?php

namespace PostSynchronization;


class Image {

	public static function send( SiteData $siteData, $imageId ) {
		$path     = get_attached_file( $imageId );
		$file     = file_get_contents( $path );
		$filename = basename( $path );
		$filetype = mime_content_type( $path );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, self::createMedia( $siteData ) );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 30 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false );

		curl_setopt( $ch, CURLOPT_HTTPHEADER, [
			'Authorization: ' . self::buildAuth( $siteData ),
			"cache-control: no-cache",
			"content-disposition: form-data; filename='$filename'",
			"content-type: $filetype",
		] );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $file );
		curl_setopt( $ch, CURLOPT_VERBOSE, 1 );

		$response = curl_exec( $ch );

		$response = json_decode( $response );

		return $response;
	}

	private static function createMedia( SiteData $siteData ) {
		return sprintf( '%s/wp-json/wp/v2/media', $siteData->url );
	}

	private static function buildAuth( SiteData $siteData ): string {
		return 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password );
	}
}