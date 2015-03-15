;function scrollToAnchor(e){ 
	var t=$("a[name='"+e+"']");
	$("html,body").animate({ scrollTop:t.offset().top},800);
}
		
/* form error */
$.fn.exists = function () {
	return this.length !== 0;
};
if($("#formError").exists()) {
	scrollToAnchor('contact');
}

// obecné proměnné
var $checkgroup = $(".checkgroup input");

// funce pro počítání lístku

$.fn.callBill = function(oddPrice, game_price) {
	if(!game_price) {
		var game_id = $(this).data("name");
		game_price = $("#price-game-" + game_id).data("price");
	}
	var $bill_element = $("#order-price");
	var bill = parseInt($bill_element.text());

	if( $(this).is(':checked') ) {
		$bill_element.text(bill + game_price);
	}
	else {
		if(oddPrice) {
			$bill_element.text(bill - game_price);
		}
	}
};

// fce pro zaslání poštou
$.fn.calSendPost = function () {
	//přepočítání poštovného
	$(this).callBill(true, 60);
	// přepočítání nákladů na vytisknutí
	if($(this).is(':checked')) {
		$checkgroup.each(function() {
			$(this).callBill(false, 39);
		});
	}else{
		$checkgroup.each(function() {
			$(this).callBill(false, -39);
		});
	}
};

// přepočítání po odeslání formuláře s chybou

$checkgroup.each(function() {
	$(this).callBill(false);
});

// průběžná kontrola - kliknutí na checkbox

$.fn.changeBill = function () {
	this.each(function() {
		$(this).change(function() {
			var $check_send_post = $("#frm-eshopGamesOrdersForm-print");
			
			$(this).callBill(true);
			
			if($check_send_post.is(":checked")) {
				$(this).callBill(true, 39);
			}
		});
	});
};

$checkgroup.changeBill();

// zaslání poštou - zmáčknutí tohoto tlačítka
$("#frm-eshopGamesOrdersForm-print").change(function() {
	$(this).calSendPost();
	
	if($(this).is(":checked")) {
		$("#form-address").show();
	}else{
		$("#form-address").hide();
	}
});

// zaslání poštou - první kontrola

var $send_checkbox = $("#frm-eshopGamesOrdersForm-print");
if($send_checkbox.is(':checked') ) {
	$send_checkbox.calSendPost();
}else{
	$("#form-address").hide();
}

$(".scroll-to-contact").click(function(event) {
	event.preventDefault();
	scrollToAnchor('contact');
});