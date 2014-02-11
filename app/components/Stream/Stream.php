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
        private $offset;
        
	public function __construct($data)
        {                        
            parent::__construct();
            $this->dataForStream = $data;
	}

	public function render()
	{
		$this->template->setFile(dirname(__FILE__) . '/stream.latte');
		// TO DO - poslání dat šabloně
                
              //  Nette\Diagnostics\Debugger::Dump($this->offset);
                if(!empty($this->offset)){
                     $this->template->stream = $this->dataForStream->limit($this->offset, 3);
                     $this->template->render();                
                } else {
                    $this->template->stream = $this->dataForStream->limit(3);
                    $this->template->render();                
                }              
		
               
                
	}
	
	/* vrací další data do streamu */
	public function handleGetMoreData($offset) {
            $this->offset = $offset;    
            return $this->dataForStream->limit($this->offset,3);  
	}
}
?>
