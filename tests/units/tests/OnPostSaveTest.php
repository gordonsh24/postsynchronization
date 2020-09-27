<?php

namespace PostSynchronization;

use WPML\FP\Either;
use WPML\FP\Fns;
use WPML\FP\Math;
use WPML\FP\Maybe;
use function WPML\FP\curryN;
use tad\FunctionMocker\FunctionMocker;

class OnPostSaveTest extends \WP_Mock\Tools\TestCase {
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
	public function it_does_nothing_if_it_is_draft_post() {
		$post              = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID          = 12;
		$post->post_status = 'auto-draft';

		$createAction = curryN( 2, function ( $siteData, $post ) {
			return Either::of( 'created' );
		} );
		$updateAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'updated' );
		} );
		$deleteAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'deleted' );
		} );

		$getTargetPostIdMock = function ( $postId, $siteName ) {
			return Maybe::nothing();
		};

		$errorLog = FunctionMocker::replace( 'error_log' );

		$onPostSave = OnPostSave::onPostSave(
			$createAction,
			$updateAction,
			$deleteAction,
			$this->getSiteConfigMock(),
			$getTargetPostIdMock
		);
		$result     = $onPostSave( $post->ID, $post );

		$this->assertEquals( Either::of( 'nothing' ), $result );
		$errorLog->wasNotCalled();
	}

	/**
	 * @test
	 */
	public function it_performs_create_action() {
		$post              = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID          = 12;
		$post->post_status = 'published';

		$createAction = curryN( 2, function ( $siteData, $post ) {
			return Either::of( 'created' );
		} );
		$updateAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'updated' );
		} );
		$deleteAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'deleted' );
		} );

		$getTargetPostIdMock = function ( $postId, $siteName ) {
			return Maybe::nothing();
		};

		$errorLog = FunctionMocker::replace( 'error_log' );

		$onPostSave = OnPostSave::onPostSave(
			$createAction,
			$updateAction,
			$deleteAction,
			$this->getSiteConfigMock(),
			$getTargetPostIdMock
		);
		$result     = $onPostSave( $post->ID, $post );

		$this->assertEquals( Either::of( 'created' ), $result );
		$errorLog->wasNotCalled();
	}

	/**
	 * @test
	 */
	public function it_performs_update_action() {
		$post              = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID          = 12;
		$post->post_status = 'published';

		$createAction = curryN( 2, function ( $siteData, $post ) {
			return Either::of( 'created' );
		} );
		$updateAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'updated' );
		} );
		$deleteAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'deleted' );
		} );

		$getTargetPostIdMock = function ( $postId, $siteName ) {
			return Maybe::just( $postId + 10 );
		};

		$errorLog = FunctionMocker::replace( 'error_log' );

		$onPostSave = OnPostSave::onPostSave(
			$createAction,
			$updateAction,
			$deleteAction,
			$this->getSiteConfigMock(),
			$getTargetPostIdMock
		);
		$result     = $onPostSave( $post->ID, $post );

		$this->assertEquals( Either::of( 'updated' ), $result );
		$errorLog->wasNotCalled();
	}

	/**
	 * @test
	 */
	public function it_performs_delete_action() {
		$post              = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID          = 12;
		$post->post_status = 'trash';

		$createAction = curryN( 2, function ( $siteData, $post ) {
			return Either::of( 'created' );
		} );
		$updateAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'updated' );
		} );
		$deleteAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'deleted' );
		} );

		$getTargetPostIdMock = function ( $postId, $siteName ) {
			return Maybe::just( $postId + 10 );
		};

		$errorLog = FunctionMocker::replace( 'error_log' );

		$onPostSave = OnPostSave::onPostSave(
			$createAction,
			$updateAction,
			$deleteAction,
			$this->getSiteConfigMock(),
			$getTargetPostIdMock
		);
		$result     = $onPostSave( $post->ID, $post );

		$this->assertEquals( Either::of( 'deleted' ), $result );
		$errorLog->wasNotCalled();
	}

	/**
	 * @test
	 */
	public function it_logs_error() {
		$post              = $this->getMockBuilder( '\WP_Post' )->getMock();
		$post->ID          = 12;
		$post->post_status = 'published';

		$createAction = curryN( 2, function ( $siteData, $post ) {
			return Either::left( 'error' );
		} );
		$updateAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'updated' );
		} );
		$deleteAction = curryN( 3, function ( $siteData, $targetPostId, $post ) {
			return Either::of( 'deleted' );
		} );

		$getTargetPostIdMock = function ( $postId, $siteName ) {
			return Maybe::nothing();
		};

		$errorLog = FunctionMocker::replace( 'error_log' );

		$onPostSave = OnPostSave::onPostSave(
			$createAction,
			$updateAction,
			$deleteAction,
			$this->getSiteConfigMock(),
			$getTargetPostIdMock
		);
		$result     = $onPostSave( $post->ID, $post );

		$this->assertEquals( Either::left( 'error' ), $result );
		$errorLog->wasCalledWithOnce( [ 'error' ] );
	}

	private function getSiteConfigMock() {
		return Fns::always( [
			SiteData::create( [
				'name'     => 'my_site_1',
				'url'      => 'http:/my_site_1.com',
				'user'     => 'admin',
				'password' => '123',
			] )
		] );
	}
}