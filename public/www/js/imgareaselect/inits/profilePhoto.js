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
$(document).ready(function () {
	moveToFooter($('.withImage form'));
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
//	alert(this.width + 'x' + this.height);
		var maxSize = Math.min(this.width, this.height);
		$('input[name="imageX1"]').val(0);
		$('input[name="imageX2"]').val(maxSize);
		$('input[name="imageY1"]').val(0);
		$('input[name="imageY2"]').val(maxSize);

		$('.img-to-crop').imgAreaSelect({
			handles: true,
			onSelectEnd: function (img, selection) {
				$('input[name="imageX1"]').val(selection.x1);
				$('input[name="imageY1"]').val(selection.y1);
				$('input[name="imageX2"]').val(selection.x2);
				$('input[name="imageY2"]').val(selection.y2);
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







