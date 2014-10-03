# language: cz
Feature: Upload Image to Competition
	
	Scenario Outline: User uploads an image to competition
		Given I am signed in as "user@test.cz"
		And I am on "/users-competitions/"
		When I follow "add-photo-button"
		And I should see "Jméno:"
		And I fill in "name" with "Test user"
		And I fill in "surname" with "Testus"
		And I fill in "phone" with "112567"
		And I attach the file "<image>" to "newCompImageFormImageFile0"
		And I fill in "newCompImageFormImageName0" with "Test image"
		And I fill in "newCompImageFormImageDescription0" with "Image for test the upload"
		And I press "_submit"
		Then I should see "Fotka byla přidaná. Nyní je ve frontě na schválení."

		Examples:
			| image |
			| profile_photo_woman.jpg |

	Scenario: Admin accepts the uploaded image
		Given I am signed in as "admin@test.cz"
		And I am on "/admin.accept-images/accept-competition-images"
		When I follow "Schválit"
		Then I should not see "Schválit"
		
    

   