<?php


namespace PostSynchronization\Mocks;


trait TaxonomiesMock {

	protected $categories = [];
	protected $tags = [];

	public function setUpCategories() {

		\WP_Mock::userFunction( 'wp_get_post_categories', [
			'return' => function ( $postId ) {
				return $this->categories[ $postId ] ?? [];
			}
		] );

		\WP_Mock::userFunction( 'wp_get_post_tags', [
			'return' => function ( $postId ) {
				return $this->tags[ $postId ] ?? [];
			}
		] );

	}

	public function setPostCategories( $postId, $categoryIds ) {
		$this->categories[ $postId ] = $categoryIds;
	}

	public function setPostTags( $postId, $tagIds ) {
		$this->tags[ $postId ] = $tagIds;
	}

}