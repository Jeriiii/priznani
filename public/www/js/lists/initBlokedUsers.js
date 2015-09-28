/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/* vyskakovací okénko u žádostí o přátelství */
if (typeof loadBlokedUserLink !== 'undefined') { //zkontroluje, zda je proměnná loadBlokedUserLink definovaná
	$('#blokedUsers').ajaxBox({ //inicializuje se nad daty, která mají být v okénku
		buttonSelector: '#blokedUsersBtn',
		theme: "posPopUp", //použijí se implicitní styly, ale budou upraveny
		autoPosition: 'center',
		hideOthers: true,
		headerHtml: "Blokovaní uživatelé", //header
		loadUrl: loadBlokedUserLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
		streamSnippetModule: {
			snippetName: 'snippet-blokedUsers-blokedUsersList',
			endMessage: 'Žádní další uživatelé',
			offsetParameter: 'blokedUsers-offset',
			limitParameter: 'blokedUsers-limit',
			addLimit: 5,
			startOffset: 0
		}
	});
}