<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use JiraRestApi\User\User;
use JiraRestApi\User\UserService;

class tkJiraUser extends UserService {

	private $uri = '/user';

	/**
	 * Returns a list of users that match the search string and/or property.
	 *
	 * @param array $paramArray
	 *
	 * @return User[]
	 * @throws \JsonMapper_Exception
	 *
	 * @throws \JiraRestApi\JiraException
	 */
	public function pickUsers( $paramArray ) {
		$queryParam = '?' . http_build_query( $paramArray );

		$ret = $this->exec( $this->uri . '/picker' . $queryParam, null );

		$this->log->info( "Result=\n" . $ret );

		$userData = json_decode( $ret );
		$users    = [];

		foreach ( $userData->users as $user ) {
			$users[] = $this->json_mapper->map(
				$user, new User()
			);
		}

		return $users;
	}
}
