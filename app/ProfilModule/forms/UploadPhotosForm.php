<?php
namespace Nette\Application\UI\Form;

use	Nette\Application\UI\Form,
	Nette\Security as NS,
	Nette\ComponentModel\IContainer, 
	Nette\Image;

class UploadPhotosForm extends EditBaseForm
{
	private $userModelFotos;
	private $userModel;
 

	public function __construct(IContainer $parent = NULL, $name = NULL)
	{
		parent::__construct($parent, $name);
		
		$this->addGroup('Fotografie (max 4 x 4MB)');
		$this->addUpload('foto1', 'Přidat fotku:')
               ->addCondition(Form::FILLED)
               ->addRule(Form::IMAGE, 'Povolené formáty fotografií jsou JPEG,  JPG, PNG nebo GIF', 'image/png,image/jpeg,image/gif')
               ->addRule(Form::MAX_FILE_SIZE, 'Fotografie nesmí být větší než 4MB', 4 * 1024 *1024);
		$this->addText('description1', 'Popis:');
		
		$this->onSuccess[] = callback($this, 'editformSubmitted');
		$this->addSubmit('send', 'Přidat')
				->setAttribute("class", "btn btn-info");
		
		return $this; 		
	}
	
	public function editformSubmitted($form)
	{
		$this->userModel = $this->getPresenter()->context->userModel;
		$presenter = $this->getPresenter();
		$values = $form->values;
		$file= $values->foto1;
		$id_user = $this->getPresenter()->getUser()->getId();
		 $pathToSaveData = WWW_DIR.'/images/users/profils/'.$id_user.'/';
		$count = count(glob($pathToSaveData."*.{jpg,png,gif,bmp}", GLOB_BRACE));
		/* 4 original fotky + 4 mini */
		if($count < 8 ){
			$valuefoto = $values->foto1;
			$nameOfFoto = $valuefoto->getName();
			$suffix = pathinfo($nameOfFoto, PATHINFO_EXTENSION);
			$id = $presenter->context->createUsersFoto()->insertUserFoto(array('userId'=> $id_user, 'suffix' => $suffix, 'description' => $values->description1));

			if(!file_exists($pathToSaveData)){
				mkdir($pathToSaveData, 0742);
			}

			$path = '/users/profils/'.$id_user;
			$this->upload($file, $id , $suffix, $path, 768, 1024);
			$this->presenter->flashMessage('Přidání nové fotky proběhlo úspěšně.');
			$this->presenter->redirect('Editprofil:default');
		} else {
		$this->presenter->flashMessage('Litujeme, Maximální počet fotek dosažen. :-(');
		$this->presenter->redirect('Editprofil:default');
		}
	}

	public function upload($image, $id, $suffix, $folder, $max_height, $max_width){
		if($image->isOK() & $image->isImage())
		{	
			/* uložení souboru a renačtení */
			$way = WWW_DIR."/images/" . $folder . "/" . $id . '.' . $suffix;
			$image->move($way);
			$image = Image::fromFile($way);

			/* kontrola velikosti obrázku, proporcionální zmenšení*/
			if($image->height > $max_height){
				$image->resize(NULL, $max_height);
			}
			if($image->width > $max_width){
				$image->resize($max_width, NULL);
			}
				$image->sharpen();
				$image->save(WWW_DIR."/images/" . $folder . "/" . $id . "." . $suffix);

			/* vytvoření miniatury*/
				$max_height = 100;
				$max_width = 130;
			if($image->height > $max_height){
				$image->resize(NULL, $max_height);
			}
			if($image->width > $max_width){
				$image->resize($max_width, NULL);
			}
				$image->sharpen();
				$image->save(WWW_DIR."/images/" . $folder . "/mini" . $id . "." . $suffix);
			} else {
				$this->addError('Chyba při nahrávání souboru. Zkuste to prosím znovu.');
			}

		}	
}
