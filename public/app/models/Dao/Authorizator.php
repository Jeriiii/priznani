<?php

class Authorizator extends Nette\Object implements Nette\Security\IAuthorizator {

	const ROLE_UNCONFIRMED_USER = "unconfirmed_user";
	const ROLE_USER = "user";
	const ROLE_ADMIN = "admin";
	const ROLE_SUPERADMIN = "superadmin";
	const ROLE_ADVANCED_USER = 'advanced_user';

	private $facebook = FALSE;
	private $galleries = FALSE;
	private $forms = FALSE;
	private $accounts = FALSE; /* moznost menit ucty */
	private $files = FALSE;
	private $map = FALSE;
	private $google_analytics = FALSE;
	private $news = FALSE;

	public function isAllowed($role, $resource, $privilege) {
		if ($role == 'superadmin' || $role == "admin")
			return TRUE;
		elseif ($role == "baseadmin")
			return FALSE;
		else {
			return FALSE;
		}
	}

}

?>
