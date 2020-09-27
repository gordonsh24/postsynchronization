<?php

namespace PostSynchronization;

class MapperTest extends \WP_Mock\Tools\TestCase {

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		\WP_Mock::userFunction( 'is_wp_error', [
			'return' => function ( $err ) {
				return $err instanceof \WP_Error;
			}
		] );
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @test
	 */
	public function it_maps_to_default_category_when_none_are_fetched() {
		$post = $this->getPost();
		\WP_Mock::userFunction( 'wp_get_post_categories', [
			'args'   => [ 12 ],
			'return' => [],
		] );

		$result = Mapper::postData( $post, $this->getSiteData() );

		$expected = [
			'title'      => 'Post 1',
			'status'     => 'publish',
			'content'    => 'Some content',
			'categories' => '1',
			'excerpt'    => 'Some excerpt',
		];

		$this->assertEquals( $expected, $result );
	}

	/**
	 * @test
	 */
	public function it_maps_to_default_category_when_error_appears() {
		$post = $this->getPost();
		\WP_Mock::userFunction( 'wp_get_post_categories', [
			'args'   => [ 12 ],
			'return' => $this->getMockBuilder( '\WP_Error' )->getMock(),
		] );

		$result = Mapper::postData( $post, $this->getSiteData() );

		$expected = [
			'title'      => 'Post 1',
			'status'     => 'publish',
			'content'    => 'Some content',
			'categories' => '1',
			'excerpt'    => 'Some excerpt',
		];

		$this->assertEquals( $expected, $result );
	}

	/**
	 * @test
	 */
	public function it_maps_categories() {
		$post = $this->getPost();

		$categories = \wpml_collect( [ 6, 2, 3, 8 ] )->map( function ( $term_id ) {
			$term          = $this->getMockBuilder( '\WP_Term' )->getMock();
			$term->term_id = $term_id;

			return $term;
		} )->toArray();

		\WP_Mock::userFunction( 'wp_get_post_categories', [
			'args'   => [ 12 ],
			'return' => $categories,
		] );

		$result = Mapper::postData( $post, $this->getSiteData() );

		$expected = [
			'title'      => 'Post 1',
			'status'     => 'publish',
			'content'    => 'Some content',
			'categories' => '1,12,13',
			'excerpt'    => 'Some excerpt',
		];

		$this->assertEquals( $expected, $result );
	}

	private function getPost() {
		$post = $this->getMockBuilder( '\WP_Post' )->getMock();

		$post->ID           = 12;
		$post->post_title   = 'Post 1';
		$post->post_status  = 'publish';
		$post->post_content = 'Some content';
		$post->post_excerpt = 'Some excerpt';
		$post->post_type    = 'post';

		return $post;
	}

	private function getSiteData() {
		return SiteData::create( [
			'name'          => 'Test site',
			'url'           => 'http://test.com',
			'user'          => 'admin',
			'password'      => 'password',
			'categoriesMap' => [
				2 => 12,
				3 => 13,
			],
		] );
	}
}