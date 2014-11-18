Feature: Friendship

Scenario Outline: If user has friend, it should be visible on his and his friend's profile
	Given I am signed in as "<user>"
	And I am on "/"
	And I should see "<friend>"
	When I follow "<friend>"
	Then I should see "<friend>"
	And I should see "<user_name>"
 
	Examples:
		| user			| user_name | friend		|
        | admin@test.cz	| Test Admin	| Test User		|
		| user@test.cz	| Test User	| Test Admin		|


   