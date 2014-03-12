<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;


class UserGalleryBaseForm extends Form
{	
	public function upload($image, $id, $suffix, $folder, $max_height, $max_width, $max_minheight, $max_minwidth){
		if($image->isOK() & $image->isImage())
		{		   
		    /* uložení souboru a renačtení */
			$dir = WWW_DIR."/images/" . $folder . "/";
			if(!file_exists($dir)){
				mkdir($dir, 0742);
			}
			$file = $id . '.' . $suffix;
			// originální obrázek
			$path = $dir . $file;
			// obrázek pro náhled v galerii
			$pathGalleryScreen = $dir . "galScrn" . $file;
			// čtvercový výřez
			$pathSqr = $dir . "minSqr" . $file;
			// miniatura - proporcionální
			$pathMin = $dir . "min" . $file;
			
		    $image->move($path);
		    
		    /* kontrola velikosti obrázku, proporcionální zmenšení*/
			$image = Image::fromFile($path);
			$image->resize($max_width, $max_height);
		    $image->save($pathGalleryScreen);
			
			/* vytvoření ořezu 200x200px*/
			$image = Image::fromFile($path);
			$image->resizeMinSite(200);
			$image->cropSqr(200);
		    $image->save($pathSqr);
			
			/* vytvoření miniatury*/
			$image = Image::fromFile($path);
			$image->resize($max_minwidth, $max_minheight);
		    $image->save($pathMin);
			
		 } else {
		    $this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
		 }	    
	}
	
	public function suffix($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
}
