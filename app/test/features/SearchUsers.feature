Feature: Search users

	Scenario Outline: Best search users
		Given I am signed in as "<my_email>"
		And I am on "/search.search/"
		#kamarád
		Then I should see "<user1>"
		#díky kategoriím
		And I should see "<user2>"
		#díky kategoriím
		And I should see "<user3>"
		#vyšachováním díky kategoriím
		And I should not see "<user4>"

		Examples:
		| my_email		| user1	  | user2			| user3	| user4			 |
		| admin@test.cz | Test User  | Párek s hořčicí | Majka | Žaludová dvojka |
