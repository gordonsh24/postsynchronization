<?php


namespace PostSynchronization;


use WPML\FP\Lst;
use WPML\FP\Obj;
use function WPML\FP\partialRight;

class Redirections {

	public static function addHooks() {
		add_filter( 'the_posts', [ self::class, 'redirectPost' ], 10, 2 );
	}

	public static function redirectPost( $posts, \WP_Query $query ) {
		if ( count( $posts ) === 1 && strlen( $query->query['name'] ) ) {
			$post     = current( $posts );
			$postType = get_post_type( $post->ID );

			Mapper::getItems( $postType, $post->ID )
			      ->map( Lst::nth( 0 ) )
			      ->map( Obj::prop( 'target_url' ) )
			      ->map( partialRight( 'wp_redirect', 301 ) );
		}

		return $posts;
	}
}