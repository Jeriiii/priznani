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
		initializeContactList();
		setWaitTime(opts.minRequestTimeout);
		refreshMessages(0);
	};

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
		idAttribute: 'data-id',
		/* atribut položky seznamu kontaktů, kde je její ID */
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
			console.log("CHAT - refreshing");
			sendRefreshRequest(waitTime);
		}, waitTime);

	}
	/* Posle na server ajaxovy pozadavek (dotaz) na nove zpravy
	 * @param waitTime aktualni hodnota casu, po ktery se cekalo na zavolani
	 * */
	function sendRefreshRequest(waitTime) {
		var opts = this.opts;
		$.getJSON(refreshMessagesLink, function(jsondata) {
			if ($.isEmptyObject(jsondata)) {
				waitTime = Math.min(waitTime + opts.timeoutStep, opts.maxRequestTimeout);
				console.log("CHAT - no new data - request timeout is now: " + waitTime);
			} else {
				handleResponse(jsondata);
				waitTime = opts.minRequestTimeout;
				console.log("CHAT - data arrived - request timeout is now: " + waitTime);
			}
			refreshMessages(waitTime);
		}).fail(function() {
			refreshMessages(opts.failResponseTimeout);
		});

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
			text: msg
		};
		sendDataByAjax(sendMessageLink, requestData);
		this.boxManager.addMsg(mydata.name, msg);//pridani zpravy do okna
		messageSent = true;
	}
	;
	/**
	 * Pomoci AJAXU konvertuje data do formatu JSON a posle je na danou adresu
	 * @param {String} url data, ktera se maji poslat
	 * @param {Object} data poslana data
	 */
	function sendDataByAjax(url, data) {
		var json = JSON.stringify(data);

		$.ajax({
			dataType: "json",
			type: 'POST',
			url: url,
			data: json,
			contentType: 'application/json; charset=utf-8',
			success: handleResponse,
			error: function() {

			}
		});

	}

	/**
	 * Zpracuje odpoved serveru. Priklad toho, jak maji vypadat data v jsonu, najdete v dokumentaci
	 * @param {Object} json data ze serveru, DEKODOVANY JSON
	 *
	 */
	function handleResponse(json) {
		$.each(json, function(key, values) {//projde vsechny uzivatele, od kterych neco prislo
			$.each(values, function(messageKey, message) {//vsechny zpravy od kazdeho uzivatele
				addMessage(key, message.name, message.text);
			});
		});
	}

	/**
	 * Inicializace seznamu kontaktu - poveseni click eventu etc.
	 */
	function initializeContactList() {
		console.log('CHAT - initializing contact list');

		var idList = new Array();

		chatboxManager.init({//chatbox manager pro spravu okenenek
			messageSent: sendMessage,
			width: this.opts.boxWidth,
			gap: this.opts.boxMargin,
			maxBoxes: this.opts.maxBoxes
		});

		var opts = this.opts;
		$(this.opts.contactListItems).click(function(event) {//click event na polozkach seznamu
			event.preventDefault();
			var id = $(this).attr(opts.idAttribute);
			idList.push(id);
			addBox(id, $(this).attr(opts.titleAttribute));
		});


	}

	/*
	 * Vytvori nove okno, nebo otevre stavajici. Pokud je pouze zavrene, otevre ho.
	 * @param {int|String} id id okna
	 * @param {String} title titulek okna
	 */
	function addBox(id, title) {
		chatboxManager.addBox(id,
				{
					title: title
				});
		console.log('CHAT - created new box #' + id);
	}


	/**
	 * Prida zpravu do okna s danym id a okno otevre
	 * @param {int|String} id id okna
	 * @param {String} name od koho zprava je
	 * @param {String} text text zpravy
	 */
	function addMessage(id, name, text) {
		addBox(id, name);//vytvori/zobrazi dotycne okno
		chatboxManager.addMessage(id, name, text);
	}



})(jQuery);


