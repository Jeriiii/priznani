<?php

/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 * created on 2.6.2015
 */

namespace POS\Webloaders;

use WebLoader\FileCollection;
use WebLoader\Compiler;
use JavaScriptPacker;
use WebLoader\Nette\JavaScriptLoader;
use Nette\Templating\Template;

/**
 * Description of OnePageWebloader
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */
class OnePageWebloader {

	public static function js(Template $template) {
		$files = new FileCollection(WWW_DIR . '/js');
		$files->addFiles(array(
			'profilePhotoBackground.js',
			'stream.js',
			'lists/initFriendRequest.js',
			'lists/initFriends.js',
			'lists/initBlokedUsers.js', //zakomentováno do první verze přiznání
			'lists/initMarkedFromOther.js',
			'onepage/default.js',
			'plugins/intro/intro.min.js',
			'plugins/intro/introAutostart.js',
		));
		$compiler = Compiler::createJsCompiler($files, WWW_DIR . '/cache/js');
		$compiler->addFilter(function ($code) {
			$packer = new JavaScriptPacker($code, "None");
			return $packer->pack();
		});
		return new JavaScriptLoader($compiler, $template->basePath . '/cache/js');
	}

}
