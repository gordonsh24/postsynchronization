<?php


namespace PostSynchronization\Mocks;


trait MediaMock {

	private $postFeatureImages = [];

	public function setUpMediaMock() {
		\WP_Mock::userFunction( 'get_post_thumbnail_id', [
			'return' => function ( $post ) {
				return array_key_exists( $post->ID, $this->postFeatureImages ) ? $this->postFeatureImages[ $post->ID ] : false;
			}
		] );
	}

	public function setPostFeatureImage( int $postId, int $mediaId ) {
		$this->postFeatureImages[ $postId ] = $mediaId;
	}
}