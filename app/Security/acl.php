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
		$this->addResource('Profil:Edit');
		$this->addResource('Profil:ShowProfil');
		$this->addResource('Profil:Galleries');
		$this->addResource('Admin:Payments');
		$this->addResource('Admin:GameOrders');
		$this->addResource('Admin:Contacts');
		$this->addResource('Competition');
		$this->addResource('Admin:Cities');
		$this->addResource('Search:Search');

		// privileges
		$this->allow('baseadmin', 'Admin:Admin');
		$this->allow('baseadmin', 'Admin:Pages');
		$this->allow('baseadmin', 'Admin:Forms');
		$this->allow('baseadmin', 'Admin:AdminNews');
		$this->allow('superadmin', 'Admin:Galleries');
		$this->allow('baseadmin', 'Admin:AcceptImages');
		$this->allow('baseadmin', 'Admin:Payments');
		$this->allow('baseadmin', 'Admin:GameOrders');
		$this->allow('baseadmin', 'Admin:Contacts');
		$this->allow('baseadmin', 'Admin:Cities');
		$this->allow('user', 'Competition');
		$this->allow('user', 'Profil:Edit');
		$this->allow('user', 'Profil:ShowProfil');
		$this->allow('user', 'Profil:Galleries');
		$this->allow('user', 'Search:Search');
	}

}
