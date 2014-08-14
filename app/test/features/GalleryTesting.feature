Feature: Create new gallery

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: User can create new gallery and add photos
		Given I am signed in as "<user>"
		And I am on "/profil.galleries/user-gallery-new"
		And I fill in "<galleryName>" for "name"
		And I fill in "<descript>" for "description"
		And I attach the file "<image0>" to "imageFile0"
		And I attach the file "<image1>" to "imageFile1"
		And I check "<option>"
		When I press "_submit"
		Then I should see "<message>"
		And I should see "Galerie uživatele <userName>"
		And I should see "<galleryName>"
		
		Examples:
			| user			| userName	| galleryName	| descript		 | image0	| image1	| option | message			|
			| user@test.cz	| Test User	| moje obrázky	| nějaký popisek | afro.jpg	| man.png	| couple | Galerie byla vytvořena. Fotky budou nejdříve schváleny adminem. |


	Scenario Outline: Edit gallery
		Given I am signed in as "<user>"
		And I am on "/profil.galleries"
		When I follow "editovat galerie"
		Then I should see "Editace galerie"
		And I fill in "<galleryName>" for "name"
		And I fill in "<descript>" for "description"
		And I uncheck "<option1>"
		And I check "<option2>"
		And I press "send"
		Then I should see "<message>"
		Then I go to "/profil.galleries/"
		When I follow "editovat galerie"
		Then the "name" field should contain "<galleryName>"
		And I should see "Popis galerie: <descript>"

		Examples:
			| user			| userName	| galleryName		 | descript				| option1	| option2	| message |
			| user@test.cz	| Test User	| moje super obrázky | nějaký nový popisek	| couple	| more		| Galerie byla úspěšně změněna |

	Scenario Outline: Adding new photos
		Given I am signed in as "<user>"
		And I am on "/profil.galleries"
		Then I follow "editovat galerie"
		And I follow "Přidat novou fotku"
		Then I attach the file "<image0>" to "imageFile0"
		And I fill in "Foto 0" for "imageName0"
		And I fill in "Popisek k Foto 0" for "imageDescription0"
		And I attach the file "<image1>" to "imageFile1"
		And I fill in "Foto 1" for "imageName1"
		And I fill in "Popisek k Foto 1" for "imageDescription1"
		And I press "_submit"
		Then I should see "<message>"
		And I should see "<userName> / Galerie <galleryName>"
		And I should see "Foto 0"
		And I should see "Foto 1"
		
 
		Examples:
			| user			| userName	| galleryName		 | image0	| image1					| message												|
			| user@test.cz	| Test User	| moje super obrázky | man.png	| profile_photo_woman.jpg	| Fotky byly přidané. Nyní jsou ve frontě na schválení. |
