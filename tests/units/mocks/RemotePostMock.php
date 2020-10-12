<?php

namespace PostSynchronization\Mocks;


trait RemotePostMock {


	public function setUpRemotePostMock() {

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'return' => function ( $response ) {
				if ( is_wp_error( $response ) || ! isset( $response['response'] ) || ! is_array( $response['response'] ) ) {
					return '';
				}

				return $response['response']['message'];
			}
		] );

	}

	public function defineAnyRemotePost() {
		\WP_Mock::userFunction( 'wp_remote_post' );
	}


	public function expectRemotePost( string $url, array $headers, array $body, array $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [ $url, [ 'headers' => $headers, 'body' => $body ] ],
			'times'  => 1,
			'return' => function ( $actualUrls, $params ) use ( $url, $headers, $body, $response ) {
				$this->assertEquals( $url, $actualUrls );
				$this->assertEquals( $headers, $params['headers'] );
				$this->assertEquals( $body, $params['body'] );

				return $response;
			},
		] );
	}

	public function expectDeleteRemotePost( string $url, array $headers, array $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [ $url, [ 'headers' => $headers, 'method' => 'DELETE' ] ],
			'times'  => 1,
			'return' => function ( $actualUrls, $params ) use ( $url, $headers, $response ) {
				$this->assertEquals( $url, $actualUrls );
				$this->assertEquals( $headers, $params['headers'] );

				return $response;
			},
		] );
	}


}