Feature: Blokování uživatele na profilu.
	Scenario: Blokování a odblokování uživatele na profilu
		Given I am signed in as "user@test.cz"
		And I am on "/profil.show/?id=4"
		Then I should see "Blokovat uživatele"
		And I should not see "Odblokovat"
		#zablokuji uživatele
		And I go to "/profil.show/?blockUserID=4&id=4&do=blockUser"
		And I should see "Uživatel byl zablokován"
		And I should not see "Blokovat uživatele"
		And I should see "Odblokovat"
		#odblokuji uživatele
		And I go to "/profil.show/?unblockUserID=4&id=4&do=unblockUser"
		And I should see "Uživatel byl odblokován"
		Then I should see "Blokovat uživatele"
		And I should not see "Odblokovat"
		



    

   