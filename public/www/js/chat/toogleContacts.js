/*
 * @copyright Copyright (c) 2013-2014 Kukral COMPANY s.r.o.
 */ 
 
/**
 * Zmenšuje a zvětšuje okénko s kontakty. Reaguje na šířku okna prohlížeče.
 *
 * @author Petr Kukrál <p.kukral@kukral.eu>
 */

;
(function($) {
	$(document).ready(function() {
		var $contacts = $("#contacts");
		var $chatBox = $("#chat-interface");
		var $chatToogleBtn = $("#contact-toogle-btn");
		var $chatHeader = $("#chat-header");
		var $chatSearch = $("#chat-search");
		var isChatBoxVisible = 1;
		var minimalizeWidth = 1150; // šířka okna, kde se zapíná minimalizace chatu
		
		/* schová chat tak, že je vidět jen jeho název - jde použít jen při malé šířce monitoru*/
		var hideChat = function() {
			$chatBox.data('height', $chatBox.height());
			$chatBox.css('height', 'auto');
			$chatBox.width($chatBox.width());
			
			$contacts.hide();
			$chatSearch.hide();
			isChatBoxVisible = 0;
			$.cookie("chat-box-visible", 0);
		};
		
		/* zvětší chat tak, že jsou vidět jeho kontakty */
		var showChat = function() {
			$chatBox.height($chatBox.data('height'));
			$contacts.show();
			$chatSearch.show();
			isChatBoxVisible = 1;
			$.cookie("chat-box-visible", 1);
		};
		
		/* Zvětší chat na výšku stránky a zarovná ho v pravo */
		var maximalizeChat = function (firstLoad) {
			$contacts.show();
			$chatBox.css("position", "auto");

			var pageHeight = $( window ).height();
			
			/* při načtení stránky se tato část neuplatní, protože výšku již nastavil plugin s posuvníkem */
			if(!firstLoad) {
				var chatBoxHeight =  pageHeight - $chatHeader.outerHeight();
				$chatBox.height(chatBoxHeight);
				$contacts.height(chatBoxHeight - $chatSearch.outerHeight());
				$chatBox.css("height", "auto !important");
			}
		};
		
		/* Zmenší chat na 0,8 výšky stránky a chat se může zmenšit / zvětšit kliknutím */
		var minimalizeChat = function(firstLoad) {
			$chatBox.css("height", "auto !important");
			$chatBox.css("position", "fixed");
			$contacts.css("min-width", "180px");
			
			var pageHeight = $( window ).height();
			var correctPageHeight = pageHeight * 0.8; //velikost stránky s korekcí - bere se trochu menší, aby měl chat vůli
			
			if(correctPageHeight < $chatBox.height() ) { //když je chat větší než stránka
				$chatBox.height(correctPageHeight);
					
				/* při načtení stránky se tato část neuplatní, protože výšku již nastavil plugin s posuvníkem */
				if(!firstLoad) {
					var contactNewHeight = correctPageHeight - $chatToogleBtn.outerHeight() - $chatSearch.outerHeight();
					$contacts.height(contactNewHeight);
				}

			}
			
		};
		
		/* Otevře / zavře chat s kontakty */
		var fnToogleContact = function() {
			if(isChatBoxVisible) {
				hideChat();
			} else {
				showChat();
			}
		};
		
		/* správné nastavení velikosti chatu */
		var fnChatResize = function(firstLoad /* true = načtení stránky, false = spuštění kdykoliv za běhu */) {
			if(window.innerWidth > minimalizeWidth) { //innerWidth zahrne i posuvník do šířky stránky
				maximalizeChat(firstLoad);	
			} else {
				minimalizeChat(firstLoad);
			}
		};
		
		$("#contact-toogle-btn").click(function() {
			fnToogleContact();
		});
		
		fnChatResize(true);
		
		/* při prvním spuštění prohlížeče se nastaví chat jako zavřený */
		if($.cookie("chat-box-visible") === undefined) {
			$.cookie("chat-box-visible", 0);
		}
		
		/* Nastavení viditelnosti chatu z cokie. Pokud není vyplněné, dá false. */
		if($.cookie("chat-box-visible") == 0 && window.innerWidth < minimalizeWidth) {
			hideChat();
		}
		
		
		/* reakce na zvětšení - zmenšení šířky okna */
		window.onresize = function() {
			fnChatResize(false);
		};
	});
})(jQuery);
