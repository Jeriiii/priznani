Feature: User can see recent conversations
# user@test.cz coded id: 2933593
# admin@test.cz coded id: 8447904
# terka1611@seznam.cz coded id: 75624012
# P.kukral@seznam.cz coded id: 9904961
	Background:
		Given I am on "/"

	Scenario Outline: Seeing messages from someone
		Given I am signed in as <sender>
		And I send chat message <message> to "<receiverCodedId>"
		And I am on "/sign/out"
		Given I am signed in as <receiver>
		And I am testing ajax
		And I am on "/?do=chat-conversationList-load&chat-conversationList-offset=0&chat-conversationList-limit=5"
		Then the response should contain "<senderName>"
		And the response should contain <message>

		Examples:
		|	sender					|	receiver			|	receiverCodedId	|	senderName	|	message					|
		|	"user@test.cz"			|	"admin@test.cz"		|	8447904			|	Test User	|	"zpráva 123 ### []ččěř"	|


	Scenario Outline: Seeing messages from myself
		Given I am signed in as <sender>
		And I send chat message <message> to "<receiverCodedId>"
		Then I am testing ajax
		And I am on "/?do=chat-conversationList-load&chat-conversationList-offset=0&chat-conversationList-limit=5"
		Then the response should contain "<receiverName>"
		And the response should contain <message>

		Examples:
		|	sender					|	receiver			|	receiverCodedId	|	receiverName	|	message					|
		|	"user@test.cz"			|	"admin@test.cz"		|	8447904			|	Test Admin		|	"zpráva 123 ### []ččěř"	|

	Scenario Outline: Not seeing messages from someone when offset is bigger
		Given I am signed in as <sender>
		And I send chat message <message> to "<receiverCodedId>"
		And I am on "/sign/out"
		Given I am signed in as <receiver>
		And I am testing ajax
		And I am on "/?do=chat-conversationList-load&chat-conversationList-offset=10&chat-conversationList-limit=5"
		Then the response should not contain "<senderName>"
		And the response should not contain <message>

		Examples:
		|	sender					|	receiver			|	receiverCodedId	|	senderName	|	message					|
		|	"user@test.cz"			|	"admin@test.cz"		|	8447904			|	Test User	|	"zpráva 123 ### []ččěř"	|


		