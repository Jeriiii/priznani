<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Search Component
 *
 * @author Mario
 */

class Search extends Nette\Application\UI\Control
{     
         
        protected $model;
        private $user;
        private $fotos;

            public function __construct($model) {
                parent::__construct();
                $this->model = $model;
            }

        
            /*
             * vrati celeho uzivatele z databaze
             */
            public function getUserFromDB()
            {
                return $this->model->getUser($this->getUser()->id)
                            ->fetch();
            }

            /*
             * vrati novy strankovac
             */
            public function getPaginator($items, $itemsPerPage)
            {
                $vp = new \VisualPaginator($this, 'vp');
                $page = $vp->page;
                $paginator = $vp->getPaginator();
                $paginator->setItemCount($items->count()); // celkový počet položek
                $paginator->setItemsPerPage($itemsPerPage);    // počet položek na stránce
                $paginator->setPage($page);    // číslo aktuální stránky

                return $items->limit($paginator->getLength(), $paginator->getOffset());
            }

            public function actionAdvancedSearch($interested_in_men, $interested_in_women,    $interested_in_couple, $interested_in_couple_men, $interested_in_couple_women, $interested_in_group, $orientation = null,  $age_from = null, $age_to = null, $tallness = null, $shape = null, $smoke = null, $drink = null, $graduation = null) {
                $want_to_meet_men = '';
                $want_to_meet_women = '';
                $want_to_meet_couple = '';
                $want_to_meet_couple_men = '';
                $want_to_meet_couple_women = '';
                $want_to_meet_group = '';

                        /* ORIENTATION */
                        if($orientation != null){	
                        $whereOrientation ='';
                                if ($orientation == 'hetero') { 	$whereOrientation .= '"hetero",';	}
                                if ($orientation == 'homo') {	$whereOrientation .= '"homo",';	}
                                if ($orientation == 'bi'){ 	$whereOrientation .= '"bi",';		}
                                if ($orientation == 'biTry' ){	$whereOrientation .= '"biTry",';		}
                                $filterOrientation = rtrim($whereOrientation, ',');
                        } else {
                                $filterOrientation = NULL;
                        }

                        /*CHECKBOXES */
                        if($interested_in_men != null || $interested_in_women != null || $interested_in_couple != null || $interested_in_couple_men != null || $interested_in_couple_women != null || $interested_in_group != null){	
                        $whereInterested = '';
                                if ($interested_in_men == 1) {	$whereInterested .= '"man",';	$want_to_meet_men = 'want_to_meet_men'; }
                                if ($interested_in_women == 1) {	$whereInterested .= '"woman",';	 	$want_to_meet_women = 'want_to_meet_women';	}
                                if ($interested_in_couple == 1){	$whereInterested .= '"couple",';		$want_to_meet_couple = 'want_to_meet_couple';	}
                                if ($interested_in_couple_men == 1 ){	$whereInterested .= '"coupleMan",'; 	$want_to_meet_couple_men = 'want_to_meet_couple_men';	}
                                if ($interested_in_couple_women == 1) {	$whereInterested .= '"coupleWoman",';		$want_to_meet_couple_women = 'want_to_meet_couple_women';	}
                                if ($interested_in_group == 1 ){	$whereInterested .= '"group",'; 	$want_to_meet_group = 'want_to_meet_group';	}			
                                $filterInterested = rtrim($whereInterested, ',');
                        } else {
                                $filterInterested = NULL;
                        }

                        /* AGE */
                        if($age_from != null && $age_to != null){	
                                $filterAge = array('from' => $age_from, 'to'=>$age_to);
                        } else {
                                $filterAge = NULL;
                        }

                        /* TALLNESS */
                        if($tallness != null){	
                        $whereTallness ='';
                                if ($tallness == '160') { 	$whereTallness .= '"160",';	}
                                if ($tallness == '170') {	$whereTallness .= '"170",';	}
                                if ($tallness == '180'){ 	$whereTallness .= '"180",';		}
                                if ($tallness == '190' ){	$whereTallness .= '"190",';		}
                                if ($tallness == '200' ){	$whereTallness .= '"200",';		}
                                $filterTallness = rtrim($whereTallness, ',');
                        } else {
                                $filterTallness = NULL;
                        }

                        /* SHAPE */
                        if($shape != null){	
                        $whereShape ='';
                                if ($shape == '0') { 	$whereShape .= '"0",';	}
                                if ($shape == '1') {	$whereShape .= '"1",';	}
                                if ($shape == '2'){ 	$whereShape .= '"2",';		}
                                if ($shape == '3' ){	$whereShape .= '"3",';		}
                                if ($shape == '4' ){	$whereShape .= '"4",';		}
                                if ($shape == '5' ){	$whereShape .= '"5",';		}
                                $filterShape = rtrim($whereShape, ',');
                        } else {
                                $filterShape = NULL;
                        }

                        /* Smoke */
                        if($smoke != null){	
                        $whereSmoke ='';
                                if ($smoke == 'often') { 	$whereSmoke .= '"often",';	}
                                if ($smoke == 'no') {	$whereSmoke .= '"no",';	}
                                if ($smoke == 'occasionlly'){ 	$whereSmoke .= '"occasionlly",';		}
                                $filterSmoke = rtrim($whereSmoke, ',');
                        } else {
                                $filterSmoke = NULL;
                        }

                        /* Drink */
                        if($drink != null){	
                        $whereDrink ='';
                                if ($drink == 'often') { 	$whereDrink .= '"often",';	}
                                if ($drink == 'no') {	$whereDrink .= '"no",';	}
                                if ($drink == 'occasionlly'){ 	$whereDrink .= '"occasionlly",';		}
                                $filterDrink= rtrim($whereDrink, ',');
                        } else {
                                $filterDrink = NULL;
                        }

                        /* Graduation */
                        if($graduation != null){	
                        $whereGraduation ='';
                                if ($graduation == 'zs') { 	$whereGraduation .= '"zs",';	}
                                if ($graduation == 'sou') {	$whereGraduation .= '"sou",';	}
                                if ($graduation == 'sos'){ 	$whereGraduation .= '"sos",';		}
                                if ($graduation == 'vos' ){	$whereGraduation .= '"vos",';		}
                                if ($graduation == 'vs' ){	$whereGraduation .= '"vs",';		}
                                $filterGraduation = rtrim($whereGraduation, ',');
                        } else {
                                $filterGraduation = NULL;
                        }

                        //pole obsahující jednotlivé části dotazu uživatele (některé pole mohou být prázdné)
                        $AdvancedFilter = array(
                                'orientation'=> $filterOrientation,
                                'user_property' => $filterInterested, 
                                'age'=> $filterAge, 
                                'tallness' => $filterTallness, 
                                'shape'=> $filterShape, 
                                'smoke'=> $filterSmoke,
                                'drink' => $filterDrink,
                                'graduation' => $filterGraduation
                        );

                        //cyklus, který filtruje dotaz podle neprázdných, vyplněných, uživatelských údajů
                        $where = array();
                                foreach ($AdvancedFilter as $varname => $varvalue) {
                                        if($varname == 'age' && !empty($varvalue)){
                                                $where[] =" $varname BETWEEN ".$varvalue['from']." AND ".$varvalue['to'].""; 		
                                        }elseif (trim($varvalue) != ''){
                                                        $where[] =" $varname IN (".$varvalue.")"; 
                                        }
                                }

                        $advancedUsersData = $this->context->createUsers()->where($where);
                        $this->template->advancedUsersData =  $this->getPaginator($advancedUsersData, "12");
                        $this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC'); 

                        // převyplněný předchozí výběr uživatele
                        $this['advancedSearchForm']->setDefaults(array(
                        'orientation' => $orientation, 
                        'interested_in'=>array(
                                                                                $want_to_meet_men,
                                                                                $want_to_meet_women,
                                                                                $want_to_meet_couple,
                                                                                $want_to_meet_couple_men,
                                                                                $want_to_meet_couple_women,
                                                                                $want_to_meet_group
                                                                                ), 
                        'age_from' => $age_from ,
                        'age_to' => $age_to,
                        'tallness' => $tallness,
                        'shape' => $shape,
                        'smoke'=> $smoke, 
                        'drink'=>$drink, 
                        'graduation' => $graduation,
                        ));

            }


