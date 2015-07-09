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
	var scrollContent = imageElement.parent();
	var window = imageElement.parents('.posPopUp.withImage');
	var windowContent = imageElement.parents('.ajaxBoxContent');
	imageElement.css('max-width', '100%');
	imageElement.css('max-height', windowContent.height() - window.find('.window-info').outerHeight() + 'px');
	
	var origWidth = window.width();
	var newWidth = Math.max(imageElement.width(), window.find('form').outerWidth());
	window.css('width', newWidth);/* zmenšení okna podle obrázku */
	window.css('left',  '+=' + ((origWidth - newWidth) / 2));
	
	var heightDifference = windowContent.height() - scrollContent.height();
	if(heightDifference > 0){/* změna výšky okna */
		windowContent.css('height', '-=' + heightDifference);
		window.css('height', '-=' + heightDifference);
		window.css('top', '+=' + heightDifference / 2);
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
		$('input[name="imageX1"]').val(0);
		$('input[name="imageX2"]').val(Math.round(maxSize * trueWidthCoef));
		$('input[name="imageY1"]').val(0);
		$('input[name="imageY2"]').val(Math.round(maxSize * trueHeightCoef));
		$('.img-to-crop').imgAreaSelect({
			handles: true,
			onSelectEnd: function (img, selection) {
				$('input[name="imageX1"]').val(Math.round(selection.x1 * trueWidthCoef));
				$('input[name="imageY1"]').val(Math.round(selection.y1 * trueHeightCoef));
				$('input[name="imageX2"]').val(Math.round(selection.x2 * trueWidthCoef));
				$('input[name="imageY2"]').val(Math.round(selection.y2 * trueHeightCoef));
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
	};

});







