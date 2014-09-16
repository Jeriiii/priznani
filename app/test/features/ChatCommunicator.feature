Feature: Testing chat messaging with ajax requests and responses
# user@test.cz coded id: 2933593
# admin@test.cz coded id: 8447904
	Background:
		Given I am testing ajax
		Given I recreate data in database

	Scenario Outline: Sending and receiving a message
		Given I am signed in as <sender>
		Then I send chat message <message> to <receiverCodedId>
		And There should be <message> in column "text" in "chat_messages"
		When I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		Then There should be <message> in response
		
		Examples:
		|	sender			|	receiver		|	receiverCodedId	|	message								|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"		|	"Text zprávy"						|
		|	"admin@test.cz"	|	"user@test.cz"	|	"2933593"		|	"Jiný text zprávy"					|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"		|	"šě#%$#"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"		|	"1235468"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"		|	"[{]})_()"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"		|	"0WĂŰËowŻ-AD4◘"						|

# sichr test, jestli se všechna předchozí data smazala
	Scenario: Test recreating database
		Given I am signed in as "user@test.cz"
		And There should not be "Text zprávy" in column "text" in "chat_messages"

#na tento test jsou připravená data v databázi - zprávy s id 1 a 2
	Scenario Outline: Receive confirm of reading message
		Given I am signed in as <sender>
		Then I send chat message "zpráva" to <receiverCodedId>
		Given I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		And I read messages in response
		Then I am signed in as <sender>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		And I look on the page
		Then There should be "Doručeno." in response
		
		Examples:
		|	sender			|	receiver		|	receiverCodedId	|
		|	"user@test.cz"	|	"admin@test.cz"	|	"8447904"	|
		|	"admin@test.cz"	|	"user@test.cz"	|	"2933593"	|