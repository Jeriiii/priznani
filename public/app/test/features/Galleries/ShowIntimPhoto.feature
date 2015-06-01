Feature: Show intim photo
   Scenario: 
		# neuvidím intimní galerii
		Given I am signed in as "admin@test.cz"
		And I am on "/"
		Then I should not see "Foto 2 uživatele Test User"

		# přepnu se že chvi vidět i intimní galerie
		Given I am on "profil.edit/" 
		Then I select "1" from "frm-setInitimityForm-intimity-1"
		And I press "frm-setInitimityForm-send"
		And I should see "Nastavení bylo změněno"

		# vidím intimní fotky
		And I am on "/"
		Then I should see "Foto 2 uživatele Test User"
