/*
 * @author Jan Kotalík
 */

;
(function($) {

	/* nastavení */
	var opts;


	var openBoxes = {};


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
		/* maximální čekání mezi zasíláním požadavků na nové zprávy. Této hodnoty postupně dosáhne neaktivní uživatel */
		timeoutStep: 100,
		/* o kolik se zvýší čekání při přijetí prázdné odpovědi */
		contactListItems: '.contact-link',
		/* selektor pro položku (jméno) na seznamu kontaktů */
		idAttribute: 'data-id',
		/* atribut položky seznamu kontaktů, kde je její ID */
		boxSelectorPrefix: '#contact-box-'
				/* prefix selektoru pro jednotliva okenka - na konec bude pridano id kontaktu */

	};


	function setOpts(opts) {
		this.opts = opts;
	}

	/**
	 * Inicializace seznamu kontaktu - poveseni click eventu etc.
	 */
	function initializeContactList() {
		console.log('CHAT - initializing contact list');

		var opts = this.opts;
		$(this.opts.contactListItems).click(function(e) {
			e.preventDefault();
			var id = $(this).attr(opts.idAttribute);
			toggleChatbox(id, opts);
		});

	}

	/*
	 * Otevre, zavre nebo vytvori a otevre okno chatu
	 * Na jeden kontakt muze byt max jedno okno
	 * @param int id id kontaktu
	 * @param Object opts to same jako this.opts, globalne to ale neni videt kvuli .click
	 *
	 */
	function toggleChatbox(id, opts) {
		if (typeof openBoxes[id] == 'undefined') {//neexistuje index = okenko
			var boxSelector = opts.boxSelectorPrefix + "" + id;
			openBoxes[id] = $(boxSelector).chatbox({id: id,
				user: {key: "value"},
				title: "test chat",
				messageSent: function(id, user, msg) {
					$("#log").append(id + " said: " + msg + "<br/>");
					$(boxSelector).chatbox("option", "boxManager").addMsg(id, msg);
				}});
			console.log('CHAT - created new box #' + id + openBoxes[id].toString());
		} else {//pokud uz existuje
			openBoxes[id].chatbox("option", "boxManager").toggleBox();
			console.log('CHAT - toggled box #' + id);
		}

	}

})(jQuery);


