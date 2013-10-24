<?php

/* !
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://{$_SERVER["SERVER_NAME"]}/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Presentation\Validation;

use \MovLib\Presentation\Validation\URL;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class URLTest extends \MovLib\TestCase {

  public static function dataProviderAllInvalid() {
    return array_merge(
      self::dataProviderMalformed(), self::dataProviderMalformedSchemeAndOrHost(), self::dataProviderIllegalParts()
    );
  }

  public static function dataProviderIllegalParts() {
    return [
      [ "http://{$_SERVER["SERVER_NAME"]}:80" ],
      [ "http://admin@{$_SERVER["SERVER_NAME"]}/" ],
      [ "http://admin:1234@{$_SERVER["SERVER_NAME"]}/" ],
      [ "http://admin:1234@{$_SERVER["SERVER_NAME"]}:1234/" ],
    ];
  }

  public static function dataProviderMalformed() {
    // Not that easy to find test cases that are seriously malformed, the following won't go through the empty() check,
    // but I wasn't able to find a single string that would cause parse_url() to fail, it always extracts something.
    return [
      [ "" ],
      [ "\n" ],
      [ "    " ],
    ];
  }

  public static function dataProviderMalformedSchemeAndOrHost() {
    return [
      [ "系" ],
      [ "MovLib" ],
      [ "http://movlib.123" ],
      [ "{$_SERVER["SERVER_NAME"]}" ],
      [ "//{$_SERVER["SERVER_NAME"]}" ],
      [ "ftp://{$_SERVER["SERVER_NAME"]}/" ],
      [ "{$_SERVER["SERVER_NAME"]}/foo/bar" ],
      [ "ldap://{$_SERVER["SERVER_NAME"]}/" ],
      [ "//{$_SERVER["SERVER_NAME"]}/foo/bar" ],
      [ "mailto:user@{$_SERVER["SERVER_NAME"]}" ],
    ];
  }

  public static function dataProviderReachability() {
    return [
      [ "http://non-existent-host.com/" ],
      [ "https://{$_SERVER["SERVER_NAME"]}/non/existent/path" ],
    ];
  }

  public static function dataProviderValid() {
    return [
      [ "http://{$_SERVER["SERVER_NAME"]}/", "http://{$_SERVER["SERVER_NAME"]}" ],
      [ "http://{$_SERVER["SERVER_NAME"]}/", "http://{$_SERVER["SERVER_NAME"]}/" ],
      [ "https://{$_SERVER["SERVER_NAME"]}/", "https://{$_SERVER["SERVER_NAME"]}" ],
      [ "https://{$_SERVER["SERVER_NAME"]}/", "https://{$_SERVER["SERVER_NAME"]}/" ],
      [ "http://wikipedia.org/", "http://wikipedia.org" ],
      [ "https://wikipedia.org/", "https://wikipedia.org" ],
      [ "http://{$_SERVER["SERVER_NAME"]}/users/login", "http://{$_SERVER["SERVER_NAME"]}/users/login" ],
      [ "http://www.kawaguchi.science.museum/", "http://www.kawaguchi.science.museum/" ],
      [ "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB", "https://ja.wikipedia.org/wiki/Unix系" ],
      [ "https://en.wikipedia.org/wiki//dev/random", "https://en.wikipedia.org/wiki//dev/random" ],
      [ "http://www.youtube.com/watch?v=5gUKvmOEGCU", "http://www.youtube.com/watch?v=5gUKvmOEGCU" ],
      [ "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB", "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB" ],
      [ "http://{$_SERVER["SERVER_NAME"]}/users/registration?token=1234567890", "http://{$_SERVER["SERVER_NAME"]}/users/registration?token=1234567890" ],
      [
        "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Url.php/function/Url%3A%3AisValid/8",
        "https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Url.php/function/Url%3A%3AisValid/8"
      ],
    ];
  }

  public function setUp() {
    static $defaultDomain = null;
    if (!$defaultDomain) {
      $defaultDomain = $GLOBALS["movlib"]["default_domain"];
    }
    $GLOBALS["movlib"]["default_domain"] = $defaultDomain;
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URL::validate
   * @dataProvider dataProviderIllegalParts
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Validation\URL::E_ILLEGAL_PARTS
   */
  public function testIllegalParts($url) {
    (new URL($url))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URL::validate
   * @dataProvider dataProviderMalformed
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Validation\URL::E_MALFORMED
   */
  public function testMalformed($url) {
    (new URL($url))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URLvalidate
   * @dataProvider dataProviderMalformedSchemeAndOrHost
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Validation\URL::E_SCHEME_OR_HOST_MALFORMED
   */
  public function testMalformedSchemeAndOrHost($url) {
    (new URL($url))->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URL::validate
   * @dataProvider dataProviderValid
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Validation\URL::E_NO_EXTERNAL
   * @global \MovLib\Tool\Configuration $config
   */
  public function testNoExternal($unused, $url) {
    global $config;
    $config->domainDefault = "example.com";
    (new URL($url))->validate();
  }

  /**
   * @dataProvider dataProviderAllInvalid
   */
  public function testPatternInvalid($url) {
    $pattern = str_replace("/", "\/", URL::PATTERN);
    $this->assertEquals(0, preg_match("/{$pattern}/", $url));
  }

  /**
   * @dataProvider dataProviderValid
   */
  public function testPatternValid($unused, $url) {
    $pattern = str_replace("/", "\/", URL::PATTERN);
    $this->assertEquals(1, preg_match("/{$pattern}/", $url));
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URL::validate
   * @dataProvider dataProviderReachability
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionCode \MovLib\Presentation\Validation\URL::E_UNREACHABLE
   */
  public function testReachability($url) {
    $urlValidator                    = new URL($url);
    $urlValidator->allowExternal     = true;
    $urlValidator->checkReachability = true;
    $urlValidator->validate();
  }

  /**
   * @covers \MovLib\Presentation\Validation\URL::__construct
   * @covers \MovLib\Presentation\Validation\URL::validate
   * @dataProvider dataProviderValid
   */
  public function testValid($expected, $url) {
    $urlValidator                    = new URL($url);
    $urlValidator->allowExternal     = true;
    $urlValidator->checkReachability = true;
    $this->assertEquals($expected, $urlValidator->validate());
  }

  /**
   * @covers ::__construct
   * @todo Implement __construct
   */
  public function testConstruct() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::__toString
   * @todo Implement __toString
   */
  public function testToString() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::set
   * @todo Implement set
   */
  public function testSet() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::validate
   * @todo Implement validate
   */
  public function testValidate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::checkReachability
   * @todo Implement checkReachability
   */
  public function testCheckReachability() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

}
