Feature: User's info

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: User's info can be seen from "/profil.show/user-info"
		Given I am signed in as "<user>"
		And I am on "/profil.show/user-info"
		Then I should see "<info>"

		Examples:
			| user					|info									|
			| user@test.cz			| Informace o uživateli					|