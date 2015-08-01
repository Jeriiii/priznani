<?php

# This file passes the content of the Readme.md file in the same directory
# through the Markdown filter. You can adapt this sample code in any way
# you like.

# Install PSR-0-compatible class autoloader
//spl_autoload_register(function($class){
//	require preg_replace('{\\\\|_(?!.*\\\\)}', DIRECTORY_SEPARATOR, ltrim($class, '\\')).'.php';
//});

# Get Markdown class
use \Michelf\Markdown;

# Read file and pass content through the Markdown parser

class MarkdownToHtml {
	
	public function __construct() {

	}
	
	public function toHtml($markdown) {
		$html = Markdown::defaultTransform($markdown);
		
		return $html;
	}
}


?>