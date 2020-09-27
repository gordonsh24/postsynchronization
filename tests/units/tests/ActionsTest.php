<?php

namespace PostSynchronization;

use WPML\FP\Fns;

class ActionsTest extends \WP_Mock\Tools\TestCase {
	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}


	/**
	 * @test
	 */
	public function it_creates_post() {
		$post     = $this->createPost();
		$siteData = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );

		$targetPost = (object) [
			'id'    => 123,
			'title' => $post->post_title,
		];

		$response = [
			'response' => [ 'message' => 'Created', 'code' => 201 ],
			'body'     => json_encode( $targetPost ),
		];

		$this->mockCreateRequest( $siteData, $post, $response );

		\WP_Mock::userFunction( 'get_option', [
			'args'   => [ Mapper::POST_IDS_MAP, [] ],
			'return' => [],
		] );

		\WP_Mock::userFunction( 'update_option', [
			'times' => 1,
			'args'  => [ Mapper::POST_IDS_MAP, [ $post->ID => [ $siteData->name => 123 ] ] ],
		] );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => 'Created',
		] );

		$result = Actions::create( $siteData, $post );

		$this->assertTrue( Fns::isRight( $result ) );
		$this->assertEquals( $targetPost, $result->get() );
	}

	/**
	 * @test
	 */
	public function post_creation_fails() {
		$post     = $this->createPost();
		$siteData = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );
		$response = [
			'response' => [ 'message' => "You're not allowed", 'code' => 400 ],
		];

		$this->mockCreateRequest( $siteData, $post, $response );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => "You're not allowed",
		] );

		$result = Actions::create( $siteData, $post );

		$this->assertTrue( Fns::isLeft( $result ) );
		$this->assertEquals( $response, $result->orElse( Fns::identity() )->get() );
	}

	/**
	 * @test
	 */
	public function it_updates_post() {
		$post         = $this->createPost();
		$siteData     = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );
		$targetPostId = 123;
		$response     = [ 'response' => [ 'message' => 'OK', 'code' => 200 ] ];

		$this->mockUpdateRequest( $siteData, $post, $targetPostId, $response );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => "OK",
		] );

		$result = Actions::update( $siteData, $targetPostId, $post );

		$this->assertTrue( Fns::isRight( $result ) );
		$this->assertEquals( $response, $result->get() );
	}

	/**
	 * @test
	 */
	public function post_update_fails() {
		$post         = $this->createPost();
		$siteData     = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );
		$targetPostId = 123;
		$response     = [ 'response' => [ 'message' => 'Err', 'code' => 400 ] ];

		$this->mockUpdateRequest( $siteData, $post, $targetPostId, $response );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => "Err",
		] );

		$result = Actions::update( $siteData, $targetPostId, $post );

		$this->assertTrue( Fns::isLeft( $result ) );
		$this->assertEquals( $response, $result->orElse( Fns::identity() )->get() );
	}

	/**
	 * @test
	 */
	public function it_deletes_post() {
		$post         = $this->createPost();
		$siteData     = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );
		$targetPostId = 123;
		$response     = [ 'response' => [ 'message' => 'OK', 'code' => 200 ] ];

		$this->mockDeleteRequest( $siteData, $targetPostId, $response );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => "OK",
		] );

		$result = Actions::delete( $siteData, $targetPostId, $post );

		$this->assertTrue( Fns::isRight( $result ) );
		$this->assertEquals( $response, $result->get() );
	}

	/**
	 * @test
	 */
	public function post_delete_fails() {
		$post         = $this->createPost();
		$siteData     = SiteData::create( [ 'name' => 'my site', 'url' => 'http://develop.test', 'user' => 'admin', 'password' => 'password' ] );
		$targetPostId = 123;
		$response     = [ 'response' => [ 'message' => 'Err', 'code' => 400 ] ];

		$this->mockDeleteRequest( $siteData, $targetPostId, $response );

		\WP_Mock::userFunction( 'wp_remote_retrieve_response_message', [
			'args'   => [ $response ],
			'return' => "Err",
		] );

		$result = Actions::delete( $siteData, $targetPostId, $post );

		$this->assertTrue( Fns::isLeft( $result ) );
		$this->assertEquals( $response, $result->orElse( Fns::identity() )->get() );
	}

	private function mockCreateRequest( SiteData $siteData, $post, $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [
				'http://develop.test/wp-json/wp/v2/posts',
				[
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password )
					],
					'body'    => [
						'title'      => $post->post_title,
						'status'     => $post->post_status,
						'content'    => $post->post_content,
						'categories' => 1,
						'excerpt'    => $post->post_excerpt,
					],
				]
			],
			'return' => $response
		] );
	}

	private function mockUpdateRequest( SiteData $siteData, $post, $targetPostId, $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [
				'http://develop.test/wp-json/wp/v2/posts/' . $targetPostId,
				[
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password )
					],
					'body'    => [
						'title'      => $post->post_title,
						'status'     => $post->post_status,
						'content'    => $post->post_content,
						'categories' => 1,
						'excerpt'    => $post->post_excerpt,
					],
				]
			],
			'return' => $response
		] );
	}

	private function mockDeleteRequest( SiteData $siteData, $targetPostId, $response ) {
		\WP_Mock::userFunction( 'wp_remote_post', [
			'args'   => [
				'http://develop.test/wp-json/wp/v2/posts/' . $targetPostId,
				[
					'headers' => [
						'Authorization' => 'Basic ' . base64_encode( $siteData->user . ':' . $siteData->password )
					],
					'method'  => 'DELETE',
				]
			],
			'return' => $response
		] );
	}

	private function createPost() {
		$post = $this->getMockBuilder( '\WP_Post' )->getMock();

		$post->ID           = 12;
		$post->post_title   = 'Post 1';
		$post->post_status  = 'published';
		$post->post_content = 'Some content';
		$post->post_excerpt = 'Some excerpt';
		$post->post_type    = 'post';

		return $post;
	}


}