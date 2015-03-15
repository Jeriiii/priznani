Feature: User can like photos and comments in competitions

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
	When I follow "Ano"
	Then I should not see "<comment>"
 
	Examples:
		| user			| comment		|
        | admin@test.cz	| Pěkná fotka!	|




   