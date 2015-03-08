Feature: User can like photos and comments in gallery

Scenario Outline: Like photos
	Given I am signed in as "<user>"
	And I am on "/profil.galleries/image?imageID=5&galleryID=3"
	And I should see "<title>"
	When I follow "Sexy (0)"
	Then I should see "Je sexy (1)"
	And I should not see "Sexy (0)"

	Examples:
		| user			| title		| 
        | admin@test.cz	| Test jména		|

   