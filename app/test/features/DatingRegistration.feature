Feature: Dating registration

	Scenario:
		Given I am on "/install/test-data"

	Scenario Outline: Registration of a couple 
		Given I am on "/dating-registration/"
		When I select "<day>" from "day"
		And I select "<month>" from "month"
		And I select "<year>" from "year"
		And I select "<property>" from "user_property"
		And I select "Pár" from "user_property"
		And I check "want_to_meet_couple"
		And I press "send"
		Then I should be on "/dating-registration/second-reg-form"
		When I fill in "<email>" for "email"
		And I fill in "<user_name>" for "user_name"
		And I fill in "<password>" for "password"
		And I fill in "<passwordVerify>" for "passwordVerify"
		And I fill in "<first_sentence>" for "first_sentence"
		And I fill in "<about_me>" for "about_me"
		And I fill in "<city>" for "city"
		And I press "send"
		Then I should be on "/dating-registration/pre-third-reg-form"
		When I select "ženatý / vdaná" from "marital_state"
		And I select "bi" from "orientation"
		And I select "160 - 170 cm" from "tallness"
		And I select "normální" from "shape"
		And I select "ne" from "smoke"
		And I select "ne" from "drink"
		And I select "střední" from "graduation"
		And I select "A" from "bra_size"
		And I fill in "Hnědá" for "hair_colour"
		And I press "send"
		Then I should see "Zaregistrujte partnera"
		When I select "ženatý / vdaná" from "marital_state"
		And I select "bi" from "orientation"
		And I select "160 - 170 cm" from "tallness"
		And I select "normální" from "shape"
		And I select "ne" from "smoke"
		And I select "ne" from "drink"
		And I select "střední" from "graduation"
		And I select "střední" from "penis_length"
		And I select "střední" from "penis_width"
		And I fill in "35" for "age" 
		And I press "send"
		Then I should see "Byli jste úspěšně zaregistrováni. Prosím potvrďte svůj email."
		And I should receive an email
		And I should see "byl jste úspěšně zaregistrován. Vaše přihlašovací údaje jsou" in last email
		And I follow the link from last email
		And I should be on "/sign/in"
		And I should see "Potvrzení bylo úspěšné, nyní se můžete přihlásit."
		When I fill in "<email>" for "email"
		And I fill in "<password>" for "password"
		And I press "login"
		Then I should see "Byl jste úspěšně přihlášen"
		And I should see "Novákovi"

		Examples:
			| day	| month	| year	| property	| email				| user_name		| password		| passwordVerify	| first_sentence	| about_me		| city							 | 
			| 1		| leden	| 1985	|Pár		|novakovi@test.cz	| Novákovi		| heslo123		| heslo123			| Vítej u Nováků	| Jsme Novákovi	| Blatec, Olomouc, Olomoucký kraj | 