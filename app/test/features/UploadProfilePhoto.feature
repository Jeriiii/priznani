Feature: Uploading profile photo

	# záměrně chybí scenario s "/install/test-data"
	# takže po ručním obnovení DB si zkontrolujte, že ve frontě NENÍ 
	# VÍCE OBRÁZKŮ ČEKAJÍCÍCH NA SCHVÁLENÍ, ať to nemate behat :-)

	Scenario Outline: User uploads profile photo and approves it
		Given I am signed in as "<user>"
		And I go to "/profil.show/"
		Then I attach the file "<image>" to "imageFile0"
		Then I press "send"
		And I should see "<message>"
		Then I go to "/admin.accept-images/"
		And I follow "Schválit"
		When I go to "/profil.show/"
		Then I should see "<text>"
		
		Examples:
			| user		      | image					| message						| text															|
			| admin@test.cz	  | profile_photo_woman.jpg	| Profilové foto bylo uloženo.  | Test Admin > Profilové fotky	|