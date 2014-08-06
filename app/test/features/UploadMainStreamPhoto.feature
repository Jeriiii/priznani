Feature: Uploading photo on main page

	# Pozn: záměrně chybí scenario s "/install/test-data"
	# 1) po ručním obnovení DB si zkontrolujte, že ve frontě NENÍ VÍCE OBRÁZKŮ ČEKAJÍCÍCH NA SCHVÁLENÍ
	# 2) pokud test nefunguje, přeinstalujte/obnovte DB + krok 1)

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
			| user		      | image0					| image1		| image2		| message												 | text															|
			| admin@test.cz	  | profile_photo_woman.jpg	| afro.jpg		| man.png		| Fotky byly přidané. Nyní jsou ve frontě na schválení.  | Test Admin > Moje fotky	|