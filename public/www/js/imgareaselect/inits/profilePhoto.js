/*
 * @copyright Copyright (c) 2013-2013 Kukral COMPANY s.r.o.
 */
;
/**
 * Přesune daný element do footeru okénka
 * @param {type} element
 * @returns {undefined}
 */
function moveToFooter(element){
	var window = element.parents('.posPopUp');
	element.detach().appendTo(window);
}

/**
 * Změní velikost obrázku podle jeho rodiče. Pokud je obrázek menší než okno, zmenší okno
 * @param {object} imageElement element obrázku
 */
function resizeImage(imageElement) {
	var imageContainer = imageElement.parent();
	var popupWindow = imageElement.parents('.posPopUp.withImage');
	var windowContent = imageElement.parents('.ajaxBoxContent');
	imageElement.css('max-width', '100%');
	imageElement.css('max-height', windowContent.height() - popupWindow.find('.window-info').outerHeight() + 'px');
	
	resizeWindowWidthByImage(popupWindow, imageElement);
	resizeWindowHeightByImage(popupWindow, windowContent, imageContainer);
}

/**
 * Změní šířku okna podle velikosti obrázku
 * @param {object} popupWindow jQuery element celého okna
 * @param {object} imageElement jQuery element obrázku
 */
function resizeWindowWidthByImage(popupWindow, imageElement) {
	var origWidth = popupWindow.width();
	var newWidth = Math.max(imageElement.width(), popupWindow.find('form').outerWidth());
	popupWindow.css('width', newWidth);/* zmenšení okna podle obrázku */
	popupWindow.css('left',  '+=' + ((origWidth - newWidth) / 2));
}

/**
 * Změní výšku okna podle velikosti obrázku
 * @param {object} popupWindow jQuery element celého okna
 * @param {object} windowContent jQuery element obsahu okna
 * @param {object} imageContainer jQuery element, ve kterém je obrázek
 */
function resizeWindowHeightByImage(popupWindow, windowContent, imageContainer) {
	var heightDifference = windowContent.height() - imageContainer.height();
	if(heightDifference > 0){/* změna výšky okna */
		windowContent.css('height', '-=' + heightDifference);
		popupWindow.css('height', '-=' + heightDifference);
		popupWindow.css('top', '+=' + heightDifference / 2);
	}
}

/**
 * Nastaví formuláři defaultní hodnoty
 * @param {double} trueWidthCoef koeficient šířky na skutečnou hodnotu
 * @param {double} trueHeightCoef koeficient výšky na skutečnou hodnotu
 * @returns {undefined}
 */
function setInitialValues(trueWidthCoef, trueHeightCoef) {
	$('input[name="imageX1"]').val(0);
	$('input[name="imageX2"]').val(Math.round(50 * trueWidthCoef));
	$('input[name="imageY1"]').val(0);
	$('input[name="imageY2"]').val(Math.round(50 * trueHeightCoef));
}

/**
 * Inicializace crop image
 */
$(document).ready(function () {
	if(!($( ".posPopUp.withImage" ).length)){/* pokud nejde o okno s obrázkem, nedělá nic*/
		return;
	}
	moveToFooter($('.withImage form'));
	var trueImg = $('#profilePhotoToCrop');
	var trueWidth = trueImg.width();
	var trueHeight = trueImg.height();
	resizeImage(trueImg);
	var imageName = $('#addProfilePhotoWindow .cropContainer').attr('data-image-name');
	$('input[name="imageName"]').val(imageName);
	var $img = $('#profilePhotoToCrop');
	var img = new Image();
	img.src = $img.attr('src');
	img.onload = function () {
		var imgWidth = $('#profilePhotoToCrop').outerWidth();
		var imgHeight = $('#profilePhotoToCrop').outerHeight();
		var trueWidthCoef = trueWidth / imgWidth;
		var trueHeightCoef = trueHeight / imgHeight;
		setInitialValues(trueWidthCoef, trueHeightCoef);
		var maxSize = Math.min(imgWidth, imgHeight);
		setPostition(0, 0, Math.round(maxSize * trueWidthCoef), Math.round(maxSize * trueHeightCoef));
		
		$('.img-to-crop').imgAreaSelect({
			handles: true,
			onSelectEnd: function (img, selection) {
				if(selection.x1 === selection.x2 && selection.y1 === selection.y2){/* korekce při odkliknutí */
					setPostition(0, 0, Math.round(maxSize * trueWidthCoef), Math.round(maxSize * trueHeightCoef));
				}else{
					setPostition(Math.round(selection.x1 * trueWidthCoef), Math.round(selection.y1 * trueHeightCoef), Math.round(selection.x2 * trueWidthCoef), Math.round(selection.y2 * trueHeightCoef));
				}
			},
			minHeight: 50,
			minWidth: 50,
			x1: 0,
			y1: 0,
			x2: maxSize -1,
			y2: maxSize -1,
			zIndex: 15000,
			position: 'absolute',
			aspectRatio: '1:1',
			parent: '#addProfilePhotoWindow .cropContainer'
		});
		
		/**
		 * Nastaví pozici výřezu do skrytého formuláře
		 * @param {int} x1 xová souřadnice levého horního rohu
		 * @param {int} y1 yová souřadnice levého horního rohu
		 * @param {int} x2 xová souřadnice pravého dolního rohu
		 * @param {int} y2 yová souřadnice pravého dolního rohu
		 */
	   function setPostition(x1, y1, x2, y2) {
		   $('input[name="imageX1"]').val(x1);
		   $('input[name="imageY1"]').val(y1);
		   $('input[name="imageX2"]').val(x2);		   
		   $('input[name="imageY2"]').val(y2);
	   }
	};
	
});







