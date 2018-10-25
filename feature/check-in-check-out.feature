Feature: Check-in and check-out

  Scenario: Checking in twice leads to a check-in anomaly
    Given a building was registered
    And "Paul" has checked into the building
    When "Paul" checks into the building
    Then "Paul" should have been checked into the building
    And a check-in anomaly should have been detected for "Paul"

