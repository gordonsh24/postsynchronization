<?php


namespace PostSynchronization\Migrations;


use PostSynchronization\Mapper;
use PostSynchronization\TargetPostInfo;

class OptionToTable {

	public static function run( $observer ) {
		self::createTableIfNeeded();
		self::migratePosts( $observer );
		self::migrateMedia( $observer );
	}

	private static function createTableIfNeeded() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "
		CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wp_ps_mapping (
			id INT auto_increment NOT NULL,
			source_id INT NOT NULL,
			`type` varchar(10) DEFAULT 'post' NOT NULL,
			site_name varchar(100) NOT NULL,
			target_id int NOT NULL,
			target_url varchar(255) NULL,
			PRIMARY KEY  (id),
			KEY source_id_site_name (source_id, site_name)		
		) $charset_collate
		";

		$wpdb->query( $sql );
	}

	private static function migratePosts( $observer ) {
		$mapping = get_option( 'post-synchronization-post-ids-map', [] );

		foreach ( $mapping as $sourceId => $targetSites ) {
			$postType = get_post_type( $sourceId );

			foreach ( $targetSites as $siteName => $targetId ) {
				$targetUrl = TargetPostInfo::getTargetUrl( $siteName, $targetId );

				Mapper::saveItemIdsMapping( $postType, $sourceId, $siteName, $targetId, $targetUrl );

				call_user_func( $observer, sprintf( 'Inserted %d %s %s %d', $sourceId, $postType, $siteName, $targetId ) );
			}
		}
	}


	private static function migrateMedia( $observer ) {
		$mapping = get_option( 'post-synchronization-image-ids-map' );

		foreach ( $mapping as $sourceId => $targetSites ) {
			foreach ( $targetSites as $siteName => $targetId ) {
				Mapper::saveItemIdsMapping( 'media', $sourceId, $siteName, $targetId, '' );

				call_user_func( $observer, sprintf( 'Inserted %d %s %s %d', $sourceId, 'media', $siteName, $targetId ) );
			}
		}
	}
}