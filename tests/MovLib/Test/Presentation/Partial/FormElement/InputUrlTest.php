<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Test\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\InputUrl;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputUrlTest extends \PHPUnit_Framework_TestCase {

  /**
   * Instantiate input url form element for test.
   *
   * @return \MovLib\Presentation\Partial\FormElement\InputUrl
   */
  public static function getInput($value) {
    $_POST["phpunit"] = $value;
    return new InputUrl("phpunit", "PHPUnit", $value);
  }

  public static function dataProviderValid() {
    return [
      [ self::getInput("http://movlib.org"), "http://movlib.org" ],
      [ self::getInput("http://movlib.org/"), "http://movlib.org/" ],
      [ self::getInput("https://movlib.org/"), "https://movlib.org/" ],
      [ self::getInput("http://movlib.org/foo/bar/"), "http://movlib.org/foo/bar/" ],
      [ self::getInput("http://movlib.org/foo?bar=42"), "http://movlib.org/foo?bar=42" ],
      [ self::getInput("https://ja.wikipedia.org/wiki/Unix系"), "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB" ],
      [ self::getInput("https://en.wikipedia.org/wiki//dev/random"), "https://en.wikipedia.org/wiki//dev/random" ],
      [ self::getInput("http://www.youtube.com/watch?v=5gUKvmOEGCU"), "http://www.youtube.com/watch?v=5gUKvmOEGCU" ],
      [ self::getInput("https://ja.wikipedia.org/wiki/Unix%E7%B3%BB"), "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB" ],
      [
        self::getInput("https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Url.php/function/Url%3A%3AisValid/8"),
        "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Url.php/function/Url%3A%3AisValid/8"
      ]
    ];
  }

  public static function dataProviderInvalid() {
    return [
      [ self::getInput("") ],
      [ self::getInput("\n") ],
      [ self::getInput("MovLib") ],
      [ self::getInput("movlib.org") ],
      [ self::getInput("//movlib.org") ],
      [ self::getInput("www.movlib.org") ],
      [ self::getInput("ftp://movlib.org/") ],
      [ self::getInput("http://movlib.123") ],
      [ self::getInput("movlib.org/foo/bar") ],
      [ self::getInput("ldap://movlib.org/") ],
      [ self::getInput("//movlib.org/foo/bar") ],
      [ self::getInput("http://movlib.org:80/") ],
      [ self::getInput("mailto:user@movlib.org") ],
      [ self::getInput("www.movlib.org/foo/bar") ],
      [ self::getInput("http://admin@movlib.org/") ],
      [ self::getInput("http://admin:1234@movlib.org/") ],
      [ self::getInput("http://admin:1234@movlib.org:1234") ],
    ];
  }

  /**
   * @covers InputUrl::__construct
   */
  public function testDefaults() {
    $input = self::getInput(null);
    $this->assertEquals("phpunit", $input->id);
    $this->assertEquals("url", $input->attributes["type"]);
    $this->assertEquals("^https?://[a-z0-9\-\.]+\.[a-z]{2,5}(/.*)*$", $input->attributes["pattern"]);
  }

  /**
   * @covers InputUrl::__construct
   * @dataProvider dataProviderInvalid
   */
  public function testValidationRegExInvalid(InputUrl $input) {
    $this->assertFalse((bool) preg_match("#{$input->attributes["pattern"]}#", $input->value));
  }

  /**
   * @covers InputUrl::__construct
   * @dataProvider dataProviderValid
   */
  public function testValidationRegExValid(InputUrl $input) {
    $this->assertTrue((bool) preg_match("#{$input->attributes["pattern"]}#", $input->value));
  }

  /**
   * @covers InputUrl::validate
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationExists() {
    $input = self::getInput("https://movlib.org/foo/bar/phpunit");
    $input->attributes["data-url-exists"] = "true";
    $input->validate();
  }

  /**
   * @covers InputUrl::validate
   * @dataProvider dataProviderInvalid
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationInvalid(InputUrl $input) {
    $input->validate();
  }

  /**
   * @covers InputUrl::validate
   * @dataProvider dataProviderValid
   */
  public function testValidationValid(InputUrl $input, $expected) {
    $this->assertEquals($expected, $input->validate()->value);
  }

}
