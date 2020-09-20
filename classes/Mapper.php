<?php


namespace PostSynchronization;


use WPML\FP\Maybe;

class Mapper {

	private static $postIdOptionName = 'post-synchronization-post-ids-map';

	/**
	 * @param int $sourcePostId
	 * @param array $targetPostIds [siteName -> ids]
	 */
	public static function savePostIdsMapping( int $sourcePostId, array $targetPostIds ) {
		$map                 = get_option( self::$postIdOptionName, [] );
		$map[ $sourcePostId ] = $targetPostIds;
		update_option( self::$postIdOptionName, $map );
	}


	public static function getTargetPostId( int $sourcePostId, string $siteName ): Maybe {
		$map = get_option( self::$postIdOptionName, [] );

		return Maybe::fromNullable( $map[ $sourcePostId ][ $siteName ] ?? null );
	}

	public static function postData( \WP_Post $post ): array {
		return [
			'title'      => $post->post_title,
			'status'     => $post->post_status,
			'content'    => $post->post_content,
			'categories' => 3,
			'excerpt'    => $post->post_excerpt,
		];
	}
}