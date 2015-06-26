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
	
	/**
	 * Posune pravý okraj obrázku na pozadí na učitou X ovou pozici. Počítá s tím, že se mění velikost obrázku.
	 * @param Object element element, o který se jedná.
	 * @param int positionTo na kterou pozici se obrázek posune
	 * @param int imageResolutionWidth vertikální rozlišení obrázku
	 * @param int imageResolutionHeight horizontální rozlišení obrázku
	 */
	var moveRightCornerOn = function (element, positionTo, imageResolutionWidth, imageResolutionHeight) {
		if(element.outerWidth() > element.outerHeight()){
			var realWidth = element.outerWidth();
		}else{
			var ratio = imageResolutionWidth / imageResolutionHeight;
			var realWidth = element.outerHeight() * ratio;
		}
		var calculatedValue = positionTo - realWidth;
		element.css('background-position', calculatedValue + 'px top');
	};