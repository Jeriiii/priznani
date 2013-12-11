/*
 * jQuery File Upload Plugin JS Example 6.11
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

/*jslint nomen: true, unparam: true, regexp: true */
/*global $, window, document */

$(function () {
    'use strict';
	var file = $("#file_upload");//alert(file.text());
    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: 'http://localhost/nette/jednoduche_stranky/www/php/?id_file=' + file.text(),
		//url: 'http://levnyenergetickystitek.cz/php/?id_file=' + file.text(),
		//url: 'http://levnyenergetickystitek.cz/php/',
		always: (function() {
			$(".error").each(
				function() {
					if($(this).attr("bat_error").search("SyntaxError") != -1) {
						$(this).text("Připraven");
						$(this).css("color","black");
					}
				}
			);
		}),
		autoUpload: true
    });

    // Enable iframe cross-domain access via redirect option:
    $('#fileupload').fileupload(
        'option',
        'redirect',
        window.location.href.replace(
            /\/[^\/]*$/,
            '/cors/result.html?%s'
        )
    );

    if (window.location.hostname === 'levnyenergetickystitek.cz') {
        url: 'http://levnyenergetickystitek.cz/php/?id_file=' + file.text()
    }
        // Load existing files:
        $.ajax({
            // Uncomment the following to send cross-domain cookies:
            //xhrFields: {withCredentials: true},
            url: $('#fileupload').fileupload('option', 'url'),
            dataType: 'json',
            context: $('#fileupload')[0]
        }).done(function (result) {
            if (result && result.length) {
                $(this).fileupload('option', 'done')
                    .call(this, null, {result: result});
            }
        });

});
