Feature: Homepage testing

   Scenario Outline: Links
	    Given I am signed in as "user@test.cz"
		And I am on "/"		
		When I follow "<link>"
		Then I should be on "<page>"

	  Examples:
		 | link				| page					|
		 | Editovat profil	| /profil.edit/			|
		 | Můj profil		| /profil.show/			|
		 | Moje galerie		| /profil.galleries/	|
		 | Vytvořit galerii	| /profil.galleries/user-gallery-new	|
		 | Pratelstvi		| /profil.edit/friend-requests	|
		 | Nastavit status	| /profil.edit/|

