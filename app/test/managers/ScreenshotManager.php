<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Test;

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
	 * @return path where is file saved
	 */
	public function saveHtml($html) {
		$path = $this->storeDir . '/behat_page' . rand(0, 100) . '.html';
		file_put_contents($path, $this->modifyHtml($html));
		return $path;
	}

	/**
	 * Modify input html for changes
	 * @param String $html
	 * @return String modified input
	 */
	private function modifyHtml($html) {
		$html = str_replace('/priznani/www/cache/', __DIR__ . '/../../../www/cache/', $html);
		return $html;
	}

}
