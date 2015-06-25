/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */
/**
 * Zajišťuje, aby se obrázek, co je na body v pozadí, srovnal podle aktuální velikosti monitoru
 *
 * @author Jan Kotalík <jan.kotalik.pro@gmail.com>
 */
	/**
	 * Zajistí, aby obrázek v pozadí zakrýval celý element
	 * @param String selector selektor elementu, o který se jedná.
	 * @param int imageResolutionWidth vertikální rozlišení obrázku
	 * @param int imageResolutionHeight horizontální rozlišení obrázku
	 */
	var fitBgToElement = function (selector, imageResolutionWidth, imageResolutionHeight) {
		var element = $(selector);
		var width = element.width();
		var height = element.height();
		var elementProportion = width / height;
		var imageProportion = imageResolutionWidth / imageResolutionHeight;

		if (elementProportion < imageProportion) {//obrázek je malý na výšku
			element.css('background-size', 'auto 100%');
		} else {//obrázek je malý na šířku
			element.css('background-size', '100% auto');
		}
	};