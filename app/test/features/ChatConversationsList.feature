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
		Then I should see "<senderName>" in the "#conversations li[data-title='<senderName>']" element
		And I should see <message> in the "#conversations li[data-title='<senderName>']" element

		Examples:
		|	sender					|	receiver			|	receiverCodedId	|	senderName	|	message					|
		|	"user@test.cz"			|	"admin@test.cz"		|	8447904			|	Test User	|	"zpráva 123 ### []ččěř"	|


Scenario Outline: Seeing messages from myself
		Given I am signed in as <sender>
		And I send chat message <message> to "<receiverCodedId>"
		Then I reload the page
		And I should see "<receiverName>" in the "#conversations li[data-title='<receiverName>']" element
		And I should see <message> in the "#conversations li[data-title='<receiverName>']" element

		Examples:
		|	sender					|	receiver			|	receiverCodedId	|	receiverName	|	message					|
		|	"user@test.cz"			|	"admin@test.cz"		|	8447904			|	Test Admin		|	"zpráva 123 ### []ččěř"	|


		