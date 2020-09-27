<?php


namespace PostSynchronization;


class SitesConfiguration {

	/**
	 * @return SiteData[]
	 */
	public static function get(): array {
		$rawData =  defined('POST_SYNC_SITES') ? POST_SYNC_SITES : [];

		return \wpml_collect($rawData)->map([SiteData::class, 'create'])->toArray();
	}

}