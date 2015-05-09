Feature: Dating registration

	Scenario Outline: Registration of a couple 
		Given I am on "/dating-registration/"
		When I select "<type>" from "type"
		And I select "<day>" from "day"
		And I select "<month>" from "month"
		And I select "<year>" from "year"
		And I select "<day>" from "daySecond"
		And I select "<month>" from "monthSecond"
		And I select "<year>" from "yearSecond"
		And I select "Pár" from "type"
		And I fill in "<want_to_meet_men>" for "frm-firstRegForm-want_to_meet_men-1"
		And I press "send"

		Then I should be on "/dating-registration/second-reg-form"
		When I fill in "<email>" for "email"
		And I fill in "<user_name>" for "user_name"
		And I fill in "<password>" for "password"
		And I fill in "<passwordVerify>" for "passwordVerify"
		And I fill in "<first_sentence>" for "first_sentence"
		And I check "agreement"
		And I press "send"

		Then I should see "Partnerka"
		And I should be on "/dating-registration/third-reg-form"
		When I select "ženatý / vdaná" from "marital_state"
		And I select "bi" from "orientation"
		And I select "160 - 170 cm" from "tallness"
		And I select "normální" from "shape"
		And I select "A" from "bra_size"
		And I select "<hair_colour>" from "hair_colour"
		Then I should see "Partner"
		When I select "ženatý / vdaná" from "marital_stateSecond"
		And I select "bi" from "orientationSecond"
		And I select "160 - 170 cm" from "tallnessSecond"
		And I select "normální" from "shapeSecond"
		And I fill in "15" for "penis_lengthSecond"
		And I select "8cm-11cm" from "penis_widthSecond"
		And I press "send"

		Then I should see "Byli jste úspěšně zaregistrováni. Prosím potvrďte svůj email."
		And I should receive an email
		And I should see "byl jste úspěšně zaregistrován. Vaše přihlašovací údaje jsou" in last email
		And I follow the link from last email

		# po prvním kliknutí je uživatel automaticky přihlášen
		And I should be on "/"
		And I should see "<user_name>"
		And I should see "Potvrzení bylo úspěšné, systém vás automaticky přihlásil."
		Given I am on "/sign/out"
		
		# druhé kliknutí na odkaz v emailu už přehlazuje na přihlášení
		And I follow the link from last email
		And I should be on "/sign/in"
		When I fill in "<email>" for "signEmail"
		And I fill in "<password>" for "signPassword"
		And I press "login"
		Then I should see "Byl jste úspěšně přihlášen"
		And I should see "<user_name>"

		Examples:
			| day	| month	| year	| want_to_meet_men | type	| email				| user_name		| password		| passwordVerify	| first_sentence	|  hair_colour |
			| 1		| leden	| 1985	| 1 | Pár		|novakovi@test.cz	| Novákovi		| heslo123		| heslo123			| Vítej u Nováků	| hnědá |