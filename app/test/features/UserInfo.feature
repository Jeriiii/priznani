Feature: User's info

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: User's info can be seen from "/profil.show/user-info"
		Given I am signed in as "<user>"
		And I am on "/profil.show/user-info"
		Then I should see "Informace o uživateli"
		And I should see "Profil uživatele <name>"
		And I should see "Jméno"
		And I should see "První věta"
		And I should see "Druh uživatele"
		And I should see "O mně"
		And I should see "Věk"
		And I should see "Výška"
		And I should see "Typ těla"
		And I should see "Kouřeni cigaret"
		And I should see "Pití alkoholu"
		And I should see "Vzdělání"
		And I should see "Status"
		And I should see "Sexuální orientace"
		And I should see "Trojka"
		And I should see "Anální sex"
		And I should see "Skupinový sex"
		And I should see "BDSM"
		And I should see "Polykání"
		And I should see "Sperma"
		And I should see "Orální sex"
		And I should see "Piss"
		And I should see "Sex masáž"
		And I should see "Petting"
		And I should see "Fisting"
		And I should see "Hluboké kouření"
		And I should see "Délka penisu"
		And I should see "Šířka penisu"
		And I should see "Hledám"

		Examples:
			| user				| name			 |
			| user@test.cz		| Test User		 |