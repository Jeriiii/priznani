Feature: Tests of friendship relation management

   Scenario: User's friendship request can be accepted
	    Given I am signed in as "user@test.cz"
		And I am on "/profil.show?id=5"
		And I press "frm-sendFriendRequest-requestMessageForm-send"
		Then I am signed in as "man@test.cz"
		And I am on "/friends/requests"
		Then I should see "Přijmout"
		Then I follow "Přijmout"
		And I am on "/profil.show?id=3"
		Then I should not see "ŽÁDOST O PŘÁTELSTVÍ"

   Scenario: User's friendship request can be denied
		When I recreate data in database
	    Given I am signed in as "user@test.cz"
		And I am on "/profil.show?id=5"
		And I press "frm-sendFriendRequest-requestMessageForm-send"
		Then I am signed in as "man@test.cz"
		And I am on "/friends/requests"
		Then I should see "Odmítnout"
		Then I follow "Odmítnout"
		And I am on "/profil.show?id=3"
		Then I should see "ŽÁDOST O PŘÁTELSTVÍ"



