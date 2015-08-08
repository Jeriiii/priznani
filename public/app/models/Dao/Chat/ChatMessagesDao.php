<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * Slouží k práci se zprávami chatu v databázi.
 * Zajištuje jejich ukládání, různé způsobi načítání, aktualizaci stavu apod.
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
class ChatMessagesDao extends AbstractDao {

	const TABLE_NAME = "chat_messages";

	/* Column name */
	const COLUMN_ID = "id";
	const COLUMN_ID_SENDER = "id_sender";
	const COLUMN_ID_RECIPIENT = "id_recipient";
	const COLUMN_TEXT = "text";
	const COLUMN_TYPE = "type";
	const COLUMN_READED = "readed";
	const COLUMN_CHECKED_BY_CRON = "checked_by_cron";
	const COLUMN_SEND_NOTIFY = "sendNotify";
	const COLUMN_CONVERSATION_ID = "id_conversation";
	const COLUMN_SENDED_DATE = 'sendedDate';


	/* priznakove konstanty */
	/* stav zpravy */
	const MESSAGE_UNREADED = 0;
	const MESSAGE_READED = 1;

	/* typy zprav */
	const TYPE_TEXT_MESSAGE = 0;
	const TYPE_INFO_MESSAGE = 1;

	public function getTable() {
		return $this->createSelection(self::TABLE_NAME);
	}

	/**
	 * Přidá novou textovou zprávu ("odešle" novou zprávu)
	 * @param int $idSender odesilatel zprávy
	 * @param int $idRecipient příjemce zprávy
	 * @param String $text text zprávy
	 * @return Nette\Database\Table\IRow | int | bool vytvořená zpráva
	 */
	public function addTextMessage($idSender, $idRecipient, $text) {
		$sel = $this->getTable();
		return $sel->insert(array(
				self::COLUMN_ID_SENDER => $idSender,
				self::COLUMN_ID_RECIPIENT => $idRecipient,
				self::COLUMN_TEXT => $text,
				self::COLUMN_TYPE => self::TYPE_TEXT_MESSAGE,
				self::COLUMN_READED => self::MESSAGE_UNREADED
		));
	}

	/**
	 * Přidá novou textovou zprávu ("odešle" novou zprávu)
	 * @param int $idSender odesilatel zprávy
	 * @param int $idConversation ID konverzace
	 * @param String $text text zprávy
	 * @return Nette\Database\Table\IRow | int | bool vytvořená zpráva
	 */
	public function addConversationMessage($idSender, $idConversation, $text) {
		$sel = $this->getTable();
		return $sel->insert(array(
				self::COLUMN_ID_SENDER => $idSender,
				self::COLUMN_CONVERSATION_ID => $idConversation,
				self::COLUMN_TEXT => $text,
				self::COLUMN_TYPE => self::TYPE_TEXT_MESSAGE,
				self::COLUMN_READED => self::MESSAGE_UNREADED
		));
	}

