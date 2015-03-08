Feature: Homepage testing

   Scenario Outline: Links
	    Given I am signed in as "user@test.cz"
		And I am on "/"		
		When I follow "<link>"
		Then I should be on "<page>"

	  Examples:
		 | link				| page					|
		 | EDITOVAT PROFIL	| /profil.edit/			|
		 | MŮJ PROFIL		| /profil.show/			|
		 | MOJE GALERIE		| /profil.galleries/	|
		 | VYTVOŘIT GALERII	| /profil.galleries/user-gallery-new	|

