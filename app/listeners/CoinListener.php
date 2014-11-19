<?php

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

namespace POS\Listeners;

use POS\Model\UserDao,
	POS\Model\UserPropertyDao,
	POS\Model\ChatMessagesDao;

/**
 * Description of FooListener
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class CoinListener extends \Nette\Object implements \Kdyby\Events\Subscriber {

	/**
	 * Množství zlatek přidaných v případě, že je uživatel označen jako sexy
	 */
	const COIN_ADDED_IS_SEXY = 1;

	/**
	 * Množství zlatek pro toho, kdo označí někoho jako že je sexy
	 */
	const COIN_ADDED_FOR_LIKING = 0.2;

	/**
	 * Kolik zlatek bude přidáno tomu, kdo si bude povídat přes chat
	 */
	const COIN_ADD_FOR_MESSAGES = 5;

	/**
	 * Kolik zpráv musím odeslat uživateli, kterému jsem ještě nic neposlal, abych dostal zlatky
	 */
	const MINIMUM_AMOUNT_OF_MESSAGES = 5;

	/**
	 * Kolik mincí bude přidáno xtý den, kdy jsem aktivní. Který den je klíč, kolik mincí je hodnota,
	 * tedy například 7 => 7 znamená, že za sedmý den aktivity (když jsem ji vyvíjel i těch šest dní předtím)
	 * dostanu 7 mincí
	 * @var array
	 */
	private $COIN_ADDED_FOR_SIGN_IN = array(
		0 => 3,
		3 => 5,
		7 => 7,
		30 => 9
	);

	/**
	 * @var \POS\Model\UserPropertyDao
	 * @inject
	 */
	public $userPropertyDao;

	/**
	 * @var \POS\Model\ChatMessagesDao
	 * @inject
	 */
	public $chatMessagesDao;

	/**
	 * @var POS\Model\UserDao
	 * @inject
	 */
	public $userDao;

	public function __construct(UserPropertyDao $propertyDao, UserDao $userDao, ChatMessagesDao $chatMessagesDao) {
		$this->userDao = $userDao;
		$this->userPropertyDao = $propertyDao;
		$this->chatMessagesDao = $chatMessagesDao;
	}

	/**
	 * Implementace interfacu.
	 * @return array Vrací pole objektů a jejich událostí, nad kterými bude naslouchat
	 */
	public function getSubscribedEvents() {

		return array('POS\Model\YouAreSexyDao::onLike' => 'onIsSexy',
			'POS\Model\YouAreSexyDao::onDislike' => 'onIsNotSexyAnymore',
//			'Nette\Application\Application::onStartup' => 'addCoinsForMessages'//TODO: napojit na CRON
			'POS\Listeners\Services\ActivityReporter::onUserFirstTodayActivity' => 'rewardForSignIn'
		);
	}

	/**
	 * Při události, kdy je někdo označen jako sexy
	 * @param int $userID1 id uživatele, co like dal
	 * @param int $userID2 id uživatele, který like dostal
	 */
	public function onIsSexy($userID1, $userID2) {
		$this->userPropertyDao->incraseCoinsBy($userID2, self::COIN_ADDED_IS_SEXY);
		$this->userPropertyDao->incraseCoinsBy($userID1, self::COIN_ADDED_FOR_LIKING);
	}

	/**
	 * Při události, kdy je někdo ODznačen jako sexy
	 * @param int $userID1 id uživatele, co like odebral
	 * @param int $userID2 id uživatele, který like ztratil
	 */
	public function onIsNotSexyAnymore($userID1, $userID2) {
		$this->userPropertyDao->decraseCoinsBy($userID2, self::COIN_ADDED_IS_SEXY);
		$this->userPropertyDao->decraseCoinsBy($userID1, self::COIN_ADDED_FOR_LIKING);
	}

	/**
	 * Náročná funkce ke spouštění CRONem. Prohledá zprávy a najde uživatele, kteří si mezi sebou poprvé napsali každý alespoň stanovený počet zpráv.
	 * Těm potom každému přidá stanovený počet zlatek.
	 */
	public function addCoinsForMessages() {
		$toCheck = $this->chatMessagesDao->getAllCronGroupedMessages(); //groupnute zpravy
		$alreadyRewarded = $this->buildAlreadyRewarded($toCheck); //seznam id, odesílatele a příjemce, která už byla odměněna
		$toReward = $this->buildToReward($toCheck, $alreadyRewarded); //id uživatelů, kteří mají být odměněni
		foreach ($toReward as $pair) {//projde pole k odměně a odmění ty, co si mezi sebou napsali každý alespoň 5 zpráv
			if (in_array(array($pair[1], $pair[0]), $toReward)) {//pokud si ten, s kým si psal, taky zasloužil odměnu
				$this->userPropertyDao->incraseCoinsBy($pair[0], self::COIN_ADD_FOR_MESSAGES);
				$this->chatMessagesDao->markAsChecked($pair[0], $pair[1]);
			}
		}
	}

	/**
	 * Odmění uživatele za určitý počet přihlášení
	 * @param int $userID id uživatele
	 * @param int $days kolikátý den nepřetržitého přihlášení uživatel začal
	 */
	public function rewardForSignIn($userID, $days) {
		$coinsToAdd = 0;
		foreach ($this->COIN_ADDED_FOR_SIGN_IN as $day => $reward) {
			if ($days >= $day) {
				$coinsToAdd = $reward;
			}
		}
		$this->userPropertyDao->incraseCoinsBy($userID, $coinsToAdd);
	}

	/**
	 * Sestaví ze Selection pole polí, kde jsou uživatelé ve tvaru array(idOdesílatele, idPříjemce),
	 * pokud odesílatel odeslal příjemci alespoň určitý počet zpráv a již byl odměněn
	 * @param Selection $toCheck výběr group z db pomocí getAllCronGroupedMessages
	 * @return array pole polí ve tvaru array(idOdesílatele, idPříjemce)
	 */
	private function buildAlreadyRewarded($toCheck) {
		$alreadyRewarded = array();
		foreach ($toCheck as $group) {
			if ($group->offsetGet(ChatMessagesDao::COLUMN_CHECKED_BY_CRON) == 1) {//jestli už byl uživatel odměněn za tyto zprávy
				$alreadyRewarded[] = array($group->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER), $group->offsetGet(ChatMessagesDao::COLUMN_ID_RECIPIENT));
			}
		}
		return $alreadyRewarded;
	}

	/**
	 * Sestaví ze Selection pole polí, kde jsou uživatelé ve tvaru array(idOdesílatele, idPříjemce),
	 * pokud odesílatel odeslal příjemci alespoň určitý počet zpráv a ještě ne byl odměněn
	 * @param Selection $toCheck výběr group z db pomocí getAllCronGroupedMessages
	 * @param array $alreadyRewarded pole již odměněných výměn mezi uživateli
	 * @return array pole polí ve tvaru array(idOdesílatele, idPříjemce), kteří si zaslouží odměnu
	 */
	private function buildToReward($toCheck, $alreadyRewarded) {
		$toReward = array();
		foreach ($toCheck as $group) {//druhý foreach pro případ, že by některé odměněné zprávy byly až za těmi neodměněnými
			if ($group->offsetGet(ChatMessagesDao::COLUMN_CHECKED_BY_CRON) == 0) {//jestli ještě uživatel nebyl odměněn za tyto zprávy
				$testArray = array($group->offsetGet(ChatMessagesDao::COLUMN_ID_SENDER), $group->offsetGet(ChatMessagesDao::COLUMN_ID_RECIPIENT));
				if (!in_array($testArray, $alreadyRewarded) && $group->cnt >= self::MINIMUM_AMOUNT_OF_MESSAGES) {//pokud není mezi už odměněnými
					$toReward[] = $testArray; //přidá je do těch, co mají být odměněni
				}
			}
		}
		return $toReward;
	}

}
