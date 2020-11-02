<?php


namespace PostSynchronization\Tags;


use PostSynchronization\Mocks\RemotePostMock;
use PostSynchronization\SitesConfiguration;
use WPML\FP\Either;

class SyncTest extends \WP_Mock\Tools\TestCase {
	use RemotePostMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpRemotePostMock();
	}

	public function tearDown(): void {
		parent::tearDown();
		\WP_Mock::tearDown();
	}

	/**
	 * @test
	 */
	public function it_returns_existing_tag() {
		$tagName = 'tag1';
		$tag     = [
			'id'   => 12,
			'name' => $tagName,
		];

		$this->expectRemoteGet(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'search' => $tagName
			],
			[
				'response' => [
					'status'  => '200',
					'message' => 'OK',
				],
				'body'     => json_encode( [ $tag ] ),
			]
		);

		$expected = Either::of( $tag );

		$this->assertEquals( $expected, Sync::createIfNotExist( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	 * @test
	 */
	public function it_creates_new_tag_if_cant_find_existings() {
		$tagName = 'tag1';
		$tag     = [
			'id'   => 12,
			'name' => $tagName,
		];

		$this->expectRemoteGet(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'search' => $tagName
			],
			[
				'response' => [
					'status'  => '200',
					'message' => 'OK',
				],
				'body'     => json_encode( [] ),
			]
		);

		$this->expectRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'name' => $tagName,
			],
			[
				'response' => [
					'status'  => '201',
					'message' => 'Created',
				],
				'body'     => json_encode( $tag ),
			]
		);

		$expected = Either::of( $tag );

		$this->assertEquals( $expected, Sync::createIfNotExist( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	* @test
	*/
	public function it_returns_error_when_tag_neither_can_be_found_or_created() {
		$tagName = 'tag1';

		$this->expectRemoteGet(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'search' => $tagName
			],
			[
				'response' => [
					'status'  => '200',
					'message' => 'OK',
				],
				'body'     => json_encode( [] ),
			]
		);

		$this->expectRemotePost(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'Authorization' => 'Basic ' . base64_encode( 'admin:password' )
			],
			[
				'name' => $tagName,
			],
			[
				'response' => [
					'status'  => '400',
					'message' => 'Error',
				],
				'body'     => json_encode( 'Some error' ),
			]
		);

		$expected = Either::left( 'Some error' );

		$this->assertEquals( $expected, Sync::createIfNotExist( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}
}