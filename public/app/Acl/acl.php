<?php

use Nette\Security\Permission;

class Acl extends Permission {

	public function __construct() {

		//base
		//roles
		$this->addRole('guest');
		$this->addRole('unconfirmed_user', 'guest');
		$this->addRole('user', 'unconfirmed_user');
		$this->addRole('advanced_user', 'user'); //uživatel od nás, u kterého sledujeme statistiky
		$this->addRole('baseadmin', 'advanced_user');
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
		$this->addResource('Admin:Statistic');
		$this->addResource('Competition');
		$this->addResource('Chat');
		$this->addResource('Admin:Cities');
		$this->addResource('Search:Search');
		$this->addResource('Admin:News');

		//blog - funkce
		$this->addResource('article');
		$this->addResource('delete-any-comment'); //smazat jakýkoliv komentář na streamu, u obrázků ...
		// privileges
		$this->allow('baseadmin', 'Admin:Admin');
		$this->allow('baseadmin', 'Admin:Forms');
		$this->allow('superadmin', 'Admin:Galleries');
		$this->allow('superadmin', 'Admin:AcceptImages');
		$this->allow('superadmin', 'Admin:Payments');
		$this->allow('superadmin', 'Admin:GameOrders');
		$this->allow('superadmin', 'Admin:Contacts');
		$this->allow('superadmin', 'Admin:Cities');
		$this->allow('superadmin', 'Admin:Statistic');
		$this->allow('superadmin', 'Admin:News');
		$this->allow('user', 'Competition');
		$this->allow('user', 'Profil:Edit');
		$this->allow('user', 'Profil:ShowProfil');
		$this->allow('user', 'Profil:Galleries');
		$this->allow('user', 'Search:Search');
		$this->allow('user', 'Chat');
		$this->allow('admin', 'article', array('adminMenu', 'deleteArticle', 'newArticle', 'editArticle'));
		$this->allow('superadmin', 'delete-any-comment');
	}

}
