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


	Scenario Outline: User sign up
		Given I am on "/sign/registration"
		When I fill in "user_name" with "<name>"
		And I fill in "email" with "<email>"
		And I fill in "password" with "<pass>"
		And I fill in "passwordVerify" with "<passVer>"
		And I check "adult"
		And I check "agreement"
		And I press "send"
		Then I should see "<message>"
		

		Examples:
			| name        | email              | pass        | passVer     | message |
			# projde a zaregistruje se
			| testik12345 | testik12345@test.cz| testik12345 | testik12345 | Pro dokončení registrace prosím klikněte na odkaz zaslaný na Váš email. |
			# nezaregistruje se
			| testik12345 | testik1@test.cz    | testik12345 | testik12345 | Tento nick už někdo používá.  |
			| testik123   | testik12345@test.cz| testik12345 | testik12345 | Tento email už někdo používá. |

	Scenario: After registration was send email
		Given I am on "/sign/registration"
		When I fill in "user_name" with "testik123456"
		And I fill in "email" with "testik123456@test.cz"
		And I fill in "password" with "mojeheslo"
		And I fill in "passwordVerify" with "mojeheslo"
		And I check "adult"
		And I check "agreement"
		And I press "send"
		Then I should receive an email
		And I should see "byl jste úspěšně zaregistrován. Vaše přihlašovací údaje jsou" in last email
		And I follow the link from last email
		And I should be on "/sign/in"

	
	