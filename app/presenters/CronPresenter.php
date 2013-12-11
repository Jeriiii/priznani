<?php

/**
 * Homepage presenter.
 *
 * Zobrayení úvodní stránky systému
 *
 * @author     Petr Kukrál
 * @package    jkbusiness
 */

class CronPresenter extends BasePresenter
{
	private $dataToDebug;
	
	public function startup() {
		parent::startup();
		
		$this->setLayout("simpleLayout");
	}

	public function actionWow()
	{
		$pripojeni_manius['ip'] = '91.219.244.178';
		$pripojeni_manius['uzivatel'] = 'priznani';
		$pripojeni_manius['heslo'] = 'SJ28XNypF';
		$pripojeni_manius['db'] = 'priznani';
		$spojeni_manius = mysql_connect($pripojeni_manius['ip'], $pripojeni_manius['uzivatel'], $pripojeni_manius['heslo']);
		mysql_select_db($pripojeni_manius['db'], $spojeni_manius);
		mysql_set_charset('utf8', $spojeni_manius);

		$now = new DateTime();
		$morning = $now->setTime(0, 0, 0)->modify('-1 day');
		$now2 = new DateTime();
		$night = $now2->setTime(23, 59, 59)->modify('-1 day');
		
		$partyConfessions = $this->context->createPartyConfessions()
								->where('release_date >= ?', $morning)
								->where('release_date <= ?', $night);

		$query = "";
		
		foreach($partyConfessions as $confession)
		{
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznanizparby (text, datum) VALUES ('".addslashes($confession->note)."', '".$release_date."');";
			mysql_query($insert, $spojeni_manius);
			$query = $query . $insert ;
		}
		
		$sexConfessions = $this->context->createForms1()
								->where('release_date >= ?', $morning)
								->where('release_date <= ?', $night);
		
		$query2 = "";
		
		foreach($sexConfessions as $confession)
		{
			$release_date = new DateTime($confession->release_date);
			$release_date = $release_date->modify('+1 day')->format('Y-m-d H:i:s');
			$insert = "INSERT into priznaniosexu (text, datum) VALUES ('".addslashes($confession->note)."', '".$release_date."');";
			mysql_query($insert, $spojeni_manius);
			$query2 = $query2 . $insert ;
		}
		$this->dataToDebug = $query;
		//mysql_query($query, $spojeni_manius);
		mysql_close($spojeni_manius);
	}
	
	public function renderWow()
	{
		$this->template->dataToDebug = $this->dataToDebug;
	}
	
}
