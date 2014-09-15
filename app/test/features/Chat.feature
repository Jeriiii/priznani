Feature: Chat messages ajax testing

	Scenario:
		Given I am testing ajax
		Given I am signed in as "user@test.cz"
		Given I am on "/?do=chat-communicator-refreshMessages&last=13"
		Then There should be "Není to špatný." in response
