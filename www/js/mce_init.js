tinyMCE.init({
        language : 'cs',
        mode: "specific_textareas",
		theme : "advanced",
		plugins : "emotions,spellchecker,advhr,insertdatetime,preview",
		skin : "o2k7",
		skin_variant : "silver",
                width: "100%",

	// Theme options - button# indicated the row# only
		theme_advanced_buttons2 : "formatselect,fontselect,fontsizeselect,|,undo,redo",
		theme_advanced_buttons1 : "bullist,numlist,|,outdent,indent,|,link,unlink,anchor|,code,preview,|,forecolor,backcolor,|,insertdate,inserttime,charmap",
		theme_advanced_buttons3 : "bold,italic,underline,|,justifyleft,justifycenter,justifyright,|,advhr,removeformat,|,sub,sup",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
	//theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : false
	});


