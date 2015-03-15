

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

;
$(document).ready(function () {

	//OKÉNKO S KONVERZACEMI
	$.convRunAjax = true;
	$.convOffset = 0;
	$.convLimit = 5;

	/**
	 * Zpracuje číselnou odpověď ajaxObserveru a nastaví ji ke tlačítku.
	 * @param {Object} opts nastavení dotyčného okénka
	 * @param {Object} data příchozí data od serveru
	 */
	function handleNumberResponse(opts, data) {//zpracování odpovědi od ajaxObserveru, konkrétně čísla s počtem zpráv. Je-li nenulové, zobrazí se vedle tlačítka.
		if (data) {
			$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
		} else {
			$(opts.buttonSelector).find('.ajaxbox-button-info').css('display', 'none');
		}
	}
	
	/**
	 * Zpracuje číselnou odpověď ajaxObserveru a nastaví ji ke tlačítku. Pokud u tlačítka již bylo takové číslo,
	 * obnoví celé okénko a tím způsobí aktualizaci jeho dat.
	 * @param {Object} opts nastavení dotyčného okénka
	 * @param {Object} data příchozí data od serveru
	 */
	function handleRefreshResponse(opts, data) {//zpracování odpovědi od ajaxObserveru, konkrétně čísla s počtem zpráv. Je-li nenulové, zobrazí se vedle tlačítka.
		var infoElement = $(opts.buttonSelector).find('.ajaxbox-button-info');
		if (data) {
			var dataNumber = parseInt(data);
			var currentNumber = parseInt(infoElement.html());
			if(dataNumber != currentNumber){
				$(opts.buttonSelector).trigger('reloadRequest');/* reload okénka */
			}
			infoElement.html(data).css('display', 'block');
		} else {
			infoElement.css('display', 'none');
		}
	}

//inicializace vyskakovacích okének
	$('#conversations').ajaxBox({
		buttonSelector: '#messages-btn',
		topMargin: -10, //korekce y
		arrowOrientation: 'right', //šipka bude vpravo
		theme: "posAjaxBox posConversations interface", //použijí se implicitní styly, ale budou upraveny
		headerHtml: "Příchozí zprávy", //header
		loadUrl: loadConversationsLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
		streamSnippetModule: {
			snippetName: 'snippet-chat-conversationList-conversations',
			endMessage: 'Žádné další zprávy.',
			offsetParameter: 'chat-conversationList-offset',
			limitParameter: 'chat-conversationList-limit',
			addLimit: 5,
			startOffset: 0
		},
		ajaxObserverId: 'chatConversationWindow', //je použit ajaxObserver a toto id reprezentuje požadavek této komponenty (pod stejným id se požadavek vyřizuje na serveru)
		observerResponseHandle: handleRefreshResponse//použití funkce v initu, která vezme výsledek dotazu na observer a zobrazí jej u tlačítka, pokud je nenulový
	});
/////////////////////////
	$('#snippet-payment-payment').ajaxBox({
		buttonSelector: '.payment-btn',
		theme: "posPopUp",
		autoPosition: 'center',
		hideOthers: true,
		headerHtml: 'Výběr účtu'
	});

	/* okénko s přihlašovacím/odhlašovacím menu */
	$('#dropmenu').ajaxBox({
		buttonSelector: '#droplink',
		theme: "posAjaxBox ajaxBoxMenu interface"
	});
});
