Feature: Create new gallery

	Scenario Outline: User can create new gallery and add photos
		Given I am signed in as "<user>"
		And I am on "/profil.galleries/user-gallery-new"
		When I fill in "<galleryName>" for "name"
		And I fill in "<descript>" for "description"
		And I attach to "userGalleryNewImageFile0" the file "<image0>"
		And I attach to "userGalleryNewImageFile1" the file "<image1>"
		And I press "_submit"
		Then I should see "<message>"
		And I should see "<userName>"
		And I should see "<galleryName>"
		
		Examples:
			| user			| userName	| galleryName	| descript		 | image0	| image1	| message			|
			| user@test.cz	| Test User	| Moje obrázky	| nějaký popisek | afro.jpg	| man.png	| Galerie byla vytvořena. Fotky budou nejdříve schváleny adminem. |


	Scenario Outline: Edit gallery
		Given I am signed in as "<user>"
		And I am on "/profil.galleries"
		When I follow "editovat galerie"
		Then I should see "Editace galerie"
		And I fill in "<galleryName>" for "name"
		And I fill in "<descript>" for "description"
		And I press "send"
		Then I should see "<message>"
		Then I go to "/profil.galleries/"
		When I follow "editovat galerie"
		Then the "name" field should contain "<galleryName>"
		And I should see "Popis galerie: <descript>"

		Examples:
			| user			| userName	| galleryName		 | descript				| message |
			| user@test.cz	| Test User	| Moje super obrázky | nějaký nový popisek	| Galerie byla úspěšně změněna |

	Scenario Outline: Adding new photos
		Given I am signed in as "<user>"
		And I am on "/profil.galleries"
		Then I follow "editovat galerie"
		And I follow "Přidat novou fotku"
		Then I attach to "newImageImageFile0" the file "<image0>"
		And I fill in "Foto 0" for "newImageImageName0"
		And I fill in "Popisek k Foto 0" for "newImageImageDescription0"
		And I attach to "newImageImageFile1" the file "<image1>"
		And I fill in "Foto 1" for "newImageImageName1"
		And I fill in "Popisek k Foto 1" for "newImageImageDescription1"
		And I press "_submit"
		Then I should see "<message>"
		And I should see "<userName> / Galerie <galleryName>"
		And I should see "Foto 0"
		And I should see "Foto 1"
		
 
		Examples:
			| user			| userName	| galleryName		 | image0	| image1					| message												|
			| user@test.cz	| Test User	| Moje super obrázky | man.png	| profile_photo_woman.jpg	| Fotky byly přidané. Nyní jsou ve frontě na schválení. |


	Scenario Outline: Show two test pictures 
		Given I am signed in as "<user>"
		When I go to "profil.galleries/image?imageID=6&galleryID=4"
		Then I should see "<imageName1>"
		And I should see "<descript1>"
		When I go to "profil.galleries/image?imageID=7&galleryID=4"
		Then I should see "<imageName2>"
		And I should see "<descript2>"

 
		Examples:
			| user			| imageName1	| imageName2	| descript1						| descript2						|
			| user@test.cz	| Foto 1		| Foto 2		| Foto 1 uživatele Test User	| Foto 2 uživatele Test User	|


	Scenario Outline: Show test gallery info 
		Given I am signed in as "<user>"
		And I am on "profil.galleries/list-user-gallery-images?galleryID=4"
		And I should see "<userName> / Galerie <galleryName>"
		Then I should see "<imageName1>"
		And I should see "<imageName2>"

 
		Examples:
			| user			| userName			| imageName1	| imageName2	| galleryName	|
			| user@test.cz	| Test User			| Foto 1		| Foto 2		| Super fotky	|


	Scenario Outline: Other users cannot edit Test User's photos
		Given I am signed in as "<otherUser>"
		And I am on "profil.galleries/list-user-gallery-images?galleryID=4"
		And I should not see "smazat fotku"
		And I should not see "editovat fotku"
		And I should not see "Přidat novou fotku"
		# And I should see "<galleryName>"
		# When I follow "<userName>"
		# Then I should see "Galerie uživatele <userName>"
 
		Examples:
			| otherUser			| userName			| imageName1	| imageName2	| galleryName	|
			| admin@test.cz		| Test User			| Foto 1		| Foto 2		| Super fotky	|