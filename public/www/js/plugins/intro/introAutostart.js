$(document).ready(function() {
	/* pokud je uživatel na stránce poprcé, zapne průvodce */	
	if ( $( "#startIntroBtn" ).data('autoStart') == 1 ) { //pokud existuje element autoStartIntro
		$( "#startIntroBtn" ).trigger('click');
	}
});