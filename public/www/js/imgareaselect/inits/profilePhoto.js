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
 * @param {type} imageElement
 * @returns {undefined}
 */
function resizeImage(imageElement) {
	var scrollContent = imageElement.parent();
	var window = imageElement.parents('.posPopUp');
	var windowContent = imageElement.parents('.ajaxBoxContent');
	if(scrollContent.width() < imageElement.width()){/* korekce šířky */
		imageElement.css('width', '100%');
	}else{
		imageElement.css('margin', '20px');
		var origWidth = window.width();
		var newWidth = Math.max(imageElement.width() + 40, window.find('form').outerWidth());
		window.css('width', newWidth);/* zmenšení okna podle obrázku */
		window.css('left',  '+=' + ((origWidth - newWidth) / 2));
	}
	var heightDifference = windowContent.height() - scrollContent.height();
	if(heightDifference > 0){/* změna výšky okna */
		windowContent.css('height', '-=' + heightDifference);
		window.css('height', '-=' + heightDifference);
		window.css('top', '+=' + heightDifference / 2);
	}
}
$(document).ready(function () {
	moveToFooter($('.withImage form'));
	var trueImg = $('#profilePhotoToCrop');
	var trueWidth = trueImg.width();
	var trueHeight = trueImg.height();
	resizeImage(trueImg);
	var imageName = $('#addProfilePhotoWindow .cropContainer').attr('data-image-name');
	$('input[name="imageName"]').val(imageName);
	var $img = $('#profilePhotoToCrop');
	$('input[name="imageX1"]').val(0);
	$('input[name="imageX2"]').val(50);
	$('input[name="imageY1"]').val(0);
	$('input[name="imageY2"]').val(50);

	var img = new Image();
	img.src = $img.attr('src');
	img.onload = function () {
		var imgWidth = $('#profilePhotoToCrop').outerWidth();
		var imgHeight = $('#profilePhotoToCrop').outerHeight();
		var trueWidthCoef = trueWidth / imgWidth;
		var trueHeightCoef = trueHeight / imgHeight;
		var maxSize = Math.min(imgWidth, imgHeight);
		$('input[name="imageX1"]').val(0);
		$('input[name="imageX2"]').val(Math.round(maxSize * trueWidthCoef));
		$('input[name="imageY1"]').val(0);
		$('input[name="imageY2"]').val(Math.round(maxSize * trueWidthCoef));

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
			show: true,
			x1: 0,
			y1: 0,
			x2: maxSize,
			y2: maxSize,
			zIndex: 15000,
			position: 'absolute',
			aspectRatio: '1:1',
			parent: '#addProfilePhotoWindow .cropContainer'
		});
	};

});







