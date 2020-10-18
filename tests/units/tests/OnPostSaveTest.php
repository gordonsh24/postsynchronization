<?php

namespace PostSynchronization;

use PostSynchronization\Mocks\MapperMock;
use PostSynchronization\Mocks\TaxonomiesMock;
use PostSynchronization\Mocks\MediaMock;
use PostSynchronization\Mocks\RemotePostMock;
use WPML\LIB\WP\OptionMock;
use WPML\LIB\WP\PostMock;
use tad\FunctionMocker\FunctionMocker;

class OnPostSaveTest extends \WP_Mock\Tools\TestCase {
	use PostMock;
	use OptionMock;
	use RemotePostMock;
	use MediaMock;
	use TaxonomiesMock;
	use MapperMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpPostMock();
		$this->setUpOptionMock();
		$this->setUpRemotePostMock();
		$this->setUpMediaMock();
		$this->setUpCategories();
		$this->setUpMapperMock();

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
		$this->mockPostType( $post->ID, 'post' );
		$this->setPostTags( $post->ID, [ 2, 8, 11 ] );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->mockPostSyncRequest( $post, 0, '1,4,2', '28,31' );

		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );

		$expectedMap = [
			[
				'id'         => 1,
				'source_id'  => $post->ID,
				'type'       => 'post',
				'site_name'  => 'gdzienazabieg',
				'target_id'  => $post->ID + 100,
				'target_url' => 'http://gdzieniazabieg.test/external_post',
			]
		];
		$this->assertEquals( $expectedMap, $this->getAllMapping() );
	}

	/**
	 * @test
	 */
	public function it_sets_feature_image_which_has_already_been_synced() {
		$post    = $this->createSamplePost();
		$mediaId = 1066;
		$this->mockPostType( $post->ID, 'post' );
		$this->setPostFeatureImage( $post->ID, $mediaId );
		$this->addMapping( $mediaId, 'media', 'gdzienazabieg', 2066, '' );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->mockPostSyncRequest( $post, 2066 );


		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );
	}

	/**
	 * @test
	 */
	public function it_sync_a_new_image_and_assign_it_to_post() {
		$post    = $this->createSamplePost();
		$mediaId = 1066;
		$this->mockPostType( $post->ID, 'post' );
		$this->setPostFeatureImage( $post->ID, $mediaId );
		FunctionMocker::replace( Image::class . '::send', [ 'id' => 2066 ] );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->mockPostSyncRequest( $post, 2066 );

		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );

		$this->assertEquals( 2066, $this->getMapping( $mediaId, 'media', 'gdzienazabieg' )['target_id'] );
	}

	/**
	* @test
	*/
	public function it_updates_post() {
		$post = $this->createSamplePost();
		$this->setPostCategories( $post->ID, [ 5, 2, 3 ] );
		$this->mockPostType( $post->ID, 'post' );
		$this->addMapping( $post->ID, 'post', 'gdzienazabieg', $post->ID + 100, 'http://gdzieniazabieg.test/external_post' );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->expectRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/posts/' . ( $post->ID + 100 ),
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'title'          => $post->post_title,
				'status'         => $post->post_status,
				'content'        => $post->post_content,
				'categories'     => '1,4,2',
				'tags'           => '',
				'author'         => 12,
				'excerpt'        => $post->post_excerpt,
				'featured_media' => 0,
			],
			[
				'response' => [
					'status'  => 'OK',
					'message' => 'OK',
				],
				'body'     => json_encode( [
					'id'   => $post->ID + 100,
					'link' => 'http://gdzieniazabieg.test/external_post',
				] ),
			]
		);

		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );
	}

	/**
	* @test
	*/
	public function it_deletes_post() {
		$post = $this->createSamplePost();
		$post->post_status = 'trash';
		$this->mockPostType( $post->ID, 'post' );
		$this->addMapping( $post->ID, 'post', 'gdzienazabieg', $post->ID + 100, 'http://gdzieniazabieg.test/external_post' );

		$_POST = [ 'some' => 'data' ];

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );

		$this->expectDeleteRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/posts/' . ( $post->ID + 100 ),
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'response' => [
					'status'  => 'OK',
					'message' => 'OK',
				],
			]
		);

		$handler = OnPostSave::onPostSave();
		$handler( $post->ID, $post );
	}

	private function mockPostSyncRequest( $post, $featuredImage = 0, $categories = '1', $tags = '' ) {
		$this->expectRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/posts',
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'title'          => $post->post_title,
				'status'         => $post->post_status,
				'content'        => $post->post_content,
				'categories'     => $categories,
				'tags'           => $tags,
				'author'         => 12,
				'excerpt'        => $post->post_excerpt,
				'featured_media' => $featuredImage,
			],
			[
				'response' => [
					'status'  => 'OK',
					'message' => 'Created',
				],
				'body'     => json_encode( [
					'id'   => $post->ID + 100,
					'link' => 'http://gdzieniazabieg.test/external_post',
				] ),
			]
		);
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