Feature: Uploading profile photo

	Scenario Outline: User uploads profile photo and approves it
		Given I am signed in as "<user>"
		And I am on "/profil.show/"
		When I attach the file "<image>" to "uploadPhotoFormImageFile0"
		And I press "uploadProfilPhoto"
		Then I should see "<message>"
		And Approve last image
		And I go to "/profil.show/"
		And I should see "<text>"
		
		Examples:
			| user		      | image					| message						| text															|
			| admin@test.cz	  | profile_photo_woman.jpg	| Profilové foto bylo uloženo.  | Test Admin > Profilové fotky	|