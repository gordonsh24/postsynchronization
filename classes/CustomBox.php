<?php

namespace PostSynchronization;


use WPML\FP\Obj;

class CustomBox {

	public static function display() {
		$render = function ( $post ) {
			?>

            <legend><b><?php _e( 'Select websites where this post should be synchronized to: ', 'postsynchronization' ) ?></b></legend>
			<?php foreach ( SitesConfiguration::get() as $siteData ): ?>
                <input type="checkbox"
                       name="postsynchronization_site_name[]"
                       value="<?php echo $siteData->name ?>"
	                <?php echo PostSynchronizationSettings::shouldSynchronize( $post->ID, $siteData->name ) ? 'checked="checked"' : '' ?>
                />
				<?php echo $siteData->name ?><br/>
			<?php endforeach; ?>

			<?php
		};

		add_meta_box( 'postsynchronization_box', __( 'Synchronize post to:', 'postsynchronization' ), $render, 'post' );
	}

	public static function save( $postId ) {
		if ( empty( $_POST ) ) {
			return;
		}

		$sites = Obj::propOr( [], 'postsynchronization_site_name', $_POST );
		PostSynchronizationSettings::saveSites( $postId, $sites );
	}
}