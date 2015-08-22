/*
 * @copyright Copyright (c) 2013-2015 Kukral COMPANY s.r.o.
 */
/**
 * Tento skript se stará o správné chování rozmazaných obrázků
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */

/**
 * Předaným elementům (předává se pouze selektor) přiřadí chování očekávatelné od rozmazaného obrázku
 * @param {object} imagesSelector jQuery selektor obrázků
 * @param {string} link odkaz, kam bude uživatel přesměrován po kliknutí na odkaz ve zprávě
 */
function applyBlurryBehavior(imagesSelector, link) {
	var tooltip = $('<div class="basicTooltip">Buď fér a ukaž se ostatním, než si je začneš prohlížet.<a href="' + link + '" title="Nahraj si profilovku">Nahrát fotku</a></div>');
	$('body').append(tooltip);
	$(document).on({
		mouseenter: function () {
			var image = $(this);
			tooltip.css('top', image.offset().top + 15);
			tooltip.css('left', image.offset().left + ((image.outerWidth() - tooltip.outerWidth()) / 2));
			tooltip.css('display', 'block');
		},
		mouseleave: function () {
			tooltip.css('display', 'none');
		}
	}, imagesSelector);
	/* když přejedu na tooltip */
	tooltip.hover(function(){
		tooltip.css('display', 'block');
	}, function(){
		tooltip.css('display', 'none');
	});
}