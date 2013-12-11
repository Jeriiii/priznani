<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer,
	Nette\DateTime,
	Nette\Mail\Message;


class BaseForm extends Form
{
	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
	}
	
	/**
	 * odeslani zpravy o odeslani formulare
	 */
	
	public function sendMailAboutSendForm($form_name, $presenter) {
		$users = $presenter->context->createUsers()
			->where("form_mail", 1);
		
//		foreach($users as $user)
//		{
//			$mail = new Message;
//			$mail->setFrom('info@nejlevnejsiwebstranky.cz')
//				->addTo($user->mail)
//				->setSubject('upozornění - odpověď ve formulář')
//				->setBody("Dobrý den,\n\n posíláme upozornění, že byl vyplněn a odeslán formulář " . $form_name . " na Vašich stránkách. \n\n Tým nejlevnejšíwebstránky")
//				->send();
//		}
	}
	
	/**
	 * zaznamenani odeslani mailu do specialni tabulky
	 */
	
	public function registerNewSendForm($path, $id_form, $presenter, $name, $id_click) {
		if($path == "standart") {
			$form = $presenter->context->createForms()
						->find($id_form)
						->fetch();
			$link = $presenter->link("Admin:Forms:formsX", array("id_form" => $form->id, "type" => $form->type, "id_click" => $id_click->id));
			$name = $form->name;
		}else{
			$link = $presenter->link($path, array("id_click" => $id_click->id));
//			echo $path . " - "; echo $id_click . " - "; die($link);
		}
		$presenter->context->createForm_new_send()
				->insert(array(
					"path" => $link,
					"name" => $name,
					"date" => new DateTime()
				));
	}	
}
