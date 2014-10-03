Feature: Testing chat messaging with ajax requests and responses
# user@test.cz coded id: 2933593
# admin@test.cz coded id: 8447904
# terka1611@seznam.cz coded id: 75624012
	Background:
		Given I am testing ajax
		Given I recreate data in database

	Scenario Outline: Sending and receiving a message
		Given I am signed in as <sender>
		Then I send chat message <message> to "<receiverCodedId>"
		And there should be <message> in column "text" in "chat_messages"
		When I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		Then the response should contain <message> 
		
		Examples:
		|	sender			|	receiver		|	receiverCodedId	|	message								|
		|	"user@test.cz"	|	"admin@test.cz"	|	8447904			|	"Text zprávy"						|
		|	"admin@test.cz"	|	"user@test.cz"	|	2933593			|	"Jiný text zprávy"					|
		|	"user@test.cz"	|	"admin@test.cz"	|	8447904			|	"šě#%$#"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	8447904			|	"1235468"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	8447904			|	"[{]})_()"							|
		|	"user@test.cz"	|	"admin@test.cz"	|	8447904			|	"0WĂŰËowŻ-AD4◘"						|

# sichr test, jestli se všechna předchozí data smazala
	Scenario: Test recreating database
		Given I am signed in as "user@test.cz"
		And there should not be "Text zprávy" in column "text" in "chat_messages"


	Scenario Outline: Receive confirm of reading message
		Given I am signed in as <sender>
		Then I send chat message "zpráva" to "<receiverCodedId>"
		Given I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		And I read messages in response
		Then I am signed in as <sender>
		And I am on "/?do=chat-communicator-loadMessages&chat-communicator-fromId=<receiverCodedId>"
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1000000000"
		Then the response should contain "Doručeno." 
		
		Examples:
		|	sender					|	receiver		|	receiverCodedId	|
		|	"user@test.cz"			|	"admin@test.cz"	|	8447904			|
		|	"admin@test.cz"			|	"user@test.cz"	|	2933593			|
		|	"terka1611@seznam.cz"	|	"user@test.cz"	|	2933593			|
		|	"terka1611@seznam.cz"	|	"admin@test.cz"	|	8447904			|

	Scenario Outline: Receive confirm of reading message with multiple senders
		Given I am signed in as <sender>
		Then I send chat message "zpráva" to "<receiverCodedId>"
		Then I send chat message "zpráva2" to "<receiverCodedId>"
		Given I am signed in as <sender2>
		Then I send chat message "zpráva" to "<receiverCodedId>"
		Then I send chat message "zpráva2" to "<receiverCodedId>"
		Given I am signed in as <receiver>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		And I read messages in response
		Then I am signed in as <sender>
		And I am on "/?do=chat-communicator-loadMessages&chat-communicator-fromId=<receiverCodedId>"
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1000000000"
		Then the response should contain "Doručeno." 
		Then I am signed in as <sender2>
		And I am on "/?do=chat-communicator-loadMessages&chat-communicator-fromId=<receiverCodedId>"
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1000000000"
		Then the response should contain "Doručeno." 
		
		Examples:
		|	sender					|	sender2					|	receiver		|	receiverCodedId	|
		|	"user@test.cz"			|	"terka1611@seznam.cz"	|	"admin@test.cz"	|	8447904			|
		|	"admin@test.cz"			|	"terka1611@seznam.cz"	|	"user@test.cz"	|	2933593			|
		|	"terka1611@seznam.cz"	|	"admin@test.cz"			|	"user@test.cz"	|	2933593			|
		|	"terka1611@seznam.cz"	|	"user@test.cz"			|	"admin@test.cz"	|	8447904			|

	Scenario Outline: Load last messages from particular user
		Given I am signed in as <sender>
		Then I send chat message <message> to "<receiverCodedId>"
		Then I send chat message <message2> to "<receiverCodedId>"
		Given I am signed in as <receiver>
		And I am on "/?do=chat-communicator-loadMessages&chat-communicator-fromId=<senderCodedId>"
		Then the response should contain <message>
		And the response should contain <message2>
		
		Examples:
		|	sender					|	receiver					|	senderCodedId	|	receiverCodedId	|	message				| message2			|
		|	"user@test.cz"			|	"admin@test.cz"				|	2933593			|	8447904			|	"text zprávy jedna" | "text zprávy dva"	|
		|	"admin@test.cz"			|	"user@test.cz"				|	8447904			|	2933593			|	"text zprávy jedna" | "text zprávy dva"	|

	Scenario Outline: Sending a message as unsigned user
		When I send chat message <message> to "<receiverCodedId>"
		Then there should not be <message> in column "text" in "chat_messages"

		Examples:
		|	receiverCodedId	|	message								|
		|	8447904			|	"test při nepřihlášeném uživateli"	|

	Scenario: Receiving messages as unsigned user
			When I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
			Then the response should contain "redirect" 

	Scenario Outline: Loading messages as unsigned user
		When I am on "/?do=chat-communicator-loadMessages&chat-communicator-fromId=<userCodedId>"
		And the response should contain "redirect" 
		And the response should not contain "Doručeno." 
		And the response should not contain "<userCodedId>" 

		Examples:
		|	userCodedId	|
		|	8447904		|
	

	Scenario Outline: User is loading just his messages
		Given I am signed in as <user>
		And I send chat message <message> to "<user2CodedId>"
		Given I am signed in as <user2>
		And I send chat message <message2> to "<user3CodedId>"
		Given I am signed in as <user3>
		And I am on "/?do=chat-communicator-refreshMessages&chat-communicator-lastid=1"
		Then the response should contain <message2> 
		And the response should not contain <message> 

	Examples:
		|	user					|	user2						|	user3					|	user2CodedId	|	user3CodedId	|	message				| message2			|
		|	"user@test.cz"			|	"admin@test.cz"				|	"terka1611@seznam.cz"	|	8447904			|	75624012		|	"text zprávy jedna" | "text zprávy dva"	|
		|	"user@test.cz"			|	"terka1611@seznam.cz"		|	"admin@test.cz"			|	75624012		|	8447904			|	"text zprávy jedna" | "text zprávy dva"	|
		|	"terka1611@seznam.cz"	|	"admin@test.cz"				|	"user@test.cz"			|	8447904			|	2933593			|	"text zprávy jedna" | "text zprávy dva"	|

	