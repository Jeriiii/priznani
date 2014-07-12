Feature: ForgottenPassword.
  User can demand new password for his old one.



	Scenario: Testing sign in
		Given I am on "/sign/forgotten-pass"
		And I fill in "p.kukral2@seznam.cz" for "email"
		And I press "send"
		And I should see "Heslo bylo odesláno emailem"
