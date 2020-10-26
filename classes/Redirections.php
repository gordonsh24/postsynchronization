<?php


namespace PostSynchronization;


use function WPML\FP\partialRight;

class Redirections {

	public static function addHooks() {
		add_filter( 'the_posts', [ self::class, 'redirectPost' ], 10, 2 );
		add_filter( 'post_link', [ self::class, 'postLink' ], 10, 2 );
	}

	public static function redirectPost( $posts, \WP_Query $query ) {
		if ( count( $posts ) === 1 && strlen( $query->query['name'] ) ) {
			$post = current( $posts );
			Mapper::getTargetUrl( $post )->map( partialRight( '\PostSynchronization\wp_redirect', 301 ) );
		}

		return $posts;
	}

	public static function postLink( $permalink, $post ) {
		return Mapper::getTargetUrl( $post )->getOrElse( $permalink );
	}
}