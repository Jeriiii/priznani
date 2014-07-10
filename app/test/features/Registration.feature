Feature: Registration.
  User can register himself.



	Scenario: Testing sign in
		Given I am on "/sign/registration"
		And I fill in "auto@test.cz" for "email"
		And I fill in "tester" for "user_name"
		And I fill in "nejakeheslo" for "password"
		And I fill in "nejakeheslo" for "passwordVerify"
		And I check "adult"
		And I check "agreement"
		And I press "send"
		Then I look on the page
		Then I should see "Pro dokončení registrace prosím klikněte na odkaz zaslaný na Váš email."
