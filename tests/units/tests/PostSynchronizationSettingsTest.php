<?php

namespace PostSynchronization;

use WPML\LIB\WP\PostMock;

class PostSynchronizationSettingsTest extends \WP_Mock\Tools\TestCase {
	use PostMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpPostMock();
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @test
	 */
	public function test_post_settings() {
		$postId = 12;
		PostSynchronizationSettings::saveSites( $postId, [ 'site_1', 'site_2' ] );

		$this->assertEquals( [ 'site_1', 'site_2' ], PostSynchronizationSettings::getSites( $postId ) );
		$this->assertTrue( PostSynchronizationSettings::shouldSynchronize( $postId, 'site_2' ) );
		$this->assertTrue( PostSynchronizationSettings::hasAnyActiveSynchronization( $postId ) );

		PostSynchronizationSettings::saveSites( $postId, [] );
		$this->assertEquals( [], PostSynchronizationSettings::getSites( $postId ) );
		$this->assertFalse( PostSynchronizationSettings::shouldSynchronize( $postId, 'site_2' ) );
		$this->assertFalse( PostSynchronizationSettings::hasAnyActiveSynchronization( $postId ) );
	}
}