                public function renderLastHour($time = "1 HOUR"){
                        $this->template->setFile(dirname(__FILE__) . '/LastHour.latte');
                        
                        $usersData = $this->model->getUsersLastActive($this->getUserFromDB(), $time);

                        if($usersData->count() < 2){
                                $this->template->usersDataLastDay = true;
                        } else {
                                $this->template->usersDataLastDay = false;
                        }
                //	Debugger::dump($this->template->usersDataLastDay);
                        $this->template->usersData =  $this->getPaginator($usersData, "4");
                        $this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC'); 
                        $this->template->render();   

                }

                public function renderLast24h($time = "1 DAY"){
                        $this->template->setFile(dirname(__FILE__) . '/Last24Hour.latte');
                        $usersLastDay = $this->context->createUsers()->getUsersLastActive($this->getUserFromDB(), $time);

                        $this->template->usersLastDay = $this->getPaginator($usersLastDay, "4");
                        $this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');	
                        $this->template->render();   
                }

                public function renderNewlyRegistered(){

                        $usersData = $this->context->createUsers()->getUsersNewlyRegistered($this->getUserFromDB());


                        $this->template->usersData = $this->getPaginator($usersData, "4");
                        $this->template->fotos = $this->context->createUsersFoto()->getAllUserFotos()->order('id DESC');
                        $this->template->render();   
                }

                public function render()
                {				 
 
                    $this->template->setFile(dirname(__FILE__) . '/default.latte');
                    $this->template->render();   
                }

                protected function createComponentAdvancedSearchForm($name){
                        $form = new Frm\AdvancedSearchForm($this, $name);
                  return $form;
                }

}
?>
