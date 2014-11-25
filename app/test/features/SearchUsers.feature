Feature: Search users

	Scenario Outline: Best search users
		Given I am signed in as "<my_email>"
		And I am on "/search.search/"
		Then I should not see "<me>"
		And I should see "<user1>"
		And I should see "<user2>"
		And I should see "<user3>"
		And I should not see "<user4>"
		And I should not see "<user5>"
		And I should not see "<user6>"

		Examples:
		| my_email		  | me          | user1 | user2 | user3 | user4 | user5 | user6 |
		| <admin@test.cz> | Test Admin  | | | | | | |
		| <user@test.cz>  | Test User	| | | | | | |
