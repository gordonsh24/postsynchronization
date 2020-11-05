<?php


namespace PostSynchronization;


use WPML\FP\Curryable;
use WPML\FP\Obj;
use WPML\FP\Relation;
use function WPML\FP\pipe;

/**
 * Class SitesConfiguration
 * @package PostSynchronization
 *
 * @method static callable|string getByName(...$siteName): Curried :: string -> SiteData
 */
class SitesConfiguration {
	use Curryable;

	public static function init() {
		self::curryN( 'getByName', 1, function ( $siteName ) {
			return \wpml_collect( self::get() )->first( pipe( Obj::prop( 'name' ), Relation::equals( $siteName ) ) );
		} );
	}

	/**
	 * @return SiteData[]
	 */
	public static function get(): array {
		$rawData = defined( 'POST_SYNC_SITES' ) ? POST_SYNC_SITES : [];

		return \wpml_collect( $rawData )->map( [ SiteData::class, 'create' ] )->toArray();
	}
}

SitesConfiguration::init();