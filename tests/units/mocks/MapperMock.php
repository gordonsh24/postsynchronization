<?php


namespace PostSynchronization\Mocks;


trait MapperMock {

	private $data = [];

	public function setUpMapperMock() {
		global $wpdb;

		$wpdb = $this->getMockBuilder( '\wpdb' )
		             ->setMethods( [ 'get_row', 'prepare', 'insert' ] )
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
	}

	public function getAllMapping(): array {
		return $this->data;
	}
}