# language: cz
Feature: Accept Image from competition
	
	Scenario: Admin accepts the uploaded image
		Given I am signed in as "admin@test.cz"
		And I go to "/admin.accept-images/accept-competition-images"
		Then I should see "Soutěžní obrázky"
		Then I follow "Schválit"
		And I should not see "Schválit"
		
		
    

   