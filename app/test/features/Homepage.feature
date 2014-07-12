Feature: Homepage.
  User is able to send confession.

	Scenario: User see confession form
		Given I am on "/"
		Then I should see "přiznání"

	Scenario: User see confession form
		Given I am on "/"
		Then I should see "přiznání"
		And I follow "Erotické hry"
		Then I should see "fantazie"

	Scenario: Testing new feature
		Given I am on "/eshop/game"
		Then I should see "fantazie"
		And It looks great

	Scenario: Testing sign in
		Given I am on "/sign/in"
		And I fill in "test@test.cz" for "email"
		And I fill in "hesloheslo" for "password"
		And I check "persistent"
		And I press "login"
		Then I should see "test"

	Scenario: Testing signed as
		Given I am on "/"
		Given I am signed in as "terka1612@seznam.cz"
		Then I look on the page
		
