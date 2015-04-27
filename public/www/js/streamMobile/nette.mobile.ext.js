/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$.nette.ext('streamAjax', {
		success: function () {/* úspěch operace z nette.ajax */
			$('.stream-item').trigger("create");/* znovuaplikování jQueryMobile na stream */
		}
});