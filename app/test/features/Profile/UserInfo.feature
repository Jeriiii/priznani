Feature: User's info

	Scenario Outline: User's info is complete and visible
		Given I am signed in as "<user>"
		And I am on "/profil.show/user-info"
		And I should see "<name>"
		And I should see "Jméno <name>"
		And I should see "První věta <firstSentence>"
		And I should see "Jsem <userProperty>"
		And I should see "O mně <about>"
		And I should see "Věk <age>"
		And I should see "Výška <tallness>"
		And I should see "Typ těla <bodyType>"
		And I should see "Kouřeni cigaret <smoking>"
		And I should see "Pití alkoholu <alcohol>"
		And I should see "Vzdělání <education>"
		And I should see "Status <status>"
		And I should see "Sexuální orientace <orientation>"
############################ v první verzi pos neobsahuje toto nastavení
# 		And I should see "Trojka <threesome> " 
# 		And I should see "Anální sex <anal>"
# 		And I should see "Skupinový sex <group>"
# 		And I should see "BDSM <BDSM>"
# 		And I should see "Polykání <swallow>"
# 		And I should see "Orální sex <oral>"
# 		And I should see "Piss <piss>"
# 		And I should see "Sex masáž <massage>"
# 		And I should see "Petting <petting>"
# 		And I should see "Fisting <fisting>"
# 		And I should see "Hluboké kouření <deepThroat>"
		And I should see "Délka penisu (cm) <penisLenght>"
		And I should see "Obvod penisu <penisWidth>"
		And I should see "Hledám <lookFor>"

		Examples:
			| user				| name			 | firstSentence	 | userProperty | about						| age | tallness     | bodyType | smoking		| alcohol	| education | status | orientation	|  penisLenght	| penisWidth | lookFor			|
			| user@test.cz		| Test User		 | Oh bože, už budu. | Muž          | Hledám zábavu a vzrušení. | 26  | 180 - 190 cm | plnoštíhlá | příležitostně | často		| vysoké	| volný	 | hetero	 | 3		| 8cm-11cm	 | ženu, ženský pár	|	
			