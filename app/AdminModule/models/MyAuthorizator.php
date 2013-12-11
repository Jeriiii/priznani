<?php

class MyAuthorizator extends Nette\Object implements Nette\Security\IAuthorizator
{
	private $facebook = FALSE;
	private $galleries = FALSE;
	private $forms = FALSE;
	private $accounts = FALSE; /* moznost menit ucty */
	private $files = FALSE;
	private $map = FALSE;
	private $google_analytics = FALSE;
	private $news = FALSE;
	
	public function isAllowed($role, $resource, $privilege)
	{
		if($role == 'superadmin')
			return TRUE;
		elseif($role == "baseadmin")
			return FALSE;
		elseif($role == "admin")
		{
			switch ($resource)
			{
				case "galleries":
					return $this->galleries;
				case "forms":
					return $this->forms;
				case "accounts":
					return $this->accounts;
				case "facebook":
					return $this->facebook;
				case "files":
					return $this->files;
				case "map":
					return $this->map;
				case "files":
					return $this->files;
				case "map":
					return $this->map;
				case "google_analytics":
					return $this->google_analytics;
				case "news":
					return $this->news;
				default:
					return FALSE;
			}
		}
	}
	
	public function setParametrs($galleries = FALSE, $forms = FALSE, $accounts = FALSE, $facebook = FALSE, $files = FALSE, $map = FALSE, $google_analytics = FALSE, $news = FALSE)
	{
		$this->galleries = $galleries;
		$this->forms = $forms;
		$this->facebook = $facebook;
		$this->accounts = $accounts;
		$this->files = $files;
		$this->map = $map;
		$this->google_analytics = $google_analytics;
		$this->news = $news;
	}
}
	
?>
