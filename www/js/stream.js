;(function($) {
	
	/* nastavení */
	var opts;
	
	/* main */
	$.fn.stream = function(options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		setOpts(opts);
		
		$.nette.init();
		timeCheckStream();
	};
	$.fn.stream.defaults = {
		offset: 0,
		addoffset: 3,
		ajaxLocation: '#next-data-item-btn',
                btnNext: '.stream-btn-next',
                streamLoader: '#stream-loader',
                msgName: '.stream-message',
                msgText: "Žádné starší příspěvky nebyly nalezené",
                offsetName: '&stream-offset=',
		rows: 6
	};
	/* prodlouží stream */
	function changeStream() {	
		var location = $(this.opts.ajaxLocation).attr('href');
		$(this.opts.btnNext).hide();

		/* přidá další příspěvky */
		if(this.opts.offset+1 <= this.opts.rows) {
			$(this.opts.streamLoader).show();
			this.opts.offset = this.opts.offset + this.opts.addoffset;
			$(this.opts.ajaxLocation).attr("href", location + this.opts.offsetName + this.opts.offset);
			//$.nette.ext('snippet-stream-posts');

			$.nette.ajax({
				url: location + this.opts.offsetName + this.opts.offset,
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
			$(this.opts.msgName).text(this.opts.msgText);
			$(this.opts.streamLoader).hide();
			$(this.opts.btnNext).hide();
		}
	}

	/* naplánuje další kontrolu za daný časový interval(půl vteřinu) */
	function timeCheckStream() {
		setTimeout(function() { visibleCheckStream();}, 500);
	}

	/* zkontroluje, zda je uživatel na konci seznamu. Když ano, zavolá prodloužení */
	function visibleCheckStream() {
		var documentScrollTop = jQuery(document).scrollTop();
		var viewportHeight = jQuery(window).height();

		var minTop = documentScrollTop;
		var maxTop = documentScrollTop + viewportHeight;
		var elementOffset = $(this.opts.streamLoader).offset();

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

