<?php

namespace PostSynchronization;

use PostSynchronization\Mocks\TaxonomiesMock;
use PostSynchronization\Mocks\MediaMock;
use PostSynchronization\Mocks\RemotePostMock;
use WPML\LIB\WP\OptionMock;
use WPML\LIB\WP\PostMock;

class OnPostSaveTest extends \WP_Mock\Tools\TestCase {
	use PostMock;
	use OptionMock;
	use RemotePostMock;
	use MediaMock;
	use TaxonomiesMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpPostMock();
		$this->setUpOptionMock();
		$this->setUpRemotePostMock();
		$this->setUpMediaMock();
		$this->setUpCategories();

		! defined( 'POST_SYNC_SITES' ) && define( 'POST_SYNC_SITES', [
			[
				'name'          => 'gdzienazabieg',
				'url'           => 'http://gdzienazabieg.test/',
				'user'          => 'admin',
				'password'      => 'password',
				'categoriesMap' => [
					2 => 4,
					3 => 2,
				],
				'tagsMap'       => [
					6  => 26,
					8  => 28,
					11 => 31,
				],
				'authorsMap'    => [
					1 => 11,
					2 => 12,
				],
			],
			[
				'name'          => 'develop',
				'url'           => 'http://develop.test/',
				'user'          => 'admin',
				'password'      => 'password',
				'categoriesMap' => [],
			]
		] );

		\WP_Mock::userFunction( 'is_wp_error', [
			'return' => function ( $param ) {
				return $param instanceof \WP_Error;
			}
		] );
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();

		$_POST = [];
	}

	/**
	 * @test
	 */
	public function it_creates_target_post() {
		$post = $this->createSamplePost();
		$this->setPostCategories( $post->ID, [ 5, 2, 3 ] );
		$this->setPostTags( $post->ID, [ 2, 8, 11 ] );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->expectRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/posts',
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'title'          => $post->post_title,
				'status'         => $post->post_status,
				'content'        => $post->post_content,
				'categories'     => '1,4,2',
				'tags'           => '28,31',
				'author'         => 12,
				'excerpt'        => $post->post_excerpt,
				'featured_media' => 0,
			],
			[
				'response' => [
					'status'  => 'OK',
					'message' => 'Created',
				],
				'body'     => json_encode( [
					'id' => $post->ID + 100,
				] ),
			]
		);

		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );

		$expectedMap = [ $post->ID => [ 'gdzienazabieg' => $post->ID + 100 ] ];
		$this->assertEquals( $expectedMap, get_option( Mapper::POST_IDS_MAP ) );
	}

	private function createSamplePost() {
		$post               = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID           = 10;
		$post->post_author  = 2;
		$post->post_title   = 'My post';
		$post->post_status  = 'publish';
		$post->post_type    = 'post';
		$post->post_content = 'some content';
		$post->post_excerpt = 'some excerpt';

		return $post;
	}

}