/*
 * @author Jan Kotalík
 */

;
(function($) {

	/* nastavení */
	var opts;


	/* main */
	$.fn.chat = function(options) {
		var opts = $.extend({}, $.fn.chat.defaults, options);
		setOpts(opts);
		initializeContactList();
	};

	$.fn.chat.defaults = {
		minRequestTimeout: 1000,
		/* minimální čekání mezi zasíláním požadavků. Této hodnoty dosáhne chat při aktivním používání */
		maxRequestTimeout: 8000,
		/* maximální čekání mezi zasíláním požadavků na nové zprávy. Této hodnoty postupně dosáhne neaktivní chat. */
		timeoutStep: 100,
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

	/**
	 * Nastaveni options
	 * @param opts nastaveni k nastaveni
	 */
	function setOpts(opts) {
		this.opts = opts;
	}

	/**
	 * Posila zpravu na server. Vola se automaticky pro odeslani zpravy v okenku
	 * @param {type} id
	 * @param {type} data data souvisejici s okenkem
	 * @param {type} msg
	 * @returns {undefined}
	 */
	function sendMessage(id, data, msg) {
		var requestData = {
			id: id,
			type: 'textMessage',
			text: msg
		};
		sendDataByAjax(sendMessageLink, requestData);
		this.boxManager.addMsg(mydata.name, msg);//pridani zpravy do okna
	}
	;
	/**
	 * Pomoci AJAXU konvertuje data do formatu JSON a posle je na danou adresu
	 * @param Object data
	 */
	function sendDataByAjax(url, data) {
		var json = JSON.stringify(data);

		$.ajax({
			dataType: "json",
			type: 'POST',
			url: url,
			data: json,
			contentType: 'application/json; charset=utf-8',
			success: handleResponse
		});

	}

	/**
	 * Zpracuje odpoved serveru
	 * @param Object data data ze serveru
	 * @param Object status status odpovedi ze serveru
	 * @param Object jqxhr jqXHR (in jQuery 1.4.x, XMLHttpRequest) object viz dokumentace jQuery.ajax()
	 */
	function handleResponse(data, status, jqxhr) {
		alert(JSON.parse(data));
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
			chatboxManager.addBox(id,
					{
						title: $(this).attr(opts.titleAttribute)
					});
		});
		console.log('CHAT - created new box #' + id);

	}



})(jQuery);


