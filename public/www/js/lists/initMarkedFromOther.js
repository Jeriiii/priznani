/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */
/* základní nastavení vyskakovacího okénka */
var baseSettings = {//inicializuje se nad daty, která mají být v okénku
	buttonSelector: '#markedFromOtherBtn',
	theme: "posPopUp", //použijí se implicitní styly, ale budou upraveny
	autoPosition: 'center',
	hideOthers: true,
	headerHtml: "Jsem pro ně sexy" //header
};
var ajaxSettings = {};

if (typeof loadFriendLink !== 'loadMarkedFromOtherLink') { //zkontroluje, zda je proměnná loadFriendLink definovaná
	if (typeof isUserPaying !== 'undefined' && isUserPaying) {
		/* má li uživatel placený účet, zobrazí se mu seznam uživatelů, kteří ho označili že je sexi */
		/* vyskakovací okénko pro to, kolik lidí mě označilo jako sexi */
		var ajaxSettings = {
			loadUrl: loadMarkedFromOtherLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
			streamSnippetModule: {
				snippetName: 'snippet-markedFromOther-sexyList',
				endMessage: 'Žádné další označení.',
				offsetParameter: 'markedFromOther-offset',
				limitParameter: 'markedFromOther-limit',
				addLimit: 5,
				startOffset: 0
			}
		};
	}

	var settings = $.extend({}, baseSettings, ajaxSettings);


	$('#markedFromOther').ajaxBox(settings);
}

