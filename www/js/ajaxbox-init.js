

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
	 * Zpracuje číselnou odpověď ajaxObserveru a nastaví ji ke tlačítku
	 * @param {type} opts nastavení dotyčného okénka
	 * @param {type} data příchozí data od serveru
	 */
	function handleNumberResponse(opts, data) {//zpracování odpovědi od ajaxObserveru, konkrétně čísla s počtem zpráv. Je-li nenulové, zobrazí se vedle tlačítka.
		if (data) {
			$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
		} else {
			$(opts.buttonSelector).find('.ajaxbox-button-info').css('display', 'none');
		}
	}

//inicializace vyskakovacích okének
	$('#conversations').ajaxBox({
		buttonSelector: '#messages-btn',
		topMargin: -10, //korekce y
		arrowOrientation: 'right', //šipka bude vpravo
		theme: "posAjaxBox posConversations", //použijí se implicitní styly, ale budou upraveny
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
		observerResponseHandle: handleNumberResponse//použití funkce v initu, která vezme výsledek dotazu na observer a zobrazí jej u tlačítka, pokud je nenulový
	});
/////////////////////////

});
