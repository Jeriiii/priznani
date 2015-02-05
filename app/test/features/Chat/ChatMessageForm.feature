Feature: User can use form to sending messages to any user
# user@test.cz coded id: 2933593
# admin@test.cz coded id: 8447904
# terka1611@seznam.cz coded id: 75624012
	
	Scenario Outline: Sending and receiving a message
		Given I am signed in as <sender>
		And I am on "/profil.show/?id=<receiverId>"
		When I fill in <message> for "frm-sendMessageForm-text"
		And I press "frm-sendMessageForm-send"
		Then there should be <message> in column "text" in "chat_messages"
		When I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		Then the response should contain <message> 
		
		Examples:
		|	sender			|	receiver				|	receiverId	|	message								|
		|	"user@test.cz"	|	"admin@test.cz"			|	4			|	"Text zprávy"						|
		|	"user@test.cz"	|	"terka1611@seznam.cz"	|	12			|	"Text zprávy"						|
		|	"admin@test.cz"	|	"terka1611@seznam.cz"	|	12			|	"Text zprávy"						|

	Scenario Outline: Unsigned user can not send messages
		Given I am on "/profil.show/?id=<receiverId>"
		Then I should not see an "frm-sendMessageForm-text" element

		Examples:
		|	receiverId	|
		|	4			|
		