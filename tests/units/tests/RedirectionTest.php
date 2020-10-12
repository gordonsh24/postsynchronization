<?php

namespace PostSynchronization;

use PostSynchronization\Mocks\MapperMock;
use WPML\LIB\WP\OptionMock;
use WPML\LIB\WP\PostMock;
use WPML\LIB\WP\TransientMock;

class RedirectionTest extends \WP_Mock\Tools\TestCase {
	use TransientMock;
	use MapperMock;
	use PostMock;
	use OptionMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpTransientMock();
		$this->setUpMapperMock();
		$this->setUpPostMock();
		$this->setUpOptionMock();

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
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @test
	 */
	public function it_redirects_to_target_post() {
		$post         = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID     = 12;
		$externalPost = 'http://gdzienazabieg.test/external-post';

		update_post_meta( $post->ID, PostSynchronizationSettings::OPTION_NAME, [ 'gdzienazabieg' ] );
		$this->addMapping( $post->ID, 'post', 'gdzienazabieg', 1012, $externalPost );
		$this->mockPostType($post->ID, 'post');

		$query        = $this->getMockBuilder( '\WP_Query' )->getMock();
		$query->query = [ 'name' => 'my-post' ];

		\WP_Mock::userFunction( 'wp_redirect', [
			'times' => 1,
			'args'  => [ $externalPost, 301 ],
		] );

		Redirections::redirectPost( [ $post ], $query );
	}
}