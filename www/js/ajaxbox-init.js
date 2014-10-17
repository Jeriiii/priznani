

/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */

;
$(document).ready(function () {

	//OKÉNKO S KONVERZACEMI
	$.convRunAjax = true;
	$.convOffset = 0;
	$.convLimit = 5;

//inicializace vyskakovacích okének
	$('#conversations').ajaxBox({
		buttonSelector: '#messages-btn',
		topMargin: -10, //korekce y
		arrowOrientation: 'right', //šipka bude vpravo
		theme: "posAjaxBox posConversations", //použijí se implicitní styly, ale budou upraveny
		headerHtml: "Příchozí zprávy", //header
		loadUrl: loadConversationsLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
		dataArrived: function (opts, data) {//zkoumá, jestli snippet poslal nějaká data a pokud ne, schová načítací gif a pomocí globálního přepínače zastaví dotazování
			if (data.snippets['snippet-chat-conversationList-conversations'].trim() == "") {
				$.convRunAjax = false;
				$('#conversations').append('<div class="noConvMessages">Žádné další zprávy.</div>');
				$('div[data-related="' + opts.buttonSelector + '"] .loadingGif').css('display', 'none');
			}
		},
		reloadPermitted: function (opts) {//zastavení dotazování, pokud je globální přepínač false
			return $.convRunAjax;
		},
		dataToReload: function (opts) {//přidá ke každému požadavku ještě offset a limit. Poté zvětší offset.
			var offset = $.convOffset;
			$.convOffset += 5;
			return {
				'chat-conversationList-offset': offset,
				'chat-conversationList-limit': $.convLimit
			};
		},
		ajaxObserverId: 'chatConversationWindow', //je použit ajaxObserver a toto id reprezentuje požadavek této komponenty (pod stejným id se požadavek vyřizuje na serveru)
		observerResponseHandle: function (opts, data) {//zpracování odpovědi od ajaxObserveru, konkrétně čísla s počtem zpráv. Je-li nenulové, zobrazí se vedle tlačítka.
			if (data) {
				$(opts.buttonSelector).find('.ajaxbox-button-info').html(data).css('display', 'block');
			} else {
				$(opts.buttonSelector).find('.ajaxbox-button-info').css('display', 'none');
			}
		}
	});
/////////////////////////

});
