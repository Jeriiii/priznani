<?php 

use Cherry\JDialogs\BaseDialog;

class testDialogOne extends BaseDialog {        
    
    public function __construct(Nette\ComponentModel\IContainer $parent = NULL, $name = NULL) {
	parent::__construct($parent, $name);
	$this->template_file = APP_DIR."/dialogs/templates/basedialog.latte";		
	
	$this->addData("text", "baf");
	
	$this->addOption("title", "testDialogOne");
    }


    public function handleTest() {	
	if($this->data["text"] == "baf") 
	    $this->data["text"] = "lek";
	else if($this->data["text"] == "lek")
	    $this->data["text"] = "baf";
	
	if($this->parent->isAjax())
	    $this->invalidateControl();
	else
	    $this->redirect ("this");
    }    
}