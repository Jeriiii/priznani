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
		And I attach the file "<image2>" to "imageFile2"
		And I check "<option>"
		When I press "_submit"
		Then I should see "<message>"
		And I should see "Galerie uživatele <userName>"
		And I should see "<galleryName>"
		

		Examples:
			| user			| userName	| galleryName			| descript			 | image0	| image1	| image2				  | option | message			|
			| user@test.cz	| Test User	| moje obrázky	| Fotky mě a mé ženy | afro.jpg	| man.png	| profile_photo_woman.jpg | couple | Galerie byla vytvořena. Fotky budou nejdříve schváleny adminem. |