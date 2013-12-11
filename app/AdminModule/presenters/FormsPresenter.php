<?php

/**
 * Form presenter.
 *
 * Obsluha administrační části systému.
 * Formuláře.
 *
 * @author     Petr Kukrál
 * @package    Safira
 */

namespace AdminModule;

use Nette\Application\UI\Form as Frm,
	Nette\Utils\Finder;

class FormsPresenter extends AdminSpacePresenter
{
	/*
	 * stavy:
	 * 0 - nevyrizeno
	 * 1 - vyrizeno
	 * 2 - do kose
	 * 3 - na facebook
	 */
	
	/*smazat*/
	public $type;
	public $show_mark;
	
	public function actionForms()
	{
		$this->template->forms = $this->context->createForms()
									->order("id DESC");
	}
	
	public function actionFormsQuery($id_click)
	{
		$this->template->id_click = $id_click;
			$this->template->forms = $this->context->createForms_query()
										->order("id DESC");
	}


	public function renderFormsX($type, /*jake prispevky se budou zobrazovat - virizene-nevyrizne-vse*/ $show_mark, $idSelectAdmin = null)
	{
		$this->show_mark = $show_mark;
		$this->template->type = $type;
		$this->template->show_mark = $show_mark;
		$this->template->form2 = "1";
		
		$unmark_counter = $this->getTable($type)
											->where("mark", 0)
											->count();
		$this->template->unmark_counter = $unmark_counter; //obsahuje počet nevyřízených přiznání pro danou sekci
				
		$forms = $this->getTable($type);
		$last = $this->getTable($type)
					->getLastScheduledConfession();
		
		/* třídění podle vybraného admina */
		if(! empty ($idSelectAdmin)) {
			$forms->where("adminID", $idSelectAdmin);
			$this->template->selectAdmin = $this->context->createUsers()
												->get($idSelectAdmin)
												->user_name;
		}else{
			$this->template->selectAdmin = "všichni";
		}
		/* pro třízení podle admina */
		$this->template->admins = $this->context->createUsers()
									->where("role", "admin");
		
		if(!$this->isAjax()) {
			
			switch ($show_mark) {
				case "unmark":
					$forms->where("mark", 0);
					break;
				case "mark":
					$forms->where("mark", 1);
					break;
				case "rubbish":
					$forms->where("mark", 2);
					break;
				case "fb":
					$forms->where("mark", 3);
					break;
				default:
					break;
			}
			
			$forms->order("mark ASC, id ASC");
		
			$vp = new \VisualPaginator($this, 'vp');
			$page = $vp->page;
			$paginator = $vp->getPaginator();
			$paginator->setItemCount($forms->count()); // celkový počet položek 
			$paginator->setItemsPerPage(50); // počet položek na stránce
			$paginator->setPage($page); // číslo aktuální stránky
			$this->template->forms = $forms
				->limit($paginator->getLength(), $paginator->getOffset());
			
		}
		$this->template->last = $last;
	}
	
	public function renderConfessionDetail($id, $type)
	{
		$confession = $this->getTable($type)
						->find($id)
						->fetch();
		
		$admin = $this->context->createUsers()
						->find($confession->adminID)
						->fetch();
		
		switch ($confession->mark) {
				case 0:
					$mark = "nevyřízeno";
					break;
				case 1:
					$mark = "vyřízeno";
					break;
				case 2:
					$mark = "koš";
					break;
				case 3:
					$mark = "na FB";
					break;
				case 4:
					$mark = "duplicitní";
					break;
				default:
					$mark = "neznámý";
					break;
			}
		
		$this->template->mark = $mark;
		$this->template->admin = $admin;
		$this->template->confession = $confession;
	}

	public function handleChangeAndschedule($id, $type)
	{
		switch ($type) 
		{
			case "1":
				$forms = $this->context->createForms1()
							->find($id)
							->fetch();
				
				$id_insert = $this->context->createAdvices()
							->insert(array(
								"note" => $forms->note,
								"create" => $forms->create
							))
							->id;
				
				$advice = $this->context->createAdvices()
							->find($id_insert)
							->fetch();
				
				$lastAdvice = $this->context->createAdvices()
								->getLastScheduledConfession();

				$this->schedulingAdvice($lastAdvice, $advice);
				
				$this->context->createForms1()
					->find($id)
					->delete();
				break;
			
			case "2":
				$forms = $this->context->createAdvices()
							->find($id)
							->fetch();
				
				$id_insert = $this->context->createForms1()
								->insert(array(
									"note" => $forms->note,
									"create" => $forms->create
								));
				
				$confession = $this->context->createForms1()
								->find($id_insert)
								->fetch();
				
				$lastAdvice = $this->context->createForms1()
								->getLastScheduledConfession();

				
				$this->scheduling($lastAdvice, $confession);
				
				$this->context->createAdvices()
					->find($id)
					->delete();
				break;
		}
		$this->increaseAdminScore(3);
		$this->flashMessage("Text byl přehozen a naplánován");
		$this->redirect("this");
	}
	
