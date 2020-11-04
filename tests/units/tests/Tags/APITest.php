<?php

namespace PostSynchronization\Tags;


use PostSynchronization\Mocks\RemotePostMock;
use PostSynchronization\SitesConfiguration;
use WPML\FP\Either;

class APITest extends \WP_Mock\Tools\TestCase {
	use RemotePostMock;

	public function setUp(): void {
		parent::setUp();
		\WP_Mock::setUp();

		$this->setUpRemotePostMock();

		\WP_Mock::userFunction( 'is_wp_error', [
			'return' => function ( $param ) {
				return $param instanceof \WP_Error;
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
	public function find_response_is_invalid() {
		$tagName = 'tag1';

		$this->expectRemoteGet(
			'http://gdzienazabieg.test//wp-json/wp/v2/tags',
			[
				'search' => $tagName
			],
			[
				'response' => [
					'status'  => '400',
					'message' => 'error',
				],
				'body'     => json_encode( 'Some error' ),
			]
		);

		$expected = Either::left( 'Tag not found' );

		$this->assertEquals( $expected, API::find( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	 * @test
	 */
	public function find_returns_empty_result() {
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

		$expected = Either::left( 'Tag not found' );

		$this->assertEquals( $expected, API::find( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	 * @test
	 */
	public function find_gets_tag() {
		$tagName = 'Some text';
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
				'body'     => json_encode( [
					[
						'id'   => 11,
						'name' => 'Some text additional'
					],
					$tag
				] ),
			]
		);

		$expected = Either::of( $tag );

		$this->assertEquals( $expected, API::find( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	 * @test
	 */
	public function create_returns_error() {
		$tagName = 'tag1';

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
					'message' => 'Err',
				],
				'body'     => json_encode( 'Some error' ),
			]
		);

		$expected = Either::left( 'Some error' );

		$this->assertEquals( $expected, API::create( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}

	/**
	 * @test
	 */
	public function create_returns_correct_response() {
		$tagName = 'tag1';
		$tag     = [
			'id'   => 12,
			'name' => $tagName,
		];

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

		$expected = Either::right( $tag );

		$this->assertEquals( $expected, API::create( SitesConfiguration::getByName( 'gdzienazabieg' ), $tagName ) );
	}
}