Feature: Stream

	Scenario Outline: User Stream
		Given I am signed in as "<my_email>"
		And I am on "/"
		Then I should not see "<meItem>"
		And I should see "<item1>"
		And I should see "<item2>"
		And I should see "<item3>"
# 		And I should not see "<item4>"
# 		And I should not see "<item5>"
# 		And I should not see "<item6>"

		Examples:
		| my_email		  | meItem						| item1 | item2 | item3 |
		| <admin@test.cz> | Status uživatele Test Admin | Status uživatele Test User | Status uživatele Igor | Status uživatele Majka | 
# 		| <user@test.cz>  | Status uživatele Test User	| Status uživatele Test Admin | Status uživatele Párek s hořčicí | Status uživatele Lízalky | 
