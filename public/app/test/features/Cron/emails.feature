Feature: Emails sended by cron

	Scenario: Server can view an emails to send in JSON
		Given I am on "/cron-email/mail-to-json?userName=mailuser&userPassword=a10b06001"
		Then Is in JSON
   