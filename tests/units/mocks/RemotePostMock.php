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

	public function defineAnyRemotePost(  ) {
		\WP_Mock::userFunction( 'wp_remote_post' );
	}
	

	public function expectRemotePost( string $url, array $headers, array $body, array $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [ $url, [ 'headers' => $headers, 'body' => $body ] ],
			'times'  => 1,
			'return' => function ( $actualUrls, $params ) use ( $url, $headers, $body, $response ) {
				$this->assertEquals( $url, $actualUrls );
				$this->assertEquals( $params['headers'], $headers );
				$this->assertEquals( $params['body'], $body );

				return $response;
			},
		] );
	}


}