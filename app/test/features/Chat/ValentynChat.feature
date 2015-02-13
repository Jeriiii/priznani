Feature: Testing chat on valentyn
	
	
	Scenario: Test initializing page
		When I am on "/chat/valentyn"
		Then I should see "Valentýnský chat"

	Scenario Outline: Sending and receiving a message
		Given I am signed in as <sender>
		And I am on "/chat/valentyn"
		When I fill in <message> for "frm-valChatMessages-messageNewForm-message"
		And I press "frm-valChatMessages-messageNewForm-submit"
		Then I should see <message>
		
		Examples:
		|	sender			|	message								|
		|	"user@test.cz"	|	"Text zprávy"						|
		|	"admin@test.cz"	|	"Jiný text zprávy"					|
		|	"user@test.cz"	|	"šě#%$#"							|
		|	"user@test.cz"	|	"1235468"							|
		|	"user@test.cz"	|	"[{]})_()"							|
		|	"user@test.cz"	|	"0WĂŰËowŻ-AD4◘"						|

	Scenario Outline: Sending and receiving a message
		Given I am signed in as <sender>
		And I am on "/chat/valentyn"
		When I fill in <message> for "frm-valChatMessages-messageNewForm-message"
		And I press "frm-valChatMessages-messageNewForm-submit"
		Then I am signed in as <receiver>
		And I am on "/chat/valentyn"
		Then I should see <message>
		
		Examples:
		|	sender			|	receiver		|	message								|
		|	"user@test.cz"	|	"admin@test.cz"	|	"Text zprávy"						|
		|	"admin@test.cz"	|	"user@test.cz"	|	"Jiný text zprávy"					|
		|	"user@test.cz"	|	"admin@test.cz"	|	"šě#%$#"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"1235468"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"[{]})_()"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"0WĂŰËowŻ-AD4◘"						|

