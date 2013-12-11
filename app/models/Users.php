<?php
use Nette\Database\Connection,
    Nette\Database\Table\Selection;

class Users extends UsersBase
{
	
    public function __construct(\Nette\Database\Connection $connection)
    {
        parent::__construct("users", $connection);
    }
	
	/**
	 * vrácí všechna data o uživateli, nikoliv o partnerovi
	 */
	
	public function getUserData($id)
	{
		$user = $this->find($id)->fetch();
		
		$baseUserData = array(
			'Jméno' => $user->user_name,
			'První věta' => $user->first_sentence,
			/*'Naposledy online' => $user->last_active,*/
			'Druh uživatele' => Users::getTranslateUserProperty($user->user_property),
			/* 'Vytvoření profilu' => $user->created, */
			/* 'Email' => $user->email,*/
			'O mně' => $user->about_me,
		);
		$baseData = $this->getBaseData($user);
		$other = $this->getOtherData($user);
		$sex = $this->getSex($user);

		return $baseUserData + $baseData + $other + $sex;	
	}
}
