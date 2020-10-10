<?php


namespace PostSynchronization;


class Cache {

	const CACHE_NAMESPACE = 'ps-cache-';

	public static function get( string $key, $fn ) {
		$key = self::buildKey( $key );

		$value = get_option( $key, null );
		if ( $value === null ) {
			$value = $fn();
			update_option( $key, $value, false );
		}

		return $value;
	}

	public static function remove( string $key ) {
		delete_option( self::buildKey( $key ) );
	}

	public static function removeAll() {
		global $wpdb;

		$wpdb->delete( $wpdb->options, "option_name LIKE '" . self::CACHE_NAMESPACE . "%'" );
	}

	private static function buildKey( string $key ): string {
		return self::CACHE_NAMESPACE . '-' . $key;
	}
}