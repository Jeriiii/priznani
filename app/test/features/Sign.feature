Feature: SignIn, SignUp

	Scenario: User can see SignIn
		Given I am on "/sign/in"
		Then I should see "Přihlášení"

	Scenario: User is loggin and he should not see Přihlášení
		Given I am signed in as "user@test.cz"
		And I am on "/sign/in"
		Then I should not see "Přihlášení"

	Scenario Outline: User sign in
		Given I am on "/sign/in"
		When I fill in "email" with "<email>"
		And I fill in "password" with "<pass>"
		And I press "login"
		Then I should see "Byl jste úspěšně přihlášen"
		
		Examples:
			| email		    | pass     |
			| user@test.cz  | testtest |
			| admin@test.cz | testtest |
		
	Scenario Outline: User not sign in
		Given I am on "/sign/in"
		When I fill in "email" with "<email>"
		And I fill in "password" with "<pass>"
		And I press "login"
		Then I am on "/sign/in"
		And I should not see "Byl jste úspěšně přihlášen"
		
		Examples:
			| email		    | pass            |
			| user@test.cz  | spatneheslo     |
			| admin@test.cz | spatneheslo     |

	Scenario Outline: User sign up
		Given I am on "/sign/registration"
		When I fill in "user_name" with "<name>"
		And I fill in "email" with "<email>"
		And I fill in "password" with "<pass>"
		And I fill in "passwordVerify" with "<passVer>"
		And I check "adult"
		And I check "agreement"
		And I press "send"
		Then I should see "Pro dokončení registrace prosím klikněte na odkaz zaslaný na Váš email."
		

		Examples:
			| name        | email              | pass        | passVer     |
			| testik12345 | testik12345@test.cz| testik12345 | testik12345 |
	