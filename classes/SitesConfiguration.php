<?php


namespace PostSynchronization;


use WPML\FP\Obj;
use WPML\FP\Relation;
use function WPML\FP\pipe;

class SitesConfiguration {

	/**
	 * @return SiteData[]
	 */
	public static function get(): array {
		$rawData =  defined('POST_SYNC_SITES') ? POST_SYNC_SITES : [];

		return \wpml_collect($rawData)->map([SiteData::class, 'create'])->toArray();
	}

	public static function getByName( $siteName ) {
		return \wpml_collect( self::get() )->first( pipe( Obj::prop( 'name' ), Relation::equals( $siteName ) ) );
	}
}