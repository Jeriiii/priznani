Feature: Homepage Fail test.
  This test should not pass!

  Scenario: User see confession form
    Given I am on "/"
    Then I should not see "sex"
