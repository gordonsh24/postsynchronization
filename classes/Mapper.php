<?php


namespace PostSynchronization;


use WPML\FP\Maybe;
use WPML\FP\Obj;

class Mapper {

	const POST_IDS_MAP = 'post-synchronization-post-ids-map';
	const MEDIA_IDS_MAP = 'post-synchronization-image-ids-map';

	/**
	 * @param int $sourcePostId
	 * @param string $siteName
	 * @param int $targetPostId
	 */
	public static function savePostIdsMapping( int $sourcePostId, string $siteName, int $targetPostId ) {
		self::saveItemIdsMapping( self::POST_IDS_MAP, $sourcePostId, $siteName, $targetPostId );
	}


	public static function getTargetPostId( int $sourcePostId, string $siteName ): Maybe {
		return self::getItemId( self::POST_IDS_MAP, $sourcePostId, $siteName );
	}

	/**
	 * @param int $sourceMediaId
	 * @param string $siteName
	 * @param int $targetMediaId
	 */
	public static function saveMediaIdsMapping( int $sourceMediaId, string $siteName, int $targetMediaId ) {
		self::saveItemIdsMapping( self::MEDIA_IDS_MAP, $sourceMediaId, $siteName, $targetMediaId );
	}


	public static function getMediaId( int $sourceMediaId, string $siteName ): Maybe {
		return self::getItemId( self::MEDIA_IDS_MAP, $sourceMediaId, $siteName );
	}

	private static function saveItemIdsMapping( string $optionName, int $sourceId, string $siteName, int $targetId ) {
		$map                           = get_option( $optionName, [] );
		$map[ $sourceId ][ $siteName ] = $targetId;
		update_option( $optionName, $map );
	}

	private static function getItemId( string $optionName, int $sourceId, string $siteName ): Maybe {
		$map = get_option( $optionName, [] );

		return Maybe::fromNullable( $map[ $sourceId ][ $siteName ] ?? null );
	}

	public static function postData( \WP_Post $post, SiteData $site, $featuredImageId = null ): array {
		return [
			'title'          => $post->post_title,
			'status'         => $post->post_status,
			'content'        => $post->post_content,
			'categories'     => self::mapCategories( $post, $site ),
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

	private static function mapAuthor( \WP_Post $post, SiteData $site ): int {
		return Obj::propOr( 1, $post->post_author, $site->authorsMap );
	}
}