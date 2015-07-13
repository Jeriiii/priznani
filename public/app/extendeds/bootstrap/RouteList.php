<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 9.7.2015
 */

namespace POS\Ext;

use Nette\Application\Routers\Route;

/**
 * Zaregistrovává všechny routy v datenode
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class RouteList extends \Nette\Application\Routers\RouteList {

	public function __construct($module = NULL) {
		parent::__construct($module);

		$this[] = new Route('index.php', 'OnePage:default', Route::ONE_WAY);
		//$this[] = new Route('//[www.]priznanizparty.cz/[/<presenter>/<url>]', array(
		//	'presenter' => 'Page',
		//	'action' => 'default',
		//	'url' => 'priznani-z-party'
		//	));
		//$this[] = new Route('//[www.]priznanizparby.cz/[/<presenter>/<url>]', array(
		//	'presenter' => 'Page',
		//	'action' => 'default',
		//	'url' => 'priznanizparby'
		//	));
		//$this[] = new Route('//priznaniosexu.cz/[/<presenter>/<url>]', array(
		//	'presenter' => 'Page',
		//	'action' => 'default',
		//	'url' => 'priznani-o-sexu'
		//	));
		//$this[] = new Route('//priznaniosexu.cz/poradna/[/<presenter>/<url>]', array(
		//	'presenter' => 'Page',
		//	'action' => 'default',
		//	'url' => 'poradna-o-sexu'
		//	));
		//$this[] = new Route('//priznaniosexu.cz/priznani/<id>', array(
		//	'presenter' => 'Page',
		//	'action' => 'confession',
		//	'id' => '<id>'
		//	));
		//$this[] = new Route('//priznaniosexu.cz/poradna/<id>', array(
		//	'presenter' => 'Page',
		//	'action' => 'advice',
		//	'id' => '<id>'
		//	));
		$this[] = new Route('registrace[/<action>]', 'DatingRegistration:default');
		$this[] = new Route('priznaniosexu', array(
			'presenter' => 'OnePage',
			'action' => 'default',
			'priznani' => '1'
			), Route::ONE_WAY);
		$this[] = new Route('<presenter>/<action>[/<url>]', 'OnePage:default');
	}

}
