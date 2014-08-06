Feature: Uploading profile photo

	# Pozn: záměrně chybí scenario s "/install/test-data"
	# 1) po ručním obnovení DB NESMÍ BÝT V ADMINISTRACI DALŠÍ OBRÁZKY ČEKAJÍCÍ NA SCHVÁLENÍ -> smazat je
	# 2) pokud test nefunguje, obnovte/přeinstalujte DB + krok 1)

	Scenario Outline: User uploads profile photo and approves it
		Given I am signed in as "<user>"
		And I go to "/profil.show/"
		Then I attach the file "<image>" to "imageFile0"
		Then I press "send"
		And I should see "<message>"
		Then I go to "/admin.accept-images/"
		And I follow "Schválit"
		When I go to "/"
		Then I should see "<text>"
		When I go to "/profil.show/"
		Then I should see "<text>"
		
		Examples:
			| user		      | image					| message						| text															|
			| admin@test.cz	  | profile_photo_woman.jpg	| Profilové foto bylo uloženo.  | Test Admin > Profilové fotky	|