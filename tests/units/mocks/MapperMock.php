<?php


namespace PostSynchronization\Mocks;


use WPML\FP\Lst;
use WPML\FP\Fns;

trait MapperMock {

	private $data = [];

	public function setUpMapperMock() {
		global $wpdb;

		$wpdb = $this->getMockBuilder( '\wpdb' )
		             ->setMethods( [ 'get_row', 'prepare', 'insert', 'get_results' ] )
		             ->getMock();

		$wpdb->prefix = 'wp_';

		$wpdb->method( 'prepare' )->willReturnCallback( function ( ...$args ) {
			return $args;
		} );

		$wpdb->method( 'insert' )->willReturnCallback( function ( $table, $data ) use ( $wpdb ) {
			if ( $table === $wpdb->prefix . 'wp_ps_mapping' ) {
				$data['id']   = count( $this->data ) + 1;
				$this->data[] = $data;
			}
		} );

		$wpdb->method( 'get_row' )->willReturnCallback( function ( $data ) use ( $wpdb ) {
			list( $sql, $postType, $sourceId, $siteName ) = $data;
			if ( $sql === 'SELECT * FROM wp_wp_ps_mapping WHERE `type` = %s AND source_id = %d AND site_name = %s' ) {
				return self::getMapping( $sourceId, $postType, $siteName );
			}
		} );

		$wpdb->method( 'get_results' )->willReturnCallback( function ( $data ) use ( $wpdb ) {
			list( $sql, $postType, $sourceId ) = $data;
			if ( $sql === 'SELECT * FROM wp_wp_ps_mapping WHERE `type` = %s AND source_id = %d' ) {
				return Fns::filter( function ( $row ) use ( $postType, $sourceId ) {
					return $row['type'] == $postType && $row['source_id'] == $sourceId;
				}, $this->data );
			}
		} );
	}

	public function getAllMapping(): array {
		return $this->data;
	}

	public function getMapping( $sourceId, $postType, $siteName ) {
		return Lst::find( function ( $row ) use ( $postType, $sourceId, $siteName ) {
			return $row['type'] == $postType && $row['source_id'] == $sourceId && $row['site_name'] == $siteName;
		}, $this->data );
	}

	public function addMapping( $sourceId, $type, $siteName, $targetId, $targetUrl ) {
		$this->data[] = [
			'id'         => count( $this->data ) + 1,
			'source_id'  => $sourceId,
			'type'       => $type,
			'site_name'  => $siteName,
			'target_id'  => $targetId,
			'target_url' => $targetUrl,
		];
	}
}