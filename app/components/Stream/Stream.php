<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Stream
 *
 * @author Petr
 */

class Stream extends Nette\Application\UI\Control
{     
        protected $dataForStream;
        private $offset = null;
        
	public function __construct($data)
        {                        
            parent::__construct();
            $this->dataForStream = $data;
	}

	public function render()
	{
		$this->template->setFile(dirname(__FILE__) . '/stream.latte');
		// TO DO - poslání dat šabloně
                                
                if(!empty($this->offset)){
                     $this->template->stream = $this->dataForStream->limit(3,$this->offset);
                     $this->template->render();                
                } else {
                    $this->template->stream = $this->dataForStream->limit(3);                  
                    $this->template->render();                
                }              
		
               
                
	}
	
	/* vrací další data do streamu */
	public function handleGetMoreData($offset) {
            $this->offset = $offset; 

            if ($this->presenter->isAjax()) {
              
                $this->invalidateControl('posts');                
            } else {
                $this->redirect('this');                
            }
	}      
}
?>
