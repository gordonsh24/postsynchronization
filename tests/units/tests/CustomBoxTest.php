<?php


namespace PostSynchronization;


use WPML\LIB\WP\PostMock;

class CustomBoxTest extends \WP_Mock\Tools\TestCase {
	use PostMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpPostMock();
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();

		$_POST = [];
	}

	/**
	 * @test
	 */
	public function it_save_post_sync_config() {
		$postId = 12;
		$sites  = [ 'site_1', 'site_2' ];
		$_POST  = [ 'postsynchronization_site_name' => $sites ];

		$subject = new CustomBox();
		$subject->save( $postId );

		$this->assertEquals( $sites, PostSynchronizationSettings::getSites( $postId ) );
	}
}