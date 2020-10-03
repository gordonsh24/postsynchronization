<?php


namespace PostSynchronization;


class Initializer {

	public static function addHooks() {
		add_action( 'add_meta_boxes', [ CustomBox::class, 'display' ] );
		add_action( 'save_post', [ CustomBox::class, 'save' ], 9, 1 );

		add_action( 'save_post', OnPostSave::onPostSave(), 10, 2 );
	}

}