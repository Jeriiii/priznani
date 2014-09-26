/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

/**
 * Jádro celého chatu - klientská část. Tento plugin zajišťuje klientskou funkci chatu
 */
;
(function($) {

	/* nastavení */
	var chatopts;
	/* detekce, zda byla poslana zprava*/
	var messageSent = false;
	/* main */
	$.fn.chat = function(options) {
		var chatopts = $.extend({}, $.fn.chat.defaults, options);
		setChatOpts(chatopts);
		initializeChatboxManager();
		initializeContactList();
		initializeConversationList();
		reloadWindowUnload();
		setWaitTime(chatopts.minRequestTimeout);
		refreshMessages(0);
		observer.register('chat', function(data) {
			alert('it works!' + data);
		});
	};
	/* proměnná pro poslední známé id zprávy */
	$.fn.chat.lastId = 0;
	/* objekt obsahující potvrzení o přečtení (předtím, než se odešlou) */
	$.fn.chat.readedQueue = new Array();
	/* implicitní hodnoty nastavení pluginu */
	$.fn.chat.defaults = {
		/* minimální čekání mezi zasíláním požadavků. Této hodnoty dosáhne chat při aktivním používání */
		minRequestTimeout: 1000,
		/* maximální čekání mezi zasíláním požadavků na nové zprávy. Této hodnoty postupně dosáhne neaktivní chat. */
		maxRequestTimeout: 8000,
		/* pokud selže požadavek, toto je doba čekání, po které se to zkusí znovu */
		failResponseTimeout: 10000,
		/* o kolik se zvýší čekání při přijetí prázdné odpovědi */
		timeoutStep: 500,
		/* selektor pro položku (jméno) na seznamu kontaktů */
		contactListItems: '.contact-link',
		/* selektor pro položku (jméno) na seznamu konverzací */
		conversationListItems: '.conversation-link',
		/* atribut položky seznamu kontaktů a konverzací, kde je ID kontaktu (cizího uživatele) */
		idAttribute: 'data-id',
		/* selector pro objekt, kde se budou vyvtaret okenka */
		boxContainerSelector: '#contact-boxes',
		/* pod kterym atributem u polozky seznamu je text, ktery se ma zobrazit v titulku okenka*/
		titleAttribute: 'data-title',
		/* sirka okenka s chatem [px] */
		boxWidth: 250,
		/* mezera mezi okenky [px] */
		boxMargin: 30,
		/* maximalni pocet okenek na obrazovce */
		maxBoxes: 8,
		/* odsazeni od prave casti stranky [px] */
		rightMargin: 260

	};


	/**
	 * Pravidelně obnovuje stav příchozích zpráv
	 * @param waitTime cas, ktery bude cekat pred refreshem
	 * */
	function refreshMessages(waitTime) {
		if (messageSent) {//pokud byla ted nekdy odeslana zprava
			waitTime = this.chatopts.minRequestTimeout;
			messageSent = false;
		}
		setTimeout(function() {
			//console.log("CHAT - refreshing");//pro debug
			sendRefreshRequest(waitTime);
		}, waitTime);
	}
	/**
	 * Pošle na server ajaxový požadavek (dotaz) na nové zprávy
	 * Zaroven posílá informace o tom, které zprávy si uživatel přečetl
	 * a tudíž se mají označit za přečtené.
	 * @param waitTime aktualní hodnota času, po který se čekalo na zavolání
	 * */
	function sendRefreshRequest(waitTime) {
		var chatopts = this.chatopts;
		var data = {
			'chat-communicator-lastid': $.fn.chat.lastId,
			'chat-communicator-readedmessages': JSON.stringify($.fn.chat.readedQueue)
		};
		$.fn.chat.readedQueue = new Array(); //vyprazdneni fronty


		if ($.fn.chat.lastId == 0) {//pokud posledni id zpravy neexistuje (ještě žádné zprávy nejsou k dispozici)
			sendFirstRefreshGet(); //zjistí id poslední relevantní zprávy
		} else {
			sendRefreshGet(data);
		}
	}


	/**
	 * Pošle požadavek s žádostí o refresh a upraví podle toho čas timeout.
	 * Počítá s tím, že je požadavek první a slouží pro prvotní zjištění ID
	 * poslední zprávy. Zprávy z odpovědi jako takové zahazuje a použije pouze id.
	 * Nastaví id poslední zprávy. Pokud nic nepřijde, nastaví co nejmenší ID,
	 * které dává smysl.
	 * Poté zajistí další zavolání refreshe.
	 */
	function sendFirstRefreshGet() {
		$.getJSON(refreshMessagesLink, function(jsondata) {
			if ($.isEmptyObject(jsondata)) {//cerstvy uzivatel, vubec zadne zpravy mu neprisly
				$.fn.chat.lastId = 1; //bude to z DB brat uplne od zacatku
			} else {
				$.each(jsondata, function(iduser, values) {//projde "všechny", ale měl by být jeden
					var messages = values.messages;
					$.each(messages, function(messageKey, message) {
						if (message.id > $.fn.chat.lastId) {//aktualizace nejvyssiho id
							$.fn.chat.lastId = message.id;
						}//zprava jako takova je zahozena
					});
				});
			}
			refreshMessages(waitTime);
		}).fail(function() {
			refreshMessages(chatopts.failResponseTimeout);
		});
	}

	/**
	 * Pošle požadavek s žádostí o refresh a upraví podle toho čas timeout.
	 * Poté zajistí další zavolání refreshe. Příchozí data jsou standardně
	 * zpracována pomocí funkce handleResponse.
	 * @param {Object} data k odeslání
	 */
	function sendRefreshGet(data) {
		var chatopts = this.chatopts;
		$.getJSON(refreshMessagesLink, data, function(jsondata) {
			if ($.isEmptyObject(jsondata)) {
				waitTime = Math.min(waitTime + chatopts.timeoutStep, chatopts.maxRequestTimeout);
				//console.log("CHAT - no new data - request timeout is now: " + waitTime);//pro debug
			} else {
				handleResponse(jsondata);
				waitTime = chatopts.minRequestTimeout;
				//console.log("CHAT - data arrived - request timeout is now: " + waitTime);//pro debug
			}
			refreshMessages(waitTime);
		}).fail(function() {
			refreshMessages(chatopts.failResponseTimeout);
		});
	}


	/**
	 * Nastaví zprávu jako přečtenou (to se automaticky projeví po dalším
	 * refreshRequestu i na serveru)
	 * @param {int} id zprávy
	 */
	function setReaded(id) {
		$.fn.chat.readedQueue.push(id);
	}
	/**
	 * Nastavení options
	 * @param chatopts nastaveni k nastaveni
	 */
	function setChatOpts(chatopts) {
		this.chatopts = chatopts;
	}

	/**
	 * Nastavení času čekání na počáteční hodnotu
	 * @param {int} time čas k nastavení
	 */
	function setWaitTime(time) {
		waitTime = time;
	}

	/**
	 * Posílá zpravu na server. Volá se automaticky pro odeslaní zprávy v okénku chatboxu
	 * jako callback k odeslání zprávy (pomocí enteru etc).
	 * @param {int|String} id
	 * @param {Object} data data související s okénkem z chatbox.js - například titulek okna apod.
	 * předává je chatboxManager
	 * @param {String} msg
	 */
	function sendMessage(id, data, msg) {
		var requestData = {
			to: id,
			type: 'textMessage',
			text: msg,
			lastid: $.fn.chat.lastId
		};
		blockWindowUnload('Zpráva se stále odesílá, prosíme počkejte několik sekund a pak to zkuste znova.');
		/* hláška, co se objeví při pokusu obnovit/zavřít okno, zatímco se čeká na odpověď při odeslání zprávy */
		sendDataByPost(sendMessageLink, requestData);
		clearInfoMessages(id);
		this.boxManager.addMsg(mydata.name, msg); //pridani zpravy do okna
		messageSent = true;
		actualizeMessageInConversationList(id, data.title, msg);
	}

	/**
	 * Pomocí AJAXU konvertuje data do formátu JSON a pošle je na danou adresu
	 * @param {String} url data, která se mají poslat
	 * @param {Object} data poslaná data
	 */
	function sendDataByPost(url, data) {
		var json = JSON.stringify(data);
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: url,
			data: json,
			contentType: 'application/json; charset=utf-8',
			success: handleResponse,
			error: function() {
				reloadWindowUnload();
			}
		});
	}

	/**
	 * Zpracuje odpověď serveru. Příklad toho, jak mají vypadat data v jsonu, najdete v dokumentaci
	 * @param {Object} json data ze serveru, DEKODOVANY JSON
	 *
	 */
	function handleResponse(json) {
		reloadWindowUnload(); //odblokovani prevence proti predcasnemu opusteni stranky
		$.each(json, function(iduser, values) {//projde vsechny uzivatele, od kterych neco prislo
			var name = values.name;
			var messages = values.messages;
			$.each(messages, function(messageKey, message) {//vsechny zpravy od kazdeho uzivatele
				addMessage(iduser, name, message.name, message.id, message.text, message.type);
				if (message.type == 0) {//textové zprávy se aktualizují v seznamu konverzací
					actualizeMessageInConversationList(iduser, name, message.text);
				}
				if (message.id > $.fn.chat.lastId) {//aktualizace nejvyssiho id
					$.fn.chat.lastId = message.id;
				}
			});
		});
	}

	/**
	 * Inicializace správce okének
	 */
	function initializeChatboxManager() {
		chatboxManager.init({//chatbox manager pro spravu okenenek
			messageSent: sendMessage,
			width: this.chatopts.boxWidth,
			gap: this.chatopts.boxMargin,
			maxBoxes: this.chatopts.maxBoxes,
			offset: this.chatopts.rightMargin
		});
	}

	/**
	 * Inicializace seznamu kontaktů - pověšení click eventů etc.
	 */
	function initializeContactList() {
		//console.log('CHAT - initializing contact list');//pro debug
		var chatopts = this.chatopts;
		$(this.chatopts.contactListItems).click(function(event) {//click event na polozkach seznamu
			event.preventDefault();
			var id = $(this).attr(chatopts.idAttribute);
			addBox(id, $(this).attr(chatopts.titleAttribute));
		});
	}

	/**
	 * Inicializace seznamu konverzací
	 */
	function initializeConversationList() {
		var chatopts = this.chatopts;
		$(this.chatopts.conversationListItems).click(function(event) {//click event na polozkach seznamu
			event.preventDefault();
			var id = $(this).attr(chatopts.idAttribute);
			addBox(id, $(this).attr(chatopts.titleAttribute));
		});
	}


	/**
	 * Aktualizuje seznam konverzací danou zprávou
	 * @param {int} id uživatele, se kterým si píšu (resp. id konverzace)
	 * @param {String} name jméno uživatele, se kterým si píšu
	 * @param {String} text text zprávy, kterou posílám
	 */
	function actualizeMessageInConversationList(id, name, text) {
		var listItem = $('#conversations li[data-id="' + id + '"]');
		if (!(listItem.length > 0)) {//pokud v konverzacich neni zaznam
			var conList = $('#conversations ul');
			var newListItem = '<li data-id="' + id + '" data-title="' + name + '" class="conversation-link">\n\
			<strong>' + name + '</strong><p class="lastmessage">' + text + '</p></li>';
			conList.prepend(newListItem); //prida se do seznamu
		} else {
			listItem.removeClass('unreaded'); //aktualizuje se po zobrazeni zpravy
			listItem.find('.lastmessage').text(text);
		}

	}



	/**
	 * Načte ajaxem posledních několik zprav do okénka
	 * @param id id okénka (a užvatele)
	 */
	function loadMessagesIntoBox(id) {
		var data = {
			'chat-communicator-fromId': id
		};
		var chatopts = this.chatopts;
		blockWindowUnload('Ještě se načítají zprávy, opravdu chcete odejít?');
		$.getJSON(loadMessagesLink, data, function(jsondata) {
			handleResponse(jsondata);
		});
	}

	/**
	 * Vytvoří nové okno, nebo otevře stávající.
	 * @param {int|String} id id okna
	 * @param {String} title titulek okna
	 * @return {bool} byl vytvoren nove a naplnen poslednimi zpravami
	 */
	function addBox(id, title) {
		var wascreated = chatboxManager.addBox(id,
				{
					title: title
				});
		if (wascreated) {//pokud je box novy
			loadMessagesIntoBox(id);
		}
		return wascreated;
		//console.log('CHAT - created new box #' + id);//pro debug
	}


	/**
	 * Přidá zprávu do okna s daným id a okno otevře
	 * @param {int|String} id id okna
	 * @param {String} boxname s kym si pisu (titulek okna)
	 * @param {String} name od koho zprava je
	 * @param {int} messid id zpravy
	 * @param {String} text text zpravy
	 * @param {int} type typ zpravy
	 */
	function addMessage(id, boxname, name, messid, text, type) {
		var newbox = addBox(id, boxname); //vytvori/zobrazi dotycne okno
		clearInfoMessages(id);
		if (type == 0) {//textova zprava
			if (!newbox) {//pokud je vytvoren box nove, bude zpravami naplnen automaticky vcetne teto posledni
				chatboxManager.addMessage(id, name, text);
			}
			setReaded(messid);
		} else {//infozprava
			chatboxManager.addMessage(id, '', text);
		}
	}

	/**
	 * V daném okně skryje všechny informační zprávy (ty, co nemají odesílatele)
	 * @param {int} id okna
	 */
	function clearInfoMessages(id) {
		$('#' + id + ' .ui-chatbox-nopeer').css('display', 'none');
	}

	/**
	 * Při pokusu zavřít nebo obnovit okno se zeptá uživatele,
	 * zda chce okno skutečně zavřít/obnovit. Toto dělá v každém případě, dokud
	 * se nezavolá reloadWindowUnload
	 * @param {String} reason důvod uvedený v dialogu
	 */
	function blockWindowUnload(reason) {
		window.onbeforeunload = function() {
			return reason;
		};
	}

	/**
	 * Vypne hlídání zavření/obnovení okna a vrátí jej do počátečního stavu.
	 */
	function reloadWindowUnload() {
		window.onbeforeunload = function() {
			var unsend = false;
			$.each($(".ui-chatbox-input-box"), function() {//projde vsechny textarea chatu
				if ($.trim($(this).val())) {//u kazdeho zkouma hodnotu bez whitespacu
					unsend = true;
				}
			});
			if (unsend) {
				return 'Máte rozepsaný příspěvek. Chcete tuto stránku přesto opustit?';
				/* hláška, co se objeví při pokusu obnovit/zavřít okno, zatímco má uživatel rozepsanou zprávu */
			}
		};
	}



})(jQuery);


