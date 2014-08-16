// Need this to make IE happy
// see http://soledadpenades.com/2007/05/17/arrayindexof-in-internet-explorer/
if (!Array.indexOf) {
	Array.prototype.indexOf = function(obj) {
		for (var i = 0; i < this.length; i++) {
			if (this[i] == obj) {
				return i;
			}
		}
		return -1;
	}
}


var chatboxManager = function() {

	// list of all opened boxes
	var boxList = new Array();
	// list of boxes shown on the page
	var showList = new Array();

	var config = {
		width: 200, //px
		gap: 20,
		maxBoxes: 8,
		offset: 300,
		messageSent: function(dest, msg) {
			// override this
			$("#" + dest).chatbox("option", "boxManager").addMsg(dest, msg);
		}
	};

	var init = function(options) {
		$.extend(config, options)
	};


	var delBox = function(id) {
		// TODO
	};

	var getNextOffset = function() {
		return config.offset + ((config.width + config.gap) * showList.length);
	};

	var boxClosedCallback = function(id) {
		// close button in the titlebar is clicked
		var idx = showList.indexOf(id);
		if (idx != -1) {
			showList.splice(idx, 1);
			diff = config.width + config.gap;
			for (var i = idx; i < showList.length; i++) {
				offset = $("#" + showList[i]).chatbox("option", "offset");
				$("#" + showList[i]).chatbox("option", "offset", offset - diff);
			}
		}
		else {
			//alert("should not happen: " + id);
		}
	};



	// caller should guarantee the uniqueness of id
	//returns true if box is created
	var addBox = function(id, data, name) {
		var idx1 = showList.indexOf(id);
		var idx2 = boxList.indexOf(id);
		if (idx1 != -1) {
			// found one in show box, do nothing
		}
		else if (idx2 != -1) {
			// exists, but hidden
			// show it and put it back to showList
			$("#" + id).chatbox("option", "offset", getNextOffset());
			var manager = $("#" + id).chatbox("option", "boxManager");
			manager.toggleBox();
			showList.push(id);
		}
		else {
			var el = document.createElement('div');
			el.setAttribute('id', id);
			$(el).chatbox({id: id,
				user: data,
				title: data.title,
				hidden: false,
				width: config.width,
				offset: getNextOffset(),
				messageSent: config.messageSent,
				boxClosed: boxClosedCallback
			});
			$(el).parent().parent().wrap('<div class="ui-chat"></div>');
			boxList.push(id);
			showList.push(id);
			return true;//da vedet, ze vytvoril novy box
		}
		return false;
	};




	/**
	 * Prida zpravu do okna
	 * @param int|String id id okna
	 * @param String name popisek zpravy (typicky odesilatel)
	 * @param String msg text zpravy
	 */
	var addMessage = function(id, name, msg) {
		$("#" + id).chatbox("option", "boxManager").addMsg(name, msg);
	};

	return {
		init: init,
		addBox: addBox,
		delBox: delBox,
		addMessage: addMessage
	};
}();