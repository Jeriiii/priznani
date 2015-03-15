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
		When I fill in "signEmail" with "<email>"
		And I fill in "signPassword" with "<pass>"
		And I press "login"
		Then I should see "<message>"
		
		Examples:
			| email		           | pass         | message					|
			# přihlásíte se
			| user@test.cz         | testtest     | Byl jste úspěšně přihlášen |
			| admin@test.cz		   | testtest     | Byl jste úspěšně přihlášen |
			# nepřihlásíte se
			| spatnyemail@test.cz  | testtest     | Neplatné uživatelské jméno nebo heslo. |
			| user@test.cz         | spatne heslo | Neplatné uživatelské jméno nebo heslo. |
	