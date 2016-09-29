<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  protected $screenshot_dir = '/tmp';

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct($parameters) {
    $this->parameters = $parameters;
    if (isset($parameters['screenshot_dir'])) {
      $this->screenshot_dir = $parameters['screenshot_dir'];
    }
  }

  /**
   * Take screenshot when step fails. Works only with Selenium2Driver.
   * Screenshot is saved at [Date]/[Feature]/[Scenario]/[Step].jpg
   *  @AfterStep
   */
  public function after(Behat\Behat\Hook\Scope\AfterStepScope $scope) {
    if ($scope->getTestResult()->getResultCode() === 99) {
      $driver = $this->getSession()->getDriver();
      if ($driver instanceof Behat\Mink\Driver\Selenium2Driver) {
        $fileName = date('d-m-y') . '-' . uniqid() . '.png';
        $this->saveScreenshot($fileName, $this->screenshot_dir);
        print 'Screenshot at: '.$this->screenshot_dir.'/' . $fileName;
      }
    }
  }

  /**
   * @Then /^I should see "([^"]*)" in the code$/
   */
  public function inspectCode($code){
    if (!$this->assertSession()->statusCodeEquals($code)) {
      throw new Exception("There is no such value in code.");
    }
    return $this->assertSession()->statusCodeEquals($code);
  }

  /**
   * Filling the field with parameter using jQuery . Some forms can't be filled using other functions.
   *
   * @When /^(?:|I )fill the field "(?P<field>(?:[^"]|\\")*)" with value "(?P<value>(?:[^"]|\\")*)" using jQuery$/
   */
  public function checkFieldValue($id, $value)
  {
    $response = $this->getSession()->getDriver()->evaluateScript(
      "return jQuery('#" . $id . "').val();"
    );
    if ($response != $value ) {
      throw new Exception("Value doesn't match");
    }
  }

  /**
   * @Then /^I execute jQuery click on selector "([^"]*)"$/
   */
  public function executeJQueryForSelector($arg) {

    $jQ = "return jQuery('".$arg."').click();";
    #$this->getSession()->getDriver()->evaluateScript($jQ);

    try {
      $this->getSession()->getDriver()->evaluateScript($jQ);
    }
    catch(Exception $e) {
      throw new \Exception("Selector isn't valid");
    }

  }

  /**
   * Setting custom size of the screen using width and height parameters
   *
   * @Given /^the custom size is "([^"]*)" by "([^"]*)"$/
   */
  public function theCustomSizeIs($width, $height)
  {
    $this->getSession()->resizeWindow($width, $height, 'current');
  }

  /**
   * Setting screen size to 1400x900 (desktop)
   *
   * @Given /^the size is desktop/
   */
  public function theSizeIsDesktop()
  {
    $this->getSession()->resizeWindow(1400, 900, 'current');
  }

  /**
   * Setting screen size to 1024x900 (tablet landscape)
   *
   * @Given /^the size is tablet landscape/
   */
  public function theSizeIsTabletLandscape()
  {
    $this->getSession()->resizeWindow(1024, 900, 'current');
  }

  /**
   * Setting screen size to 768x900 (tablet portrait)
   *
   * @Given /^the size is tablet portrait/
   */
  public function theSizeIsTabletPortrait()
  {
    $this->getSession()->resizeWindow(768, 900, 'current');
  }

  /**
   * Setting screen size to 640x900 (mobile landscape)
   *
   * @Given /^the size is mobile landscape/
   */
  public function theSizeIsMobileLandscape()
  {
    $this->getSession()->resizeWindow(640, 900, 'current');
  }

  /**
   * Setting screen size to 320x900 (mobile portrait)
   *
   * @Given /^the size is mobile portrait/
   */
  public function theSizeIsMobilePortrait()
  {
    $this->getSession()->resizeWindow(320, 900, 'current');
  }

  /**
   * Check if the port is 443(https) or 80(http) / secure or not.
   *
   * @Then /^the page is secure$/
   */
  public function thePageIsSecure()
  {
    $current_url = $this->getSession()->getCurrentUrl();
    if(strpos($current_url, 'https') === false) {
      throw new Exception('Page is not using SSL and is not Secure');
    }
  }

  /**
   * This will cause a 3 second delay
   *
   * @Given /^I wait$/
   */
  public function iWait() {
    sleep(3);
  }

  /**
   * Hover over an item using id|name|class
   *
   * @Given /^I hover over the item "([^"]*)"$/
   */
  public function iHoverOverTheItem($arg1)
  {
    if($this->getSession()->getPage()->find('css', $arg1)) {
      $this->getSession()->getPage()->find('css', $arg1)->mouseOver();
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * See if Element has style eg p.padL8 has style font-size= 12px
   *
   * @Then /^the element "([^"]*)" should have style "([^"]*)"$/
   */
  public function theElementShouldHaveStyle($arg1, $arg2)
  {
    $element = $this->getSession()->getPage()->find('css', $arg1);
    if($element) {
      if(strpos($element->getAttribute('style'), $arg2) === FALSE) {
        throw new Exception('Style not found');
      }
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * Look for a cookie
   *
   * @Then /^I should see cookie "([^"]*)"$/
   */
  public function iShouldSeeCookie($cookie_name) {
    if($this->getSession()->getCookie('welcome_info_name') == $cookie_name) {
      return TRUE;
    } else {
      throw new Exception('Cookie not found');
    }
  }

  /**
   * Setting the cookie with particular value
   *
   * @Then /^I set cookie "([^"]*)" with value "([^"]*)"$/
   */
  public function iSetCookieWithValue($cookie_name, $value) {
    $this->getSession()->setCookie($cookie_name, $value);
  }

  /**
   * Check if the cookie isn't presented
   *
   * @Then /^I should not see cookie "([^"]*)"$/
   */
  public function iShouldNotSeeCookie($cookie_name) {
    if($this->getSession()->getCookie('welcome_info_name') == $cookie_name) {
      throw new Exception('Cookie not found');
    }
  }

  /**
   * Destroy cookies. Resetting the session
   *
   * @Then /^I reset the session$/
   */
  public function iDestroyMyCookies() {
    $this->getSession()->reset();
  }

  /**
   * See if element is visible
   *
   * @Then /^element "([^"]*)" is visible$/
   */
  public function elementIsVisible($arg) {
    $el = $this->getSession()->getPage()->find('css', $arg);
    if($el) {
      if(!$el->isVisible()){
        throw new Exception('Element is not visible');
      }
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * See if element is not visible
   *
   * @Then /^element "([^"]*)" is not visible$/
   */
  public function elementIsNotVisible($arg) {
    $el = $this->getSession()->getPage()->find('css', $arg);
    if($el) {
      if($el->isVisible()){
        throw new Exception('Element is visible');
      }
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * Set a waiting time in seconds
   *
   * @Given /^I wait for "([^"]*)" seconds$/
   */
  public function iWaitForSeconds($arg1) {
    sleep($arg1);
  }

  /**
   * Switching to iFrame with Name(don't use id, title etc. ONLY NAME)
   *
   * @Given /I switch to iFrame named "([^"]*)"$/
   */
  public function iSwitchToIframeNamed($arg1) {
    $this->getSession()->switchToIFrame($arg1);
  }

  /**
   * Switching to Window with Name(don't use id, title etc. ONLY NAME)
   *
   * @Given /^I switch to window named "([^"]*)"$/
   */
  public function iSwitchPreviousToWindow($arg1)
  {
    $this->getSession()->switchToWindow($arg1);
  }

  /**
   * Switching to second window
   *
   * @Given /^I switch to the second window$/
   */
  public function iSwitchToSecondWindow()
  {
    $windowNames = $this->getSession()->getWindowNames();
    if (count($windowNames) > 1) {
      $this->getSession()->switchToWindow($windowNames[1]);
    }
  }

  /**
   * Click an element with an onclick handler
   *
   * @Given /^I click on element which has onclick handler located at "([^"]*)"$/
   */
  public function iClickOnElementWhichHasOnclickHandlerLocatedAt($item)
  {
    $node = $this->getSession()->getPage()->find('css', $item);
    if($node) {
      $this->getSession()->wait(3000,
        "jQuery('{$item}').trigger('click')"
      );
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * Y would be the way to up and down the page. A good default for X is 0
   *
   * @Given /^I scroll to x "([^"]*)" y "([^"]*)" coordinates of page$/
   */
  public function iScrollToXYCoordinatesOfPage($arg1, $arg2) {
    $function = "(function(){
              window.scrollTo($arg1, $arg2);
            })()";
    try {
      $this->getSession()->executeScript($function);
    }
    catch(Exception $e) {
      throw new \Exception("ScrollIntoView failed");
    }
  }

  /**
   * Check existence of JavaScript variable on loaded page.
   *
   * @Then /^I should see "([^"]*)" Js variable$/
   */
  public function iShouldSeeJsVariable($variable_name) {

    $javascript = <<<EOT
return (typeof $variable_name === "undefined") ? 0 : 1;
EOT;

    // Execute javascript and return variable value or undefined
    // if javascript variable not exists or equals to undefined.
    $variable_value_exist = $this->getSession()->evaluateScript($javascript);

    if (empty($variable_value_exist)) {
      throw new Exception('JavaScript variable doesn\'t exists or undefined.');
    }
  }

  /**
   * Check NON existence of JavaScript variable on loaded page.
   *
   * @Then /^I should not see "([^"]*)" Js variable$/
   */
  public function iShouldNotSeeJsVariable($variable_name) {

    $javascript = <<<EOT
return (typeof $variable_name != $variable_value_exist) ? 0 : 1;
EOT;

    // Execute javascript and return variable value or undefined
    // if javascript variable not exists or equals to undefined.
    $variable_value_exist = $this->getSession()->evaluateScript($javascript);

    if (empty($variable_value_exist)) {
      throw new Exception('JavaScript variable match.');
    }
  }

  /**
   * @Then /^I should see "([^"]*)" in the "([^"]*)" Js variable$/
   */
  public function iShouldSeeInTheJsVariable($variable_value, $variable_name) {

    $javascript = <<<EOT
return (typeof $variable_name === "undefined") ? "" : $variable_name;
EOT;

    // Execute javascript and return variable value or undefined
    // if javascript variable not exists or equals to undefined.
    $variable_value_exist = $this->getSession()->evaluateScript($javascript);

    if ($variable_value_exist === "undefined") {
      throw new Exception('JavaScript variable doesn\'t exists or undefined.');
    }

    if ($variable_value != $variable_value_exist) {
      throw new Exception('JavaScript variable value doesn\'t match.');
    }
  }

  /**
   * Scrolling to the particular element(arg1 - Nav menu selector, arg2 - element's selector to scroll to)
   *
   * @Given /^I scroll to element "([^"]*)" "([^"]*)"$/
   */
  public function iScrollToElement($arg1, $arg2) {
    $function = <<<JS
     var headerHeight = jQuery('$arg2').outerHeight(true),
          scrollBlock = jQuery('$arg1').offset().top;
 jQuery('body, html').scrollTo(scrollBlock - headerHeight);

JS;
    try {
      $this->getSession()->executeScript($function);
    }
    catch(Exception $e) {
      throw new \Exception("ScrollIntoElement failed");
    }
  }

  /**
   * Clicking the element using selector (works only if element is visible)
   *
   * @When /^I click the element with selector "([^"]*)"$/
   */
  public function iClickTheElement($arg)
  {
    $node = $this->getSession()->getPage()->find('css', $arg);
    if($node) {
      $this->getSession()->getPage()->find('css', $arg)->click();
    } else {
      throw new Exception('Element not found');
    }
  }

  /**
   * Verifying that element has particular class
   *
   * @When /^element "(?P<field>(?:[^"]|\\")*)" should have class "(?P<value>(?:[^"]|\\")*)"$/
   */
  public function checkElementClass($arg, $class)
  {
    $response = $this->getSession()->getDriver()->evaluateScript(
      "           
            return (function () {
            var element = jQuery('" . $arg . "');
            if (element.length > 0) {
              if (element.hasClass('" . $class . "')){
                return 'Ok';
              }
              
              else {
                return 'Class doesn\'t match';
              }
            }
            else {
              return 'Selector wasn\'t found';
            }
            })();
            "
    );
    if ($response != 'Ok') {
      throw new Exception($response);
    }
  }
}
