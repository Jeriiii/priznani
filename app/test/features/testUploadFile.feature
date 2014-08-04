#----------------------------------
# Empty Cucumber .feature file
#----------------------------------
    

  Feature: Confessions form

	Scenario:
		Given I am signed in as "user@test.cz"
		And I am on "/profil.show"
		When I attach the file "profile_photo_woman.jpg" to "imageFile0"
		And I press "send"
		Then I should see "Profilové foto bylo uloženo. Nyní je ve frontě na schválení."