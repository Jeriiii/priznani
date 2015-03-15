Feature: Uploading photo on main page

	Scenario Outline: User uploads photo on main page and approves it
		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		And I attach to "newStreamImageFormImageFile0" the file "<image0>"
		And I attach to "newStreamImageFormImageFile1" the file "<image1>"
		And I attach to "newStreamImageFormImageFile2" the file "<image2>"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		And I should see "<message>"
		And Approve last image
		And Approve last image
		And Approve last image
		When I go to "/profil.show/"
		Then I should see "<text>"
		
		Examples:
			| user		      | image0					| image1		| image2		| message												 | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| afro.jpg		| man.png		| Fotky byly přidané. Nyní jsou ve frontě na schválení.  | Test Admin > Moje fotky	|

	Scenario Outline: Photos are automatically approved
 		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		When I attach to "newStreamImageFormImageFile0" the file "<image0>"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		Then I should see "<message>"
		Then I should not see "<message2>"
		When I go to "/profil.show/"
		Then I should see "<text>"

		Examples:
			| user		      | image0					| message			   | message2                          | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| Fotky byly přidané.  | Nyní jsou ve frontě na schválení. | Test Admin > Moje fotky	|

	Scenario: Error when form is not fill
		Given I am signed in as "admin@test.cz"
		And I am on "/"
		And I should see "Fotky"
		And I attach to "newStreamImageFormImageFile0" the file "profile_photo_woman.jpg"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		