<?php


namespace PostSynchronization;


use WPML\FP\Lst;
use WPML\FP\Maybe;
use WPML\FP\Obj;

class Mapper {

	public static function savePostIdsMapping( int $sourcePostId, string $siteName, int $targetPostId, string $targetUrl ) {
		$postType = get_post_type( $sourcePostId );

		self::saveItemIdsMapping( $postType, $sourcePostId, $siteName, $targetPostId, $targetUrl );
	}


	public static function getTargetPostId( int $sourcePostId, string $siteName ): Maybe {
		$postType = get_post_type( $sourcePostId );

		return self::getItem( $postType, $sourcePostId, $siteName )->map( Obj::prop( 'target_id' ) );
	}

	/**
	 * @param int $sourceMediaId
	 * @param string $siteName
	 * @param int $targetMediaId
	 */
	public static function saveMediaIdsMapping( int $sourceMediaId, string $siteName, int $targetMediaId ) {
		self::saveItemIdsMapping( 'media', $sourceMediaId, $siteName, $targetMediaId );
	}


	public static function getMediaId( int $sourceMediaId, string $siteName ): Maybe {
		return self::getItem( 'media', $sourceMediaId, $siteName )->map( Obj::prop( 'target_id' ) );
	}

	public static function saveItemIdsMapping( string $postType, int $sourceId, string $siteName, int $targetId, string $targetUrl = '' ) {
		if ( self::getItem( $postType, $sourceId, $siteName )->isNothing() ) {
			global $wpdb;

			$wpdb->insert(
				$wpdb->prefix . 'wp_ps_mapping',
				[
					'source_id'  => $sourceId,
					'type'       => $postType,
					'site_name'  => $siteName,
					'target_id'  => $targetId,
					'target_url' => $targetUrl,
				]
			);
		}
	}

	public static function getItem( string $postType, int $sourceId, string $siteName ): Maybe {
		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}wp_ps_mapping WHERE `type` = %s AND source_id = %d AND site_name = %s";
		$row = $wpdb->get_row( $wpdb->prepare( $sql, $postType, $sourceId, $siteName ) );

		return Maybe::fromNullable( $row );
	}

	public static function getItems( string $postType, int $sourceId ): Maybe {
		global $wpdb;

		$sql    = "SELECT * FROM {$wpdb->prefix}wp_ps_mapping WHERE `type` = %s AND source_id = %d";
		$result = $wpdb->get_results( $wpdb->prepare( $sql, $postType, $sourceId ) );

		return Maybe::fromNullable( count( $result ) ? $result : null );
	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return Maybe
	 */
	public static function getTargetUrl( \WP_Post $post ) {
		$fn = function () use ( $post ) {
			$postType = get_post_type( $post->ID );

			return Mapper::getItems( $postType, $post->ID )
			             ->map( Lst::nth( 0 ) )
			             ->map( Obj::prop( 'target_url' ) );
		};

		return Cache::get( 'target-url-for' . $post->ID, $fn );
	}

	public static function postData( \WP_Post $post, SiteData $site, $featuredImageId = null ): array {
		return [
			'title'          => $post->post_title,
			'status'         => $post->post_status,
			'content'        => $post->post_content,
			'categories'     => self::mapCategories( $post, $site ),
			'tags'           => self::mapTags( $post, $site ),
			'author'         => self::mapAuthor( $post, $site ),
			'excerpt'        => $post->post_excerpt,
			'featured_media' => $featuredImageId,
		];
	}

	private static function mapCategories( \WP_Post $post, SiteData $site ): string {
		$categories = wp_get_post_categories( $post->ID );
		if ( is_wp_error( $categories ) || count( $categories ) === 0 ) {
			return '1';
		}

		$map = function ( $id ) use ( $site ) {
			return \wpml_collect( $site->categoriesMap )->get( $id, 1 );
		};

		return \wpml_collect( $categories )
			->map( $map )
			->unique()
			->implode( ',' );
	}

	private static function mapTags( \WP_Post $post, SiteData $siteData ): string {
		$tagIds = wp_get_post_tags( $post->ID, [ 'fields' => 'ids' ] );

		$map = function ( $id ) use ( $siteData ) {
			return \wpml_collect( $siteData->tagsMap )->get( $id, false );
		};

		return \wpml_collect( $tagIds )
			->map( $map )
			->filter()
			->unique()
			->implode( ',' );
	}

	private static function mapAuthor( \WP_Post $post, SiteData $site ): int {
		return Obj::propOr( 1, $post->post_author, $site->authorsMap );
	}
}