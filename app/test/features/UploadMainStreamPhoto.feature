Feature: Uploading photo on main page

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: User uploads photo on main page and approves it
		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		And I attach the file "<image0>" to "imageFile0"
		And I attach the file "<image1>" to "imageFile1"
		And I attach the file "<image2>" to "imageFile2"
		And I press "frm-userStream-newStreamImageForm-submit"
		And I should see "<message>"
		And Approve last image
		And Approve last image
		And Approve last image
		When I go to "/"
		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"
		
		Examples:
			| user		      | image0					| image1		| image2		| message												 | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| afro.jpg		| man.png		| Fotky byly přidané. Nyní jsou ve frontě na schválení.  | Test Admin > Moje fotky	|

	Scenario Outline: Photos are automatically approved
		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		And I attach the file "<image0>" to "imageFile0"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should see "<message>"
		Then I should not see "<message2>" 
		When I go to "/"
		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"

		Examples:
			| user		      | image0					| message			   | message2                          | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| Fotky byly přidané.  | Nyní jsou ve frontě na schválení. | Test Admin > Moje fotky	|