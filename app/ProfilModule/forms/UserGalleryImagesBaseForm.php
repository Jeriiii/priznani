<?php

namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Utils\Html,
	Nette\ComponentModel\IContainer,
	NetteExt\Image;


class UserGalleryImagesBaseForm extends Form
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
        
        public function addImagesFile($count){                 
            for($i=0 ; $i < $count; $i++){                    
                    $this->addUpload('foto'.$i, 'Přidat fotku:')                        
                        ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024)
                        ->AddCondition(Form::MIME_TYPE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/jpg,image/png,image/jpeg,image/gif');
                    $this->addText('image_name'.$i, 'Jméno:')
                        ->AddConditionOn($this['foto'.$i], Form::FILLED)
                        ->addRule(Form::MAX_LENGTH, "Maximální délka jména fotky je %d znaků", 40)
                        ->addRule(Form::FILLED, 'Zadejte jméno fotky');
                    $this->addText('description_image'.$i, 'Popis:')
                        ->AddConditionOn($this['foto'.$i], Form::FILLED)
                        ->addRule(Form::MAX_LENGTH, "Maximální délka popisu fotky je %d znaků", 500)
                        ->addRule(Form::FILLED, 'Zadejte popis fotky');
                }
        }
         
        
        public function addImages($arr, $values, $uID, $idGallery) {       
            
        foreach ($arr as $key => $image) {
            $name = 'image_name'.$key;        
            $description = 'description_image'.$key;
            
                    if($image->isOK()){                        
                    
                    $valuesDB['suffix'] = $this->suffix( $image->getName() );
                                        
                    $valuesDB['name'] = $values->$name;
                    $valuesDB['description'] = $values->$description;                         
                    
                    $valuesDB['galleryID'] = $idGallery;

                    $id = $this->getPres()->context->createUsersImages()
                            ->insert($valuesDB);

                    $this->getPres()->context->createUsersGalleries()
                            ->where('id', $idGallery)
                            ->update(array(
								"bestImageID" => $id,
								"lastImageID" => $id
							));
                                        
                    $this->upload($image, $id, $valuesDB['suffix'], "userGalleries" . "/" . $uID ."/".$idGallery, 500, 700, 100, 130);
                    unset($image);
                    }
                }
        }
        
}
