Feature: Uploading photo on main page

	# Pozn: záměrně chybí scenario s "/install/test-data"
	# 1) po ručním obnovení DB NESMÍ BÝT V ADMINISTRACI DALŠÍ OBRÁZKY ČEKAJÍCÍ NA SCHVÁLENÍ -> smazat je
	# 2) pokud test nefunguje, obnovte/přeinstalujte DB + krok 1)

	Scenario Outline: User uploads photo on main page and approves it
		Given I am signed in as "<user>"
		And I am on "/"
		And I should see "Fotky"
		And I attach the file "<image0>" to "imageFile0"
		And I attach the file "<image1>" to "imageFile1"
		And I attach the file "<image2>" to "imageFile2"
		And I press "frm-userStream-newStreamImageForm-submit"
		And I should see "<message>"
		Then I go to "/admin.accept-images/"
		And I follow "Schválit"
		And I follow "Schválit"
		And I follow "Schválit"
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
		When I go to "/"
		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"

		Examples:
			| user		      | image0					| message			   | text						|
			| admin@test.cz	  | profile_photo_woman.jpg	| Fotky byly přidané.  | Test Admin > Moje fotky	|