	/**
	 * Upraví text dané zprávy
	 * @param int $id id zprávy
	 * @param String $newText nový text zprávy
	 * @return Nette\Database\Table\Selection zpráva
	 */
	public function editTextMessage($id, $newText) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->update(array(
			self::COLUMN_TEXT => $newText
		));
		return $sel;
	}

	/**
	 * Odstraní (nenávratně) danou zprávu jakéhokoli typu
	 * @param int $id id (primární klíč) zprávy
	 */
	public function deleteMessage($id) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		$sel->delete();
	}

	/**
	 * Vrátí danou zprávu jakéhokoli typu
	 * @param int $id id zprávy
	 * @return Nette\Database\Table\Selection zpráva
	 */
	public function getMessage($id) {
		$sel = $this->getTable();
		return $sel->wherePrimary($id);
	}

	/**
	 * Vrátí zprávy z konverzace.
	 * @param int $conversationID Id konverzace.
	 * @return Nette\Database\Table\Selection zpráva
	 */
	public function getMessagesByConversation($conversationID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_CONVERSATION_ID, $conversationID);
		$sel->order("id DESC");
		return $sel;
	}

	/**
	 * Vrátí z konverzace zprávy, které jsou novější než zpráva s daným id.
	 * @param int $conversationID id konverzace
	 * @param int $lastId id poslední známé zprávy
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getNewMessagesFromConversation($conversationID, $lastId) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID . " > ?", $lastId); /* s přibývajícími zprávami je selektivnější filtrovat nejdřív nové */
		$sel->where(self::COLUMN_CONVERSATION_ID, $conversationID);
		return $sel;
	}

	/**
	 * Nastaví zprávu jako přečtenou/nepřečtenou
	 * Nekontroluje příjemce zpráv.
	 * @param int $id id zprávy
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravená zpráva
	 */
	public function setMessageReaded($id, $readed) {
		$sel = $this->getTable();
		$sel->wherePrimary($id);
		return $this->setSelectionReaded($sel, $readed);
	}

	/**
	 * Nastaví všechny zprávy jako přečtené/nepřečtené
	 * Nekontroluje příjemce zpráv.
	 * @param Nette\Database\Table\Selection $selection výběr prvků k úpravě
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setMessagesReaded($selection, $readed) {
		return $this->setSelectionReaded($selection, $readed);
	}

	/**
	 * Nastaví všechny zprávy mezi uživateli jako přečtené/nepřečtené
	 * @param int $idSender id odesílatele
	 * @param int $idRecipient id příjemce
	 * @param bool $readed přečtené/nepřečtené
	 * @return \Nette\Database\Table\Selection změněné řádky
	 */
	public function setAllMessagesReaded($idSender, $idRecipient, $readed) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_SENDER, $idSender);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		return $this->setSelectionReaded($sel, $readed);
	}

	/**
	 * Nastaví všechny zprávy s id v poli jako přečtené/nepřečtené
	 * @param array $ids neasociativni pole idček zpráv
	 * @param boolean $readed přečtená/nepřečtená
	 * @param int $idRecipient id příjemce kvůli bezpečnosti
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setMultipleMessagesReaded(array $ids, $idRecipient, $readed) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID, $ids);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		return $this->setSelectionReaded($sel, $readed);
	}

	/**
	 * Nastaví zprávu s daným id a všechny s nižším id jako přečtené/nepřečtené, pokud patří danému uživateli
	 * @param array $messageId id dotyčné zprávy
	 * @param boolean $idRecipient id příjemce
	 * @param int $readed  přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setAllOlderMessagesReaded($messageId, $idRecipient, $readed) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_ID . " <= ?", $messageId);
		return $this->setSelectionReaded($sel, $readed);
	}

	/**
	 * Nastaví všechny zprávy starší než dané id (včetně) od daného uživatele jako přečtené
	 * @param int $idFrom id uživatele, se kterým si píšu
	 * @param int $idRecipient id přihlášeného uživatele (pro jistotu)
	 * @param int $lastId id nejnovější přečtené zprávy
	 * @param int $readed  přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setOlderMessagesFromUserReaded($idFrom, $idRecipient, $lastId, $readed) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_SENDER, $idFrom);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_ID . " <= ?", $lastId);
		return $this->setSelectionReaded($sel, $readed);
	}

	/**
	 * Vrátí úplně všechny nepřečtené příchozí zprávy daného uživatele
	 * @param int $idRecipient id uživatele, kterému mají zprávy přijít
	 * @return Nette\Database\Table\Selection příchozí zprávy
	 */
	public function getAllUnreadedTextMessages($idRecipient) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_READED, self::MESSAGE_UNREADED); //casem bude vsech neprectenych mene, nez zprav jednoho uzivatele
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		return $sel;
	}

	/**
	 * Vrátí všechny zprávy novější než daná zpráva
	 * @param int $messageId id dané zprávy
	 * @param int $idRecipient příjemce zpráv
	 * @param array $extraMessages nepovinné pole obsahující id zpráv, které mají být obsaženy tak jako tak
	 * @return Nette\Database\Table\Selection  zprávy
	 */
	public function getAllNewerMessagesThan($messageId, $idRecipient, $extraMessages = array()) {
		if (!empty($extraMessages)) {
			$extraIds = implode(', ', $extraMessages);
			$extraSQL = ' OR ' . self::COLUMN_ID . " IN ($extraIds)";
		} else {
			$extraSQL = '';
		}
		$sel = $this->getTable();
		$sel->where('('
			. self::COLUMN_ID . ' > ? AND '
			. self::COLUMN_ID_RECIPIENT . ' IS NOT NULL AND '
			. self::COLUMN_ID_RECIPIENT . '= ? AND '
			. self::COLUMN_TYPE . '= ?) '
			. $extraSQL
			, $messageId, $idRecipient, self::TYPE_TEXT_MESSAGE);
		return $sel;
	}

	/**
	 * Vrátí všechny zprávy mezi dvěma uživateli novější než daná zpráva.
	 * @param int $lastMessageId id dané zprávy
	 * @param int $idUser1 odesílatel/příjemce zpráv
	 * @param int $idUser2 odesílatel/příjemce zpráv
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getAllNewerMessagesBetween($lastMessageId, $idUser1, $idUser2) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID . ' > ?', $lastMessageId);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);

		/* vybere zprávy mezi dvěma uživateli */
		$sel->where(self::COLUMN_ID_SENDER . ' = ? AND ' . self::COLUMN_ID_RECIPIENT . ' = ? OR ' .
			self::COLUMN_ID_RECIPIENT . ' = ? AND ' . self::COLUMN_ID_SENDER . ' = ? ', $idUser1, $idUser2, $idUser1, $idUser2);

		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);

		return $sel;
	}

	/**
	 * Vrátí určitý počet zpráv mezi dvěma uživateli starší než daná zpráva.
	 * @param int $lastMessageId id dané zprávy
	 * @param int $idUser1 odesílatel/příjemce zpráv
	 * @param int $idUser2 odesílatel/příjemce zpráv
	 * @param int $limit kolik maximálně zpráv bude vráceno
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getOlderMessagesBetween($lastMessageId, $idUser1, $idUser2, $limit) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID . ' < ?', $lastMessageId);
		/* vybere zprávy mezi dvěma uživateli */
		$sel->where(self::COLUMN_ID_SENDER . ' = ? AND ' . self::COLUMN_ID_RECIPIENT . ' = ? OR ' .
			self::COLUMN_ID_RECIPIENT . ' = ? AND ' . self::COLUMN_ID_SENDER . ' = ? ', $idUser1, $idUser2, $idUser1, $idUser2);

		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->limit($limit);
		return $sel;
	}

	/**
	 * Vrátí úplně všechny nepřečtené ODCHOZÍ zprávy daného uživatele
	 * @param int $idSender id uživatele, který zprávy poslal
	 * @return Nette\Database\Table\Selection odchozí zprávy
	 */
	public function getAllSendedAndUnreadedTextMessages($idSender) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_READED, self::MESSAGE_UNREADED); //casem bude vsech neprectenych mene, nez zprav jednoho uzivatele
		$sel->where(self::COLUMN_ID_SENDER, $idSender);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		return $sel;
	}

	/**
	 * Vrátí všechny nepřečtené zprávy daného uživatele, které přišly od daného uživatele
	 * @param int $idSender odesílatel zprávy
	 * @param int $idRecipient příjemce zprávy
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getAllNewMessagesBy($idSender, $idRecipient) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_READED, self::MESSAGE_UNREADED); //casem bude vsech neprectenych mene, nez zprav jednoho uzivatele
		$sel->where(self::COLUMN_ID_SENDER, $idSender);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		return $sel;
	}

	/**
	 * Vrátí poslední zprávy jednoho uživatele - ať už je poslal nebo přijal
	 * Lze nastavit offset (krok) OD KONCE
	 * @param int $idUser id uživatele
	 * @param int $amount počet zpráv
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getLastTextMessages($idUser, $amount = 10, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_SENDER . '=? OR ' . self::COLUMN_ID_RECIPIENT . '=?', $idUser, $idUser);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->limit($amount, $offset);
		return $sel;
	}

	/**
	 * Vrátí poslední zprávy mezi dvěma uživateli
	 * Lze nastavit offset (krok) OD KONCE
	 * @param int $idUser id uživatele
	 * @param int $idSecondUser id druhého uživatele
	 * @param int $amount počet zpráv
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getLastTextMessagesBetweenUsers($idUser, $idSecondUser, $amount = 10, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_SENDER . '=? AND ' . self::COLUMN_ID_RECIPIENT . '=?' .
			' OR ' . self::COLUMN_ID_RECIPIENT . '=? AND ' . self::COLUMN_ID_SENDER . '=?', $idUser, $idSecondUser, $idUser, $idSecondUser);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->limit($amount, $offset);
		return $sel;
	}

	/**
	 * Vrátí poslední zprávy, které poslal jeden uživatel druhému
	 * Lze nastavit offset (krok) OD KONCE
	 * @param int $idSender id odesílatele
	 * @param int $idRecipient id příjemce
	 * @param int $amount počet zpráv
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getLastTextMessagesBy($idSender, $idRecipient, $amount = 10, $offset = 0) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_SENDER, $idSender);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->limit($amount, $offset);
		return $sel;
	}

	/**
	 * Vrátí poslední zprávu konverzace každého (až do daného maxima) uživatele (pokud nějakou posílal)
	 * Nedávné zprávy jsou přednostní.
	 * @param int $idUser id koho se zprávy týkají
	 * @param int $limit maximální počet uživatelů
	 * @param int $offset maximální počet uživatelů
	 * @return \Nette\Database\ResultSet zprávy
	 */
	public function getLastMessageFromEachSender($idUser, $limit = 10, $offset = 0) {
		return $this->database->query('SELECT *
										FROM (SELECT * FROM chat_messages ORDER BY id DESC) AS a
										WHERE id_recipient IS NOT NULL
										WHERE id_recipient = ? OR id_sender = ?
										GROUP BY id_recipient, id_sender DESC
										LIMIT ' . $limit . ';', $idUser, $idUser);
	}

	/**
	 * Vrátí řádky s id odesílatele a id příjemce, které budou všechny unikátní a budou
	 * reprezentovat, kdy kdo komu psal (poslední komunikace bude na začátku)
	 * @param type $idUser kterého uživatele se zprávy týkají
	 * @param type $limit limit dotazu
	 * @param type $offset offset dotazu
	 * @return \Nette\Database\Table\Selection idčka
	 */
	public function getLastConversationMessagesIDs($idUser, $limit = 10, $offset = 0) {
		$sel = $this->getTable();
		$sel->select('DISTINCT ' . self::COLUMN_ID_SENDER . ', ' . self::COLUMN_ID_RECIPIENT);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT . '= ?' . ' OR ' . self::COLUMN_ID_SENDER . ' = ?', $idUser, $idUser);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->limit($limit, $offset);
		return $sel;
	}

	/**
	 * Vrátí všechny zprávy, které shlukne dohromady podle odesílatele, příjemce a toho, jestli již byly označeny
	 * spolu s jejich počtem.
	 * @return \Nette\Database\Table\Selection zprávy
	 */
	public function getAllCronGroupedMessages() {
		$sel = $this->getTable();
		$sel->select('*, count(id) AS cnt');
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->group(self::COLUMN_ID_SENDER . ',' . self::COLUMN_ID_RECIPIENT . ',' . self::COLUMN_CHECKED_BY_CRON);
		return $sel;
	}

	/**
	 * Označí zprávy jako prošlé cronem
	 * @param array $ids
	 * @return type
	 */
	public function markTheseAsChecked(array $ids) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID, $ids);
		$sel->update(array(
			self::COLUMN_CHECKED_BY_CRON => 1
		));
		return $sel;
	}

	/**
	 * Označí zprávy jako prošlé cronem
	 * @param int $senderID id odesílatele
	 * @param int $recipientID id příjemce
	 * @return \Nette\Database\Table\Selection updatnuté řádky
	 */
	public function markAsChecked($senderID, $recipientID) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID_SENDER, $senderID);
		$sel->where(self::COLUMN_ID_RECIPIENT . " IS NOT NULL");
		$sel->where(self::COLUMN_ID_RECIPIENT, $recipientID);
		$sel->update(array(
			self::COLUMN_CHECKED_BY_CRON => 1
		));
		return $sel;
	}

	/**
	 * Slouží pro nastavení hodnoty "přečteno" pro daný výběr z tabulky
	 * @param Nette\Database\Table\Selection $selection výběr z tabulky
	 * @param boolean $readed přečteno/nepřečteno
	 * @return Nette\Database\Table\Selection upravený výběr z tabulky
	 */
	private function setSelectionReaded($selection, $readed) {
		if ($readed) {
			$selection->update(array(
				self::COLUMN_READED => self::MESSAGE_READED
			));
		} else {
			$selection->update(array(
				self::COLUMN_READED => self::MESSAGE_UNREADED
			));
		}
		return $selection;
	}

	/**
	 * Vrátí nepřečtené zprávy o kterých ještě neodešel email s upozorněním.
	 * @param \Nette\Database\Table\Selection $usersForNewsletters Uživatelé, kteří by měli dostat informační email.
	 * @return Nepřečtené zprávy o kterých ještě neodešel email s upozorněním.
	 */
	public function getNotReadedNotSendNotify($usersForNewsletters) {
		$sel = $this->getTable();

		$sel->where(self::COLUMN_SEND_NOTIFY, 0);
		$sel->where(self::COLUMN_READED, 0);
		$sel->where(self::COLUMN_ID_RECIPIENT, $usersForNewsletters);

		return $sel;
	}

	/**
	 * Označí tyto zprávy jako zprávy s odeslaným oznámením emailem.
	 * @param \Nette\Database\Table\Selection $usersForNewsletters Uživatelé, kteří by měli dostat informační email.
	 */
	public function updateSendNotify($usersForNewsletters) {
		$messages = $this->getNotReadedNotSendNotify($usersForNewsletters);

		$messages->update(array(
			self::COLUMN_SEND_NOTIFY => 1
		));
	}

}
