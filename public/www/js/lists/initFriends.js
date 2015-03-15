/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/* vyskakovací okénko u žádostí o přátelství */
$('#friends').ajaxBox({ //inicializuje se nad daty, která mají být v okénku
	buttonSelector: '#friendsBtn',
	theme: "posPopUp", //použijí se implicitní styly, ale budou upraveny
	autoPosition: 'center',
	hideOthers: true,
	headerHtml: "Seznam přátel", //header
	loadUrl: loadFriendLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
	streamSnippetModule: {
		snippetName: 'snippet-friends-friends',
		endMessage: 'Žádní další přátelé',
		offsetParameter: 'friends-offset',
		limitParameter: 'friends-limit',
		addLimit: 5,
		startOffset: 0
	}
});