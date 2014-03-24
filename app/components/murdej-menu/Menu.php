<?php

namespace Murdej;

use Nette\Application\UI\Control;

class Menu extends Control {
	var $rootNode; // = new MenuItem();
	var $separateMenuLevel;
	protected $_selected;
	var $allNodes = array();
	protected $_path = null;
	var $templatePath = array();
	public $idCounter = 0;
	public $defaultTemplateDir = '';
	public $defaultSingleSeparator = ' | ';
	
	public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
		parent::__construct($parent, $name);
		$this->defaultTemplateDir = dirname(__FILE__);
		$this->templatePath = array(
			// sablona pro drobeckovou navigaci
			'nav' => '/Menu.latte',
			// sablona pro jednourovnovou cast menu
			'single' => '/Menu.latte',
			// sablona pro stromovou cast menu
			'tree' => '/Menu.latte',
		);
		$this->rootNode = new MenuNode();
		$this->rootNode->menu = $this;
		$this->rootNode->isRootNode = true;
	}
	
	public function setSelected($node) {
		if (is_scalar($node)) {
			if (!isset($this->allNodes[$node])) return;
			$node = $this->allNodes[$node];
		};
		$this->_path = null;
		$this->_selected = $node;
	}
	
	public function getSelected() {
		return $this->_selected;
	}
	
	public function getPath() {
		if (!$this->_path) $this->_path = $this->makePath();
		return $this->_path;
	}
	
	function makePath() {
		$node = $this->selected;
		$path = array();
		while ($node && ($node != $this->rootNode)) {
			$path[] = $node;
			$node = $node->parent;
		};
		$path = array_reverse($path);
		
		return $path;
	}

	public function render($part, $templateName = null) {
		$template = $this->template;
		$templateType = "";
		$template->path = $this->path;
		$template->selected = $this->selected;
		if ((string)$part == 'nav') {
			// Drobeckova navigace
			$templateType = "nav";
		} else if (preg_match('/^([0-9]+)([*+]?)$/', $part, $chunks)) {
			$level = (int)$chunks[1];
			// echo "l=$level ";
			$template->startNode = ($level == 0)
				? $this->rootNode
				: (isset($this->path[$level - 1])
					? $this->path[$level - 1]
					: null);
			
			switch($chunks[2]) {
				case '': 
					$templateType = 'single'; 		
					break;
				case '+': 
					$templateType = 'tree';
					$template->showAll = false;
					break;
				case '*': 
					$templateType = 'tree'; 
					$template->showAll = true;
					break;
			}
		}
		// echo '['.$part.' '.$templateType.'] ';
		$template->templateType = $templateType;
		/* die("dtd=".$this->defaultTemplateDir
			." t=$template dtp=".$this->templatePath[$templateType]); */
		$template->setFile(
			$this->defaultTemplateDir 
			.($templateName ? $templateName : $this->templatePath[$templateType])
		);
		$template->render();
	}	
	
	public function renderNav($opt = null) {
		if (!$opt) $opt = array();
		$opt += array('separator' => ' > ', 'template' => null);
		$this->template->navSeparator = $opt['separator'];
		$this->render('nav');
	}
	
	public function renderSingle($opt = 0) { // $level, $separator = ' | ') {
		// \Nette\Diagnostics\Debugger::barDump($opt);
		if (!$opt) {
			$opt = array();
		} elseif (!is_array($opt)) {
			$opt = array('level' => $opt);
		}
		$opt += array(
			'separator' => $this->defaultSingleSeparator, 
			'level' => 0, 
			'template' => null
		);
		/* $separator = ' | ';
		if (is_array($opt)) {
			if (isset($opt['separator'])) $separator = $opt['separator'];
			$level = $opt[0];
		} else {
			$level = $opt;
		};*/
		$this->template->singleSeparator = $opt['separator'];
		$this->render($opt['level'], $opt['template']);
	}
	
	public function renderTree($level = 0) {
		$this->render($level.'+');
	}

	public function renderMap($level = 0) {
		$this->render($level.'*');
	}
	
	public function renderSelectedName() {
		echo $this->selected ? $this->selected->name : "";
	}
	
	public function fromTable($data, $setupNode) {
		$usedIds = array(null);
		$newIds = array();
		$nodes = array();
		foreach($data as $row) {
			$node = new MenuNode;
			$parentId = $setupNode($node, $row);
			$nodes[$parentId][] = $node;
		}
		//\Nette\Diagnostics\Debugger::dump($nodes);
		$this->linkNodes(null, $nodes);
		//\Nette\Diagnostics\Debugger::dump($this->rootNode);
		//\Nette\Diagnostics\Debugger::dump($this->allNodes);
	}
	
	protected function linkNodes($parentId, &$nodes) {
		//echo "linkNodes($parentId)";
		if (isset($nodes[$parentId])) {
			foreach($nodes[$parentId] as $node) {
				if ($parentId) {
					$this->allNodes[$parentId]->add($node);
				} else {
					$this->rootNode->add($node);
				}
				$this->linkNodes($node->id, $nodes);
			}
		}
	}

	public function byId($id) {
		return $this->allNodes[$id];
	}
	
	public function selectByUrl($url) {
		foreach($this->allNodes as $node) {
			if ($url == $node->url) {
				$this->selected = $node;
			}
		}
	}
}

class MenuNode extends \Nette\Object {
	var $name;
	var $tooltip;
	var $link;
	var $nodes = array();
	var $parent;
	var $id;
	var $menu;
	var $isRootNode = false;
	
	public function Add($node) {
		if (is_array($node)) {
			$newNode = new MenuNode;
			foreach($node as $k => $v) {
				$newNode->{$k} = $v;
			}
			$node = $newNode;
		}
		$node->parent = $this;
		$node->menu = $this->menu;
		if (!$node->id) {
			$node->id = '__auto_id_'.$this->menu->idCounter++;
		}
		$this->nodes[] = $node;
		$this->menu->allNodes[$node->id] = $node;
		return $node;
	}
	
	public function getUrl() {
		$params = array();
		if (is_string($this->link)) {
			$params[] = $this->link;
		} else if (is_array($this->link)) {
			$params[] = $this->link[0];
			if (isset($this->link[1])) {
				$params = array_merge($params, $this->link[1]);
			}
		}
		return $params ? call_user_func_array(array($this->menu->presenter, 'link'), $params) : null;
	}
	
	public function getItemClass() {
		if ($this == $this->menu->selected) {
			return 'selected';
		} else if (in_array($this, $this->menu->path)) {
			return 'in-path';
		} else {
			return null;
		}
	}
}
