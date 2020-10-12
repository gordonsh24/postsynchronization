<?php

namespace WPML\LIB\WP;

trait TransientMock {

	private $transients = [];

	public function setUpTransientMock() {

		\WP_Mock::userFunction( 'get_transient', [
			'return' => function ( $name ) {
				return array_key_exists( $name, $this->transients ) ? $this->transients[ $name ] : null;
			}
		] );

		\WP_Mock::userFunction( 'set_transient', [
			'return' => function ( $name, $value, $expired = 0 ) {
				$this->transients[ $name ] = $value;

				return true;
			}
		] );

		\WP_Mock::userFunction( 'delete_transient', [
			'return' => function ( $name ) {
				unset( $this->transients[ $name ] );

				return true;
			}
		] );
	}
}
