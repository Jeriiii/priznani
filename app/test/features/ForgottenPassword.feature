Feature: ForgottenPassword.
  User can demand new password for his old one.



	Scenario: Testing sign in
		Given I am on "/sign/forgotten-pass"
		And I fill in "p.kukral2@seznam.cz" for "email"
		And I press "send"
		#Then I should receive an email
		And I should see "Heslo bylo odesláno emailem"

	Scenario: Testing sign in
		Given I am on "/sign/forgotten-pass"
		And I fill in "p.kukral2@seznam.cz" for "email"
		And I press "send"
		Then I should receive an email
		And I should see "p.kukral2@seznam.cz" in last email
		#When I follow the link from last email
		Then I should not receive another email
		And I should see "Heslo bylo odesláno emailem"
