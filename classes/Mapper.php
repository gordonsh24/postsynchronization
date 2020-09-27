<?php


namespace PostSynchronization;


use WPML\FP\Maybe;

class Mapper {

	CONST POST_IDS_MAP = 'post-synchronization-post-ids-map';

	/**
	 * @param int $sourcePostId
	 * @param array $targetPostIds [siteName -> ids]
	 */
	public static function savePostIdsMapping( int $sourcePostId, array $targetPostIds ) {
		$map                 = get_option( self::POST_IDS_MAP, [] );
		$map[ $sourcePostId ] = $targetPostIds;
		update_option( self::POST_IDS_MAP, $map );
	}


	public static function getTargetPostId( int $sourcePostId, string $siteName ): Maybe {
		$map = get_option( self::POST_IDS_MAP, [] );

		return Maybe::fromNullable( $map[ $sourcePostId ][ $siteName ] ?? null );
	}

	public static function postData( \WP_Post $post ): array {
		return [
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'content'    => $post->post_content,
			'categories' => 1,
			'excerpt'    => $post->post_excerpt,
		];
	}
}