	public function handleMoveToRubbish($id, $type)
	{
		$this->getTable($type)
				->find($id)
				->update(array(
					"mark" => "2"
				));
		$this->increaseAdminScore(1);
		$this->flashMessage("Text byl přesunut do koše");
		$this->redirect("this");
	}

	public function getTable($type) {
		if($type == 1)
		{
			return $this->context->createForms1();
		}
		elseif($type == 2) 
		{
			return $this->context->createAdvices();
		}
		else
		{
			return $this->context->createPartyConfessions();
		}
	}
	
	protected function createComponentFormNewForm($name) {
		return new Frm\formNewForm($this, $name);
	}
	
	public function handlemarkForm($id, $type, $doit, $show_mark)
	{

		$subform = $this->getTable($type)
			->find($id);
		$subforms = $this->getTable($type);
		$forms = $this->getTable($type);
		
		if($doit == "mark")
		{
			$this->mark($subforms, $subform, $type);			
		}elseif($doit == "unmark"){
			$subform
				->update(array(
				    "mark" => 0,
					"release_date" => NULL,
					"sort_date" => NULL
				));
		}elseif($doit == "fbmark"){ //fb check
			$subform
				->update(array(
				    "mark" => 1
				));
		}elseif($doit == "duplicate"){
			$subform
				->update(array(
				    "mark" => 4,
					"release_date" => new \Nette\DateTime(),
					"sort_date" => new \Nette\DateTime()
				));
		}else{ // to fb
			$this->mark($subforms, $subform, $type);
			$subform
				->update(array(
				    "mark" => 3
				));
		}

		if($this->isAjax("changeButton")) {
			
			switch ($show_mark) {
				case "unmark":
					$forms->where("mark", 0);
					$this->assignAdminToConfession($id, $type);
					break;
				case "mark":
					$forms->where("mark", 1);
					break;
				case "rubbish":
					$forms->where("mark", 2);
					break;
				case "fb":
					$forms->where("mark", 3);
					break;
				case "duplicate":
					$forms->where("mark", 4);
					break;
				default:
					break;
			}

			$forms->order("mark ASC, id ASC");
			
			$vp = new \VisualPaginator($this, 'vp');
			$page = $vp->page;
			$paginator = $vp->getPaginator();
			$paginator->setItemCount($forms->count()); // celkový počet položek 
			$paginator->setItemsPerPage(50); // počet položek na stránce
			$paginator->setPage($page); // číslo aktuální stránky
			$this->template->forms = $forms
				->limit($paginator->getLength(), $paginator->getOffset());
							
			$this->template->id_color_row = $id;
			$this->increaseAdminScore(2);
			$this->invalidateControl('changeButton');
		}
	}
	
	public function assignAdminToConfession($id_confession, $type)
	{
		$this->getTable($type)
			->find($id_confession)
			->update(array(
				"adminID" => $this->getUser()->id
			));
	}

	public function mark($subforms, $subform, $type)
	{
		$confession = $subforms
							->getLastScheduledConfession();

		if($type == "1")
			$this->scheduling($confession, $subform);
		else
			$this->schedulingAdvice($confession, $subform);
	}

	private function schedulingAdvice(/* posledni naplanovane */ $confession, /*ma se preplanovat*/ $subform){
		$this->scheduling($confession, $subform, $time = "+30 minutes");
	}

	private function scheduling(/* posledni naplanovane */ $confession, /*ma se preplanovat*/ $subform, $time = "+20 minutes")
	{
		$special_time = array(
				"01" => "1",
				"02" => "1",
				"03" => "2",
				"05" => "2",
		);

		$new_release_date = new \Nette\DateTime($confession->release_date);
		$now = new \Nette\DateTime();
		
		/* kdyz by melo byt vydani do minulosti */
		if($new_release_date < $now)
		{
			$new_release_date = $now;
			$new_release_date->setTime(date_format($new_release_date, "H") + 1, 0);
		}
		
		$hour = date_format($confession->release_date, "H");

		if(array_key_exists($hour, $special_time)) 
		{
			$new_release_date->modify("+" . $special_time[$hour] . " hours");
		}else{
			$new_release_date->modify($time);
		}

		$subform
			->update(array(
				"mark" => 1,
				"release_date" => $new_release_date,
				"sort_date" => $new_release_date
			));
	}

	public function handledeleteFormX($type, $id)
	{
		$this->getTable($type)
			->find($id)
			->delete();
		
		$this->increaseAdminScore(1);
		$this->flashMessage("Položka ve formuláři byla smazána.");
		$this->redirect("this");
	}
	
	public function handledeleteForm($id_form)
	{
		$form = $this->context->createForms()
				->where("id", $id_form)
				->fetch();
		
		switch ($form->type) 
		{
			case "1":
				$this->context->createForms1()
					->where("id_form", $id_form)
					->delete();
				break;
			
			case "2":
				$this->context->createForms2()
					->where("id_form", $id_form)
					->delete();
				break;
			
			case "3":
				$this->context->createForms3()
					->where("id_form", $id_form)
					->delete();
				break;
		}
		
		$this->context->createPages_Forms()
				->where("id_form", $id_form)
				->delete();
		
		$form->delete();
		
		$this->flashMessage("Formulář byl smazán.");
		$this->redirect("this");
	}
	
}
?>