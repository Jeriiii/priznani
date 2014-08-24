<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

use Nette\Utils\Strings;

/**
 * Sends mail to the directory instead off using SMTP server
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ScreenshotManager {

	/**
	 *
	 * @var string url to folder where will emails be stored
	 */
	private $storeDir;

	/**
	 *
	 * @param type $mailDir dir for screenshots
	 */
	public function __construct($storeDir) {
		$this->storeDir = $storeDir;
	}

	/**
	 * Saves given html into html file
	 * @param string html to store
	 * @param string baseUrl absolute URL to root of the project to replace
	 * @return path where is file saved
	 */
	public function saveHtml($html, $baseUrl) {
		$path = $this->storeDir . '/behat_page' . rand(0, 100) . '.html';
		file_put_contents($path, $this->modifyHtml($html, $baseUrl));
		return $path;
	}

	/**
	 * Saves html and returns link to html file with page content.
	 * Tries to find link to log file and in that care returns it instead
	 * @param string html to revision
	 * @param string baseUrl absolute URL to root of the project to replace
	 * @return link where is file saved
	 */
	public function getHtmlLink($html, $baseUrl) {
		$errorLink = $this->getErrorLogLink($html);
		if (!empty($errorLink)) {//obsahuje odkaz do logu?
			return $errorLink;
		} else {
			return $this->saveHtml($html, $baseUrl);
		}
	}

	/**
	 * Modify input html for changes
	 * @param String $html
	 * @param string baseUrl absolute URL to root of the project to replace
	 * @return String modified input
	 */
	private function modifyHtml($html, $baseUrl) {
		$newHtml = Strings::replace($html, '.=\"(\S)*((\/)|(\\\\))www((\/)|(\\\\)).', '="' . $baseUrl); //nahrazeni jakekoli cesty v atributu do www
		//nahrazuje relativni adresy do www absolutnimi. nahrazuje pouze cesty v atributech (cesty co zacinaji =" )
		return $newHtml;
	}

	/**
	 * Prohledá řetězec a hledá v něm odkazy na html soubory v adresáři log
	 * @param type $html vstupní řetězec (html), kde se hleda odkaz
	 * @return string první nalezený odkaz nebo prázdný řetězec, pokud odkaz nebyl nalezen
	 */
	private function getErrorLogLink($html) {
		$links = array(); // pozn ((\/)|(\\\\)) = lomitko nebo zpetne lomitko
		if (preg_match('.\S*((\/)|(\\\\))log((\/)|(\\\\))\S*\.html.', $html, $links) == 1) {//link nalezen
			return $links[0]; //prvni nalezeny
		} else {
			return '';
		}
	}

}
