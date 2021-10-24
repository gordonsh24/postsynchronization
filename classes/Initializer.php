<?php


namespace PostSynchronization;


use PostSynchronization\Cache\CacheIntegrator;

class Initializer {

	public static function addHooks() {
		CustomBox::addHooks();

		add_action( 'save_post', OnPostSave::onPostSave(), 10, 2 );

		Redirections::addHooks();
		CacheIntegrator::addHooks();
	}

}