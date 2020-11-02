<?php


namespace PostSynchronization\Tags;

use PostSynchronization\SiteData;
use WPML\FP\Curryable;
use WPML\FP\Either;

/**
 * Class Sync
 * @package PostSynchronization\Tags
 *
 * @method static callable|Either createIfNotExist( ...$siteData, ...$name ): Curried :: \SiteData->string->Either
 */
class Sync {
	use Curryable;

	public static function init() {

		self::curryN( 'createIfNotExist', 2, function ( SiteData $site, string $name ) {
			$create = function () use ( $site, $name ) {
				return API::create( $site, $name );
			};

			return API::find( $site, $name )->orElse( $create )->join();
		} );

	}

}

Sync::init();