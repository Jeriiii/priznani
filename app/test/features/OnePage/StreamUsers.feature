Feature: Stream

	Scenario Outline: User Stream
		Given I am signed in as "<my_email>"
		And I am on "/"
		Then I should not see "<meItem>"
		#kamarád
		And I should see "<item1>" 
		#díky kategoriím
		And I should see "<item2>" 
		#díky kategoriím
		And I should see "<item3>" 
		#vyšachováním díky kategoriím
 		And I should not see "<item4>"

		Examples:
		| my_email		| meItem					  | item1						| item2							   | item3					| item4 |
		| admin@test.cz | Status uživatele Test Admin | Status uživatele Test User  | Status uživatele Párek s hořčicí | Status uživatele Majka | Status uživatele Žaludová dvojka |
# 		| user@test.cz  | Status uživatele Test User  | Status uživatele Test Admin | Status uživatele Párek s hořčicí | Status uživatele Lízalky | 
