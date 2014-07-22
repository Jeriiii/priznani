Feature: Enviroment.
  User is able to do various things.

	Scenario: User see homepage
		Given I am on "/"
		Then I should see "přiznání"

	Scenario: User see erotic games
		Given I am on "/"
		Then I should see "přiznání"
		And I follow "Erotické hry"
		Then I should see "fantazie"

	Scenario: Admin has access to administration
		Given I am signed in as "p.kukral@seznam.cz"
		Given I am on "/admin.forms/forms"
		Then I should see "Přejít na:"
		And I should see "OBJEDNÁVKY"


	Scenario: Testing new feature
		Given I am on "/eshop/game"
		Then I should see "fantazie"
		And It looks great

	Scenario: Testing signed as
		Given I am signed in as "terka1611@seznam.cz"
		Then I should see "Terka"

	Scenario: Testing signed as
		Given I am on "/"
		Then I should not see "Terka"

	Scenario:
		Given I am signed in as "terka1611@seznam.cz"
		Given I am on "/admin.forms/forms"
		Then I should not see "Přejít na:" 
		And I should not see "OBJEDNÁVKY"



	Scenario:
		And I am signed in as "p.kukral@seznam.cz"
		And I am on "/profil.galleries/"
		Then I should see "Galerie uživatele Jerry"

		
