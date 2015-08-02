/* 
 * Zajišťuje, že se při obnovení snippetu obnoví i jQuery mobile
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */

/* rekce na událost nette ajax - obnovení snippetu*/
$.nette.ext('streamAjax', {
		success: function () {/* úspěch operace z nette.ajax */
			$('.stream-item').trigger("create");/* znovuaplikování jQueryMobile na stream */
		}
});