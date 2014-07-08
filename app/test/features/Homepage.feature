Feature: Homepage.
  User is able to send confession.

	Scenario: User see confession form
		Given I am on "/"
		Then I should see "přiznání"

	Scenario: Testing new feature
		Given I am on "/eshop/game"
		Then I should see "fantazie"
		And It looks great

	Scenario: Testing sign in
		Given I am on "/sign/in"
		And I fill in "p.kukral@seznam.cz" for "email"
		And I fill in "hesloheslo" for "password"
		And I check "persistent"
		And I press "login"
		Then I look on the page

	Scenario: Testing signed as
		Given I am on "/"
		And I am signed in as "terka1612@seznam.cz"
		Then I should see "Ter"
