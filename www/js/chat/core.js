/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 * @author Jan Kotalík
 */

;
(function($) {

	/* nastavení */
	var opts;

	/* detekce, zda byla poslana zprava*/
	var messageSent = false;


	/* main */
	$.fn.chat = function(options) {
		var opts = $.extend({}, $.fn.chat.defaults, options);
		setOpts(opts);
		initializeChatboxManager();
		initializeContactList();
		initializeConversationList();
		reloadWindowUnload();
		setWaitTime(opts.minRequestTimeout);
		refreshMessages(0);
	};

	/* proměnná pro poslední známé id zprávy */
	$.fn.chat.lastId = 0;

	/* objekt obsahující potvrzení o přečtení (předtím, než se odešlou) */
	$.fn.chat.readedQueue = new Array();


	$.fn.chat.defaults = {
		minRequestTimeout: 1000,
		/* minimální čekání mezi zasíláním požadavků. Této hodnoty dosáhne chat při aktivním používání */
		maxRequestTimeout: 8000,
		/* maximální čekání mezi zasíláním požadavků na nové zprávy. Této hodnoty postupně dosáhne neaktivní chat. */
		failResponseTimeout: 10000,
		/* pokud selže požadavek, toto je doba čekání, po které se to zkusí znovu */
		timeoutStep: 500,
		/* o kolik se zvýší čekání při přijetí prázdné odpovědi */
		contactListItems: '.contact-link',
		/* selektor pro položku (jméno) na seznamu kontaktů */
		conversationListItems: '.conversation-link',
		/* selektor pro položku (jméno) na seznamu konverzací */
		idAttribute: 'data-id',
		/* atribut položky seznamu kontaktů a konverzací, kde je ID kontaktu (cizího uživatele) */
		boxContainerSelector: '#contact-boxes',
		/* selector pro objekt, kde se budou vyvtaret okenka */
		titleAttribute: 'data-title',
		/* pod kterym atributem u polozky seznamu je text, ktery se ma zobrazit v titulku okenka*/
		boxWidth: 300,
		/* sirka okenka s chatem [px] */
		boxMargin: 30,
		/* mezera mezi okenky [px] */
		maxBoxes: 8
				/* maximalni pocet okenek na obrazovce */

	};

	/** pravidelne obnovuje stav prichozich zprav
	 * @param waitTime cas, ktery bude cekat pred refreshem
	 * */
	function refreshMessages(waitTime) {
		if (messageSent) {//pokud byla ted nekdy odeslana zprava
			waitTime = this.opts.minRequestTimeout;
			messageSent = false;
		}
		setTimeout(function() {
			//console.log("CHAT - refreshing");//pro debug
			sendRefreshRequest(waitTime);
		}, waitTime);

	}
	/* Posle na server ajaxovy pozadavek (dotaz) na nove zpravy
	 * Zaroven posila zpravy o precteni
	 * @param waitTime aktualni hodnota casu, po ktery se cekalo na zavolani
	 * */
	function sendRefreshRequest(waitTime) {
		var opts = this.opts;
		var data = {
			'chat-communicator-lastid': $.fn.chat.lastId,
			'chat-communicator-readedmessages': JSON.stringify($.fn.chat.readedQueue)
		};
		$.fn.chat.readedQueue = new Array();//vyprazdneni fronty


		if ($.fn.chat.lastId == 0) {
			sendFirstRefreshGet();
		} else {
			sendRefreshGet(data);
		}
	}


	/**
	 * Pošle požadavek s žádostí o refresh a upraví podle toho čas timeout.
	 * Počítá s tím, že je požadavek první a podle toho bude zacházet s odpovědí.
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
			refreshMessages(opts.failResponseTimeout);
		});
	}

	/**
	 * Pošle požadavek s žádostí o refresh a upraví podle toho čas timeout.
	 * Poté zajistí další zavolání refreshe.
	 * @param {type} data
	 * @returns {undefined}
	 */
	function sendRefreshGet(data) {
		var opts = this.opts;
		$.getJSON(refreshMessagesLink, data, function(jsondata) {
			if ($.isEmptyObject(jsondata)) {
				waitTime = Math.min(waitTime + opts.timeoutStep, opts.maxRequestTimeout);
				//console.log("CHAT - no new data - request timeout is now: " + waitTime);//pro debug
			} else {
				handleResponse(jsondata);
				waitTime = opts.minRequestTimeout;
				//console.log("CHAT - data arrived - request timeout is now: " + waitTime);//pro debug
			}
			refreshMessages(waitTime);
		}).fail(function() {
			refreshMessages(opts.failResponseTimeout);
		});
	}


	/**
	 * Nastavi zpravu jako prectenou (to se automaticky projevi po dalsim
	 * refreshRequestu i na serveru)
	 * @param {int} id
	 */
	function setReaded(id) {
		$.fn.chat.readedQueue.push(id);
	}
	/**
	 * Nastaveni options
	 * @param opts nastaveni k nastaveni
	 */
	function setOpts(opts) {
		this.opts = opts;
	}

	/**
	 * Nastaveni cas cekani na pocatecni hodnotu
	 * @param {int} time cas k nastaveni
	 */
	function setWaitTime(time) {
		waitTime = time;
	}

	/**
	 * Posila zpravu na server. Vola se automaticky pro odeslani zpravy v okenku
	 * @param {int|String} id
	 * @param {Object} data data souvisejici s okenkem
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
		this.boxManager.addMsg(mydata.name, msg);//pridani zpravy do okna
		messageSent = true;
	}
	;
	/**
	 * Pomoci AJAXU konvertuje data do formatu JSON a posle je na danou adresu
	 * @param {String} url data, ktera se maji poslat
	 * @param {Object} data poslana data
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
	 * Zpracuje odpoved serveru. Priklad toho, jak maji vypadat data v jsonu, najdete v dokumentaci
	 * @param {Object} json data ze serveru, DEKODOVANY JSON
	 *
	 */
	function handleResponse(json) {
		reloadWindowUnload();//odblokovani prevence proti predcasnemu opusteni stranky
		$.each(json, function(iduser, values) {//projde vsechny uzivatele, od kterych neco prislo
			var name = values.name;
			var messages = values.messages;
			$.each(messages, function(messageKey, message) {//vsechny zpravy od kazdeho uzivatele
				addMessage(iduser, name, message.name, message.id, message.text, message.type);
				if (message.id > $.fn.chat.lastId) {//aktualizace nejvyssiho id
					$.fn.chat.lastId = message.id;
				}
			});
		});
	}

	/**
	 * Inicializace spravce okenek
	 */
	function initializeChatboxManager() {
		chatboxManager.init({//chatbox manager pro spravu okenenek
			messageSent: sendMessage,
			width: this.opts.boxWidth,
			gap: this.opts.boxMargin,
			maxBoxes: this.opts.maxBoxes
		});
	}

	/**
	 * Inicializace seznamu kontaktu - poveseni click eventu etc.
	 */
	function initializeContactList() {
		//console.log('CHAT - initializing contact list');//pro debug
		var opts = this.opts;
		$(this.opts.contactListItems).click(function(event) {//click event na polozkach seznamu
			event.preventDefault();
			var id = $(this).attr(opts.idAttribute);
			addBox(id, $(this).attr(opts.titleAttribute));
		});


	}

	/**
	 * Inicializace seznamu konverzaci
	 */
	function initializeConversationList() {
		var opts = this.opts;
		$(this.opts.conversationListItems).click(function(event) {//click event na polozkach seznamu
			event.preventDefault();
			var id = $(this).attr(opts.idAttribute);
			addBox(id, $(this).attr(opts.titleAttribute));
		});
	}



	/**
	 * Nacte ajaxem poslednich nekolik zprav do boxu
	 * @param id id okenka (a uzivatele)
	 */
	function loadMessagesIntoBox(id) {
		var data = {
			'chat-communicator-fromId': id
		};
		var opts = this.opts;
		blockWindowUnload('Ještě se načítají zprávy, opravdu chcete odejít?');
		$.getJSON(loadMessagesLink, data, function(jsondata) {
			handleResponse(jsondata);
		});
	}

	/*
	 * Vytvori nove okno, nebo otevre stavajici. Pokud je pouze zavrene, otevre ho.
	 * @param {int|String} id id okna
	 * @param {String} title titulek okna
	 */
	function addBox(id, title) {
		var wascreated = chatboxManager.addBox(id,
				{
					title: title
				});
		if (wascreated) {//pokud je box novy
			loadMessagesIntoBox(id);
		}
		//console.log('CHAT - created new box #' + id);//pro debug
	}


	/**
	 * Prida zpravu do okna s danym id a okno otevre
	 * @param {int|String} id id okna
	 * @param {String} boxname s kym si pisu (titulek okna)
	 * @param {String} name od koho zprava je
	 * @param {int} messid id zpravy
	 * @param {String} text text zpravy
	 * @param {int} type typ zpravy
	 */
	function addMessage(id, boxname, name, messid, text, type) {
		addBox(id, boxname);//vytvori/zobrazi dotycne okno
		if (type == 0) {
			chatboxManager.addMessage(id, name, text);
			setReaded(messid);
		} else {
			chatboxManager.addMessage(id, '', text);
		}
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
				return 'Máte rozepsaný příspěvek. Chcete tuto stránku opustit a zahodit tak svou práci?';
				/* hláška, co se objeví při pokusu obnovit/zavřít okno, zatímco má uživatel rozepsanou zprávu */
			}
		};
	}



})(jQuery);


