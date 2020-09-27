<?php


namespace PostSynchronization;


class Initializer {

	public static function addHooks() {
		$onPostSave = OnPostSave::onPostSave(
			Actions::create(),
			Actions::update(),
			function ( SiteData $siteData, int $targetPostId ) {
				return function () use ( $siteData, $targetPostId ) {
					return Actions::delete( $siteData, $targetPostId );
				};
			},
			[ self::class, 'getSitesConfiguration' ],
			[ Mapper::class, 'getTargetPostId' ],
		);

		add_action( 'save_post_post', $onPostSave, 10, 2 );
	}

	/**
	 * @return SiteData[]
	 */
	public static function getSitesConfiguration(): array {
		$siteData           = new SiteData();
		$siteData->name     = 'gdzienazabieg';
		$siteData->url      = 'http://gdzienazabieg.test/';
		$siteData->user     = 'admin';
		$siteData->password = 'password';

		return [
			$siteData
		];
	}
}