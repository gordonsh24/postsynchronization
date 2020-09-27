<?php


namespace PostSynchronization;


use WPML\FP\Maybe;

class Mapper {

	const POST_IDS_MAP = 'post-synchronization-post-ids-map';

	/**
	 * @param int $sourcePostId
	 * @param array $targetPostIds [siteName -> ids]
	 */
	public static function savePostIdsMapping( int $sourcePostId, array $targetPostIds ) {
		$map                  = get_option( self::POST_IDS_MAP, [] );
		$map[ $sourcePostId ] = $targetPostIds;
		update_option( self::POST_IDS_MAP, $map );
	}


	public static function getTargetPostId( int $sourcePostId, string $siteName ): Maybe {
		$map = get_option( self::POST_IDS_MAP, [] );

		return Maybe::fromNullable( $map[ $sourcePostId ][ $siteName ] ?? null );
	}

	public static function postData( \WP_Post $post, SiteData $site ): array {
		return [
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'content'    => $post->post_content,
			'categories' => self::mapCategories( $post, $site ),
			'excerpt'    => $post->post_excerpt,
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
			->pluck( 'term_id' )
			->map( $map )
			->unique()
			->implode( ',' );
	}
}