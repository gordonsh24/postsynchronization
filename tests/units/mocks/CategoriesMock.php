<?php


namespace PostSynchronization\Mocks;


trait CategoriesMock {

	protected $categories = [];

	public function setUpCategories() {

		\WP_Mock::userFunction( 'wp_get_post_categories', [
			'return' => function ( $postId ) {
				return $this->categories[ $postId ] ?? [];
			}
		] );

	}

	public function setPostCategories( $postId, $categoryIds ) {
		$this->categories[ $postId ] = $categoryIds;
	}

}