/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/* vyskakovací okénko u žádostí o přátelství */
$('#friendRequests').ajaxBox({ //inicializuje se nad daty, která mají být v okénku
	buttonSelector: '#friendRequestsBtn',
	theme: "posPopUp", //použijí se implicitní styly, ale budou upraveny
	autoPosition: 'center',
	hideOthers: true,
	headerHtml: "Žádosti o přátelství", //header
	loadUrl: loadFriendRequestsLink, /* link (url) vygenerovaný komponentou StandardConversationsList */
	streamSnippetModule: {
		snippetName: 'snippet-friendRequest-requests',
		endMessage: 'Žádné další žádosti.',
		offsetParameter: 'friendRequest-offset',
		limitParameter: 'friendRequest-limit',
		addLimit: 1,
		startOffset: 0
	}
});