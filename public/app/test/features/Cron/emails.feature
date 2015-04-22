Feature: Emails sended by cron

	Scenario: Server can view an emails to send in JSON
		#emaily k odeslání
		Given I am on "/cron-email/mail-to-json?userName=mailuser&userPassword=a10b06001"
		And Is not empty JSON
		
		#emaily jsou označeny jako odeslané
		Given I am on "/cron-email/mail-is-sended?userName=mailuser&userPassword=a10b06001"
		Then I should see "Oznámení byly označeny jako odeslané"

		#emaily se odeslaly = již tu není žádný email k odeslání
		Given I am on "/cron-email/mail-to-json?userName=mailuser&userPassword=a10b06001"
		Then Is empty JSON
   