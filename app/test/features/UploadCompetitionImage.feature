# language: cz
Feature: Upload Image to Competition
	
	Scenario Outline: User uploads an image to competition
		Given I am signed in as "user@test.cz"
		Given I am on "/"
		Then I should see "přiznání"
		Then I go to "/users-competitions/"
		Then I follow "add-photo-button"
		And I should see "Jméno:"
		Then I fill in "name" with "Test user"
		Then I fill in "surname" with "Testus"
		Then I fill in "phone" with "112567"
		Then I attach the file "<image>" to "imageFile0"
		Then I fill in "imageName0" with "Test image"
		Then I fill in "imageDescription0" with "Image for test the upload"
		Then I check "man"
		Then I press "_submit"
		And I should see "Fotka byla přidaná. Nyní je ve frontě na schválení."

		Examples:
			| image |
			| profile_photo_woman.jpg |
		
    

   