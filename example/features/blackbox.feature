Feature: Getbootstrap smoke testing
  As an anonymous user
  I should be able to navigate through website pages using Nav buttons


  Scenario: Open home page and find text
    Given I am on "https://getbootstrap.com/"
    Then I should see text matching "Build fast, responsive sites with Bootstrap."
    When I follow "Get started"
    Then I should see text matching "Get started with Bootstrap"
    When I follow "Starter template"
    Then I should see text matching "Be sure to have your pages set up with the latest design and development standards"
