<?php


namespace PostSynchronization;


class Initializer {

	public static function addHooks() {
		$onPostSave = OnPostSave::onPostSave(
			Actions::create(),
			Actions::update(),
			Actions::delete(),
			[ SitesConfiguration::class, 'get' ],
			[ Mapper::class, 'getTargetPostId' ],
		);

		add_action( 'save_post', $onPostSave, 10, 2 );
	}

}