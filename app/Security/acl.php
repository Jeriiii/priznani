<?php

use Nette\Security\Permission;

class Acl extends Permission {

	public function __construct() {

		//base
		//roles
		$this->addRole('guest');
		$this->addRole('unconfirmed_user', 'guest');
		$this->addRole('user', 'unconfirmed_user');
		$this->addRole('baseadmin', 'user');
		$this->addRole('admin', 'baseadmin');
		$this->addRole('superadmin', 'admin');

		// resources
		$this->addResource('Admin:Admin');
		$this->addResource('Admin:AdminNews');
		$this->addResource('Admin:Pages');
		$this->addResource('Admin:Forms');
		$this->addResource('Admin:Galleries');
		$this->addResource('Admin:AcceptImages');
		$this->addResource('Profil:EditProfil');
		$this->addResource('Profil:ShowProfil');
		$this->addResource('Profil:Galleries');

		// privileges
		$this->allow('baseadmin', 'Admin:Admin');
		$this->allow('baseadmin', 'Admin:Pages');
		$this->allow('baseadmin', 'Admin:Forms');
		$this->allow('baseadmin', 'Admin:AdminNews');
		$this->allow('baseadmin', 'Admin:Galleries');
		$this->allow('baseadmin', 'Admin:AcceptImages');
		$this->allow('user', 'Profil:EditProfil');
		$this->allow('user', 'Profil:ShowProfil');
		$this->allow('user', 'Profil:Galleries');
	}

}
