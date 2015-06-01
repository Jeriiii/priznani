Feature: Confessions form

	Scenario Outline: User can post confession
		Given I am on "/"
		And I should see "Přiznání"
		When I fill in "note" with "<text>"
		And I press "frm-addConfessionForm-submit"
		Then I should see "<flash_message>"

		Examples:
			|text									| flash_message	 |
			| Tajně sním o Danu Nekonečném...		| Přiznání bylo vytvořeno, na této adrese můžete sledovat STAV svého přiznání. |


	Scenario Outline: Approve confession
		Given I am signed in as "<admin>"
		And I am on "/"
		And I should see "Přiznání"
		When I fill in "note" with "<text>"
		And I press "frm-addConfessionForm-submit"					
		Then I should see "<flash_message>"
		When I go to "/admin.forms/forms-x?show_mark=unmark&type=1"
		Then I should see "<text>"
		And I follow "vyřídit" 
		Then I should not see "<text>"
		# kvůli obnovení příspěvků na streamu
		Given I am signed in as "<admin>" 
		When I go to "/"
		Then I should see "<text>"

		Examples:
			| admin			| text							 | flash_message					|
			| admin@test.cz | Tajně sním o Danu Nekonečném...| Čeká na schválení adminem		|