Feature: Filling eshop form

	Scenario Outline: User fills out the form
		Given I am on "/eshop/game"
		When I fill in "name" with "<name>"
		And I fill in "surname" with "<surname>"
		And I fill in "email" with "<email>"
		And I fill in "phone" with "<phone>"
		And I check "<checkbox>"
		Then I press "frm-eshopGamesOrdersForm-submit"
		And I should see "<message>"

		Examples:
			| name			| surname		| email				| phone		| checkbox			| message	|
			# projde
			| Test			| User			| user@test.cz		| 123456789 | vasnivefantazie	| Aplikace objednávku NEODESLALA! Pokud potíže přetrvávají, prosím kontaktujte nás. |
			# chybí name
			|				| User			| user@test.cz		| 123456789 | vasnivefantazie	| Prosím, vyplňte Vaše jméno |
			# chybí surname
			| Test			|				| user@test.cz		| 123456789 | vasnivefantazie	| Prosím, vyplňte Vaše příjimení |
			# chybí email
			| Test			| User			|					| 123456789 | vasnivefantazie	| Prosím, vyplňte Váš email |	
			# chybný tvar emailu
			| Test			| User			| spatnyemail.cz	| 123456789 | vasnivefantazie	| Zadejte email ve správném tvaru např. vasemail@seznam.cz |
			# chybí phone
			| Test			| User			| user@test.cz		|			| vasnivefantazie	| Prosím, vyplňte Váš telefon |
			# chybí adresa
			| Test			| User			| user@test.cz		| 123456789	| print				| Prosím vyplňte Vaší adresu, kam Vám máme hru zaslat |

	Scenario Outline: Unchecked game
		Given I am on "/eshop/game"
		When I fill in "name" with "<name>"
		And I fill in "surname" with "<surname>"
		And I fill in "email" with "<email>"
		And I fill in "phone" with "<phone>"
		Then I press "frm-eshopGamesOrdersForm-submit"
		And I should see "<message>"

		Examples:
			| name			| surname		| email			| phone		| message							|
			# nezaškrtnutá hra
			| Test			| User			| user@test.cz	| 123456789	| Musíte vybrat alespoň jednu hru	|