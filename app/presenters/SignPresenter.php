<?php

use Nette\Application\UI\Form;
use Nette\Security\IIdentity;

class SignPresenter extends BasePresenter
{

	public function actionIn()
	{
		// facebook
		//curl_setopt ( $ch , CURLOPT_SSL_VERIFYPEER ,  false );
		$fbUrl = $this->context->facebook->getLoginUrl(array(
			'scope' => 'user_birthday,email',
			'redirect_uri' => $this->link('//fbLogin'), // absolute
		));
		
//		$response = file_get_contents($fbUrl);
//		$params = null;
//		parse_str($response, $params);
		//die($fbUrl);

		// twitter
//		$twitter = $this->context->twitter;
//		$token = $twitter->getRequestToken();
//		$twitterSession = $this->getSession('twitter');
//		$twitterSession->oauthToken = $token['oauth_token'];
//		$twitterSession->oauthTokenSecret = $token['oauth_token_secret'];
//		$twitterUrl = $twitter->getAuthorizeURL($token);

		$this->template->fbUrl = $fbUrl;
//		$this->template->twitterUrl = $twitterUrl;
	}

	public function actionFbLogin()
	{
		$me = $this->context->facebook->api('/me');
		$identity = $this->context->facebookAuthenticator->authenticate($me);

		$this->getUser()->login($identity);
		$this->redirect('Homepage:');
	}

	protected function createComponentSignInForm()
	{
		$form = new Form;
		$form->addText('mail', 'Mail')
			->setRequired('Vyplňte e-mail.');

		$form->addPassword('password', 'Heslo')
			->setRequired('Vyplňte heslo');

		$form->addSubmit('s', 'Přihlásit se');

		$form->onSuccess[] = callback($this, 'signInFormSubmitted');
		return $form;
	}

	public function signInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			$user = $this->getUser();
			$user->login($values->mail, $values->password);
			if($this->user->isInRole("admin") || $this->user->isInRole("superadmin")) {
				$this->redirect('Admin:Forms:forms');
			} else {
				$this->redirect('Homepage:');
			}

		} catch (\Nette\Security\AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}

	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Uživatel byl odhlášen.');
		$this->redirect('Homepage:');
	}

}
