Feature: Edit user profile

######################### změna statusu uživatele byla odložena a není v první verzi přiznání 
# 	Scenario Outline: User can edit State
# 		Given I am on "/"
# 	    And I am signed in as "user@test.cz"
# 		When I follow "Změna statusu"
# 		Then I should see "Změna statusu"
# 		And I select "<option>" from "statusID"
# 		And I press "Změnit"
# 		Then I should see "Status byl změněn"
# 		And the "statusID" field should contain "<value>"
# 
# 
# 	Examples:
# 		| option					| value	|
# 		| Poznávat nové přátele	| 1		|
# 		| Chatovat				| 2		|
# 		| Randit					| 3		|
# 		| Sexovat				| 4		|

	Scenario: User can change Basic info 
	    Given I am signed in as "user@test.cz"
		And I am on "/profil.edit/"		
		And I should see "Základní údaje"
		And I select "1" from "frm-firstEditForm-day"
		And I select "leden" from "month"
		And I select "1990" from "year"
		And I press "frm-firstEditForm-send" 
		Then I should see "Informace byla změněny"

	Scenario Outline: User can change Identity info
		Given I am signed in as "user@test.cz"
	    And I am on "/profil.edit/"
		And I should see "Identifikační údaje"
		And I fill in "<first>" for "first_sentence"
		And I fill in "<about>" for "about_me"
		And I press "frm-secondEditForm-send" 
		Then I should see "Změna identifikačních údajů byla úspěšná"

		Examples:
		| first			| about				|
		| Chcete mě?	| Hledám pobavení	|


############################ v první verzi pos neobsahuje toto nastavení
 
#	Scenario Outline: User can change Interests
# 	    Given I am signed in as "user@test.cz"
# 		And I am on "/profil.edit/"		
# 		And I should see "Zajímám se o"
# 		And I select "ano" from "<form_element>"
# 		And I press "frm-interestedInForm-send"
# 		Then I should see "Změna doplňujících údajů byla úspěšná"
#  
# 		 Examples:
#          | form_element		|
# 		 | threesome		|
# 		 | anal				|
# 		 | group			|
# 		 | bdsm				|
# 		 | swallow			|
# 		 | oral				| 
# 		 | piss				|
# 		 | sex_massage		|
# 		 | petting			|
# 		 | fisting			|
# 		 | deepthrought		|

	Scenario: User can change favourite positions
	    Given I am signed in as "user@test.cz"
		And I am on "/profil.edit/"		
		And I should see "Oblíbené polohy při milování"
		And I check "positions[]"
		And I press "frm-editPlacesPositionsForm-send"
		Then I should see "Údaje byly změněny"


	Scenario: User can change favourite places
	    Given I am signed in as "user@test.cz"
		And I am on "/profil.edit/"	
		And I should see "Oblíbená místa při milování"
		And I check "positions[]"
		And I press "frm-editPlacesPositionsForm-send"
		Then I should see "Údaje byly změněny"



