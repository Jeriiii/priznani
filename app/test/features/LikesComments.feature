Feature: User can like photos and comments

Scenario Outline: Like photos
	Given I am signed in as "<user>"
	And I am on "/competition/list"
	And I follow "Test competition"
	And I should see "<title>"
	When I follow "Sexy (0)"
	Then I should see "Je sexy (1)"
	And I should not see "Sexy (0)"

	Examples:
		| user			| title		| 
        | admin@test.cz	| Test		|

Scenario Outline: Add, like and delete commentary
	Given I am signed in as "<user>"
	And I am on "/competition/list"
	And I follow "Test competition"
	Then I fill in "comment" with "<comment>" 
	When I press "_submit"
	Then I should see "×"
	When I follow "Líbí (0)"
	Then I should see "Líbí (1)"
	And I should not see "Líbí (O)"
	When I follow "×"
	Then I should not see "<comment>"
 
	Examples:
		| user			| comment		|
        | admin@test.cz	| Pěkná fotka!	|




   