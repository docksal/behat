@javascript
Feature: Getbootstrap smoke testing
  As an anonymous user
  I should be able to navigate through website pages using Nav buttons


  Scenario: Open home page and find text
    Given I am on "http://getbootstrap.com/"
    #And the size is desktop
    Then I should see text matching "Bootstrap is the most popular HTML, CSS, and JS framework for developing responsive, mobile first projects on the web."
    When I follow "Getting started"
    Then I should see text matching "Getting started"
    When I follow "CSS"
    Then I should see text matching "Global CSS settings, fundamental HTML elements styled and enhanced with extensible classes, and an advanced grid system."
