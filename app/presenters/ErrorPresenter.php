<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ErrorPresenter
 *
 * @author Petr
 */
class ErrorPresenter extends BasePresenter
{
    /**
     * @param  Exception
     * @return void
     */
    public function renderDefault($exception)
    {
       //debug::dump($this->application->requests[0]);
       //$this->template->lang = 'en';

        if ($this->isAjax()) { // AJAX request? Just note this error in payload.
            $this->getPayload()->error = TRUE;
            $this->terminate();
        } elseif ($exception instanceof BadRequestException) {
            $this->setView('404'); // load template 404.phtml
                        $this->template->title = 'Stránka nebyla nalezena';
                        $this->template->referer = $this->getHttpRequest()->getOriginalUri();
                } else {
            $this->setView('500'); // load template 500.phtml
                        if ($this->lang=='cs')
                            $this->template->title = 'Chyba 500: Interní chyba serveru2';
                        else
                            $this->template->title = 'Error 500: Internal Server Error2';
            Debug::processException($exception); // and handle error by Nette\Debug
        }
    }
}

?>
