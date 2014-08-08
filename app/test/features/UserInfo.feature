Feature: User's info

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: User's info can be seen from "/profil.show/user-info"
		Given I am signed in as "<user>"
		And I am on "/profil.show/user-info"
		Then I should see "Informace o uživateli"
		And I should see "Profil uživatele <name>"
		And I should see "Jméno <name>"
		And I should see "První věta <firstSentence>"
		And I should see "Druh uživatele <userProperty>"
		And I should see "O mně <about>"
		And I should see "Věk <age>"
		And I should see "Výška <tallness>"
		And I should see "Typ těla <bodyType>"
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
			| user				| name			 | firstSentence	 | userProperty | about						| age | tallness     | bodyType |
			| user@test.cz		| Test User		 | Oh bože, už budu. | Muž          | Hledám zábavu a vzrušení. | 25  | 170 - 180 cm | při těle |