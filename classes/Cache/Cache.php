<?php


namespace PostSynchronization\Cache;


class Cache {

	const CACHE_NAMESPACE = 'ps-cache';

	public static function get( string $key, $fn, $expiration = 0 ) {
		$key = self::buildKey( $key );

		$value = get_option( $key, null );
		if ( $value === null ) {
			$value = $fn();
			set_transient( $key, $value, $expiration );
		}

		return $value;
	}

	public static function remove( string $key ) {
		delete_transient( self::buildKey( $key ) );
	}

	public static function removeAll() {
		global $wpdb;

		$wpdb->delete( $wpdb->options, "option_name LIKE '_transient_" . self::CACHE_NAMESPACE . "%'" );
	}

	private static function buildKey( string $key ): string {
		return self::CACHE_NAMESPACE . '-' . $key;
	}
}