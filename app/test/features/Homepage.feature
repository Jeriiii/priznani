Feature: Homepage.
  User is able to send confession.

  Scenario: User see confession form
    Given I am on "/"
    Then I should see "sex"

  Scenario: Testing new feature
    Given I am on "/eshop/game"
    Then I should see "fantazie"
    And It looks great
