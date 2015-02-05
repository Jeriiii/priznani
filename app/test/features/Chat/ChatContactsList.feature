Feature: User can see correct contact list in chat
# user@test.cz coded id: 2933593
# admin@test.cz coded id: 8447904
# terka1611@seznam.cz coded id: 75624012
# P.kukral@seznam.cz coded id: 9904961
	Background:
		Given I am on "/"

	Scenario Outline: Seeing all of my friends
		Given I am signed in as <user>
		Then I should see <friend> in the "#contacts" element
		And the response should contain "<friendCodedId>"

		Examples:
		|	user			|	friend			|	friendCodedId	|
		|	"user@test.cz"	|	"Test Admin"	|	8447904			|
		|	"user@test.cz"	|	"Jerry"			|	9904961			|

	Scenario Outline: Not seeing my not-friends or myself
		Given I am signed in as <user>
		Then I should not see <notFriend> in the "#contacts" element
		And I should not see "<notFriendCodedId>" in the "#contacts" element
		And I should not see an "#contacts  li[data-title='<username>']" element

		Examples:
		|	user			|	username			|	notFriend			|	notFriendCodedId	|
		|	"admin@test.cz"	|	Test Admin			|	"Jerry"				|	9904961				|

		
