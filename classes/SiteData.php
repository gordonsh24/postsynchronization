<?php

namespace PostSynchronization;


class SiteData {
	/** @var string */
	public $name;

	/** @var string */
	public $url;

	/** @var string */
	public $user;

	/** @var string */
	public $password;

	public static function create( array $data ): SiteData {
		$result = new self();

		$result->name     = $data['name'] ?? null;
		$result->url      = $data['url'] ?? null;
		$result->user     = $data['user'] ?? null;
		$result->password = $data['password'] ?? null;

		return $result;
	}
}