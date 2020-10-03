<?php


namespace PostSynchronization\Mocks;


trait MediaMock {

	public function setUpMediaMock() {
		\WP_Mock::userFunction( 'get_post_thumbnail_id' );
	}

}