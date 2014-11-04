Feature: Advanced search

Scenario Outline: Searching for test users
	Given I am on "/search.search/advanced"
	And I fill in "<age_from>" for "age_from" 
	And I fill in "<age_to>" for "age_to"
	And I select "<sex>" from "sex"
	And I select "<orientation>" from "orientation"
	And I select "<shape>" from "shape"
	And I select "<hair_color>" from "hair_color"
	And I select "<tall_from>" from "tallness_from"
	And I select "<tall_to>" from "tallness_to"
	And I select "<drink>" from "drink"
	And I select "<smoke>" from "smoke"
	And I select "<marital>" from "marital_state"
	And I select "<grad>" from "graduation"
	And I check "women"
	And I check "women_couple"
	And I check "threesome"
	And I check "group"
	And I check "bdsm"
	And I check "oral"
	And I check "sex_massage"
	And I check "petting"
	And I check "deepthroat"
	And I press "search"
	Then I should see "<found>"
	And I should not see "<not_found>"
	And I should not see "<not_found2>"
	And I should not see "<not_found3>"
	When I follow "<found>"
	Then I should see "Profil uživatele <found>"


Examples:	
| age_from	| age_to	| sex	| orientation	| shape			| hair_color	| tall_from	| tall_to	| drink | smoke			| marital	| grad		| found			| not_found		| not_found2	| not_found3	|
| 28		| 28		| žena	| hetero		| plnoštíhlá	| blond			| 160		| 170		| ne	| často			| volný		| střední	| Test Admin		| Test User		| Jerry			| Štěpán			|
| 26		| 26		| muž	| hetero		| plnoštíhlá	| hnědá			| 180		| 190		| často	| příležitostně | volný		| vysoké	| Test User		| Test Admin		| Jancatest		| Jana			|


#test, zda se uživatel (ne)vyhledá při drobné změně praktik

Scenario Outline: Testing practics
	Given I am on "/search.search/advanced"
	And I check "group"
	And I check "bdsm"
	And I check "oral"
	And I check "sex_massage"
	And I check "deepthroat"
	And I check "<choice1>"
	And I press "search"
	Then I should see "<found>"
	And I should not see "<not_found>"
	When I follow "<found>"
	Then I should see "Profil uživatele <found>"
	
 
Examples:
| choice1		| found			| not_found |
# Test User se vyhledá
| petting		| Test User		| Jancatest |
# Test User se již nesmí objevit
| piss			| Test Admin		| Test User |

