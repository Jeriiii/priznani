;(function($) {
	
	/* nastavení */
	var opts;
	
	/* main */
	$.fn.stream = function(options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		setOpts(opts);
		
		$.nette.init();
		$('.stream-loader').hide();

		timeCheckStream();
	};
	$.fn.stream.defaults = {
		offset: 0,
		addoffset: 3,
		ajaxLocation: '#next-data-item-btn',
		rows: 6
	};
	/* prodlouží stream */
	function changeStream() {	
		var location = $(this.opts.ajaxLocation).attr('href');
		$('.stream-btn-next').hide();

		/* přidá další příspěvky */
		if(this.opts.offset+1 <= this.opts.rows) {
			$('#stream-loader').show();
			this.opts.offset = this.opts.offset + this.opts.addoffset;
			$('#next-data-item-btn').attr("href", location + '&stream-offset=' + this.opts.offset);
			//$.nette.ext('snippet-stream-posts');

			$.nette.ajax({
				url: location + "&stream-offset=" + this.opts.offset,
				success: function(response) {
//							console.log(response);
				},
				complete: function(payload) {
//							console.log(payload);
				}
			});
		}
		/* Nejsou-li žádné další příspěvky, vypíše hlášku, že už nejsou */
		if(this.opts.offset+1 > this.opts.rows) {
			$('.stream-message').text("Žádné starší příspěvky nebyly nalezené");
			$('#stream-loader').hide();
			$('.stream-btn-next').hide();
		}
	}

	/* naplánuje další kontrolu za daný časový interval */
	function timeCheckStream() {
		setTimeout(function() { visibleCheckStream();}, 500);
	}

	/* zkontroluje, zda je uživatel na konci seznamu. Když ano, zavolá prodloužení */
	function visibleCheckStream() {
		var documentScrollTop = jQuery(document).scrollTop();
		var viewportHeight = jQuery(window).height();

		var minTop = documentScrollTop;
		var maxTop = documentScrollTop + viewportHeight;
		var elementOffset = $('#stream-loader').offset();

		/* naskroluju-li nakonec stránky if větev projde */   
		if( elementOffset.top > minTop &&  elementOffset.top < maxTop) {
			changeStream();
		}
		timeCheckStream();
	}
	
	function setOpts(opts) {
		this.opts = opts;
	}
})(jQuery);

