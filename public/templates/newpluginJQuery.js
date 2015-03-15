<#assign licenseFirst = "/* ">
<#assign licensePrefix = " * ">
<#assign licenseLast = " */">
<#include "${project.licensePath}">
 
 
/**
 * NAPIŠ POPIS PLUGINU
 *
 * @author ${user}
 */

;
(function($) {

	/**
	 * Navázání pluginu do jquery
	 * @param {Object} options
	 * @returns {Object} Instance pluginu.
	 */
	$.fn.${name} = function(options) {
		var opts = $.extend({}, $.fn.stream.defaults, options);
		
		/* Aby jsme mohli plugin použít i na více prvků. */
		return this.each(function() {
			init(opts);
		});
	};
	
	function init(opts) {
		// TO DO - výkoný kód pluginu
	}

	$.fn.stream.defaults = {

	};

})(jQuery);
