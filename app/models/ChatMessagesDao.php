<?php

/*
 * @copyright Copyright (c) 2014 Kukral COMPANY s.r.o.
 */

namespace POS\Model;

/**
 * NAME DAO NAMEDao
 * slouží k
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
	 * Nastaví zprávu jako přečtenou/nepřečtenou
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
	 * @param Nette\Database\Table\Selection $selection výběr prvků k úpravě
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setMessagesReaded($selection, $readed) {
		return $this->setSelectionReaded($selection, $readed);
	}

	/**
	 * Nastaví všechny zprávy s id v poli jako přečtené/nepřečtené
	 * @param array $ids neasociativni pole idček
	 * @param boolean $readed přečtená/nepřečtená
	 * @return Nette\Database\Table\Selection upravené zprávy
	 */
	public function setMultipleMessagesReaded(array $ids, $readed) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID, $ids);
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
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		return $sel;
	}

	/**
	 * Vrátí všechny zprávy novější než daná zpráva
	 * @param int $messageId id dané zprávy
	 * @param int $idRecipient příjemce zpráv
	 * @return Nette\Database\Table\Selection  zprávy
	 */
	public function getAllNewerMessagesThan($messageId, $idRecipient) {
		$sel = $this->getTable();
		$sel->where(self::COLUMN_ID . ' > ?', $messageId);
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
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
		$sel->where(self::COLUMN_ID_SENDER . '=? OR ' . self::COLUMN_ID_RECIPIENT . '=?', $idUser, $idUser);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
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
		$sel->where(self::COLUMN_ID_SENDER . '=? AND ' . self::COLUMN_ID_RECIPIENT . '=?' .
			' OR ' . self::COLUMN_ID_RECIPIENT . '=? AND ' . self::COLUMN_ID_SENDER . '=?', $idUser, $idSecondUser, $idUser, $idSecondUser);
		$sel->order(self::COLUMN_ID . ' DESC');
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
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
		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
		$sel->where(self::COLUMN_ID_SENDER, $idSender);
		$sel->where(self::COLUMN_ID_RECIPIENT, $idRecipient);
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
	 * @return Nette\Database\Table\Selection zprávy
	 */
	public function getLastMessageFromEachSender($idUser, $limit = 10, $offset = 0) {
//				$sel = $this->getTable();
//		$sel->select('*, max(' . self::COLUMN_ID . ') AS mxid'); //je treba radit podle maximalniho id, jinak pak ten limit nerespektuje ten order
//		$sel->where(self::COLUMN_ID_RECIPIENT . ' = ? OR ' . self::COLUMN_ID_SENDER . ' = ?', $idUser, 0);
//		//to OR id_sender = 0 tam musi byt, aby spravne fungoval GROUP BY id?sender DESC
//		$sel->where(self::COLUMN_TYPE, self::TYPE_TEXT_MESSAGE);
//		$sel->group(self::COLUMN_ID_SENDER . ' DESC');
//		$sel->order('mxid DESC');
//		$sel->limit($limit, $offset);
		return $this->database->query('SELECT *
FROM (SELECT * FROM chat_messages ORDER BY id DESC) AS a
WHERE id_recipient = ? OR id_sender = ?
GROUP BY id_recipient, id_sender DESC
LIMIT 4;', $idUser, $idUser);
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

}
