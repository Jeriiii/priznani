Feature: Uploading photo on main page

	Scenario Outline: User uploads photo on main page and approves it
		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		And I attach the file "<image0>" to "newStreamImageFormImageFile0"
		And I attach the file "<image1>" to "newStreamImageFormImageFile1"
		And I attach the file "<image2>" to "newStreamImageFormImageFile2"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		And I should see "<message>"
		And Approve last image
		And Approve last image
		And Approve last image
#		Zakomentováno kvůli tomu, že ve streamu se nezobrazí fotka uživatele co jí nahrál jemu
# 		When I go to "/"
# 		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"
		
		Examples:
			| user		      | image0					| image1		| image2		| message												 | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| afro.jpg		| man.png		| Fotky byly přidané. Nyní jsou ve frontě na schválení.  | Test Admin > Moje fotky	|

	Scenario Outline: Photos are automatically approved
 		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		When I attach the file "<image0>" to "newStreamImageFormImageFile0"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		Then I should see "<message>"
		Then I should not see "<message2>" 
#		Zakomentováno kvůli tomu, že ve streamu se nezobrazí fotka uživatele co jí nahrál jemu
# 		When I go to "/"
# 		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"

		Examples:
			| user		      | image0					| message			   | message2                          | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| Fotky byly přidané.  | Nyní jsou ve frontě na schválení. | Test Admin > Moje fotky	|

	Scenario: Error when form is not fill
		Given I am signed in as "admin@test.cz"
		And I am on "/"
		And I should see "Fotky"
		And I attach the file "profile_photo_woman.jpg" to "newStreamImageFormImageFile0"
		And I press "frm-userStream-newStreamImageForm-submit"
		Then I should not see "Musíte vybrat alespoň 1 soubor"
		