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

  public $inputUrl;

  public function setUp() {
    $this->inputUrl = new InputUrl("phpunit");
  }

  public function testDefaults() {
    $this->assertEquals("phpunit", $this->inputUrl->id);
    $this->assertEquals("url", $this->inputUrl->attributes["type"]);
    $this->assertEquals("https?://.*", $this->inputUrl->attributes["pattern"]);
  }

  public static function dataProviderValidationValid() {
    return [
      [ "http://movlib.org", "http://movlib.org" ],
      [ "http://movlib.org/", "http://movlib.org/" ],
      [ "https://movlib.org/", "https://movlib.org/" ],
      [ "http://movlib.org/foo/bar/", "http://movlib.org/foo/bar/" ],
      [ "http://movlib.org/foo?bar=42", "http://movlib.org/foo?bar=42" ],
      [ "https://en.wikipedia.org/wiki//dev/random", "https://en.wikipedia.org/wiki//dev/random" ],
      [ "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB", "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB" ],
      [ "http://www.youtube.com/watch?v=5gUKvmOEGCU", "http://www.youtube.com/watch?v=5gUKvmOEGCU" ],
      [ "https://ja.wikipedia.org/wiki/Unix%E7%B3%BB", "https://ja.wikipedia.org/wiki/Unix系" ],
      [
        "https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Component%21Utility%21Url.php/function/Url%3A%3AisValid/8",
        "https://api.drupal.org/api/drupal/core!lib!Drupal!Component!Utility!Url.php/function/Url%3A%3AisValid/8"
      ]
    ];
  }

  /**
   * @dataProvider dataProviderValidationValid
   */
  public function testValidationValid($expected, $input) {
    $_POST["phpunit"] = $input;
    $this->assertEquals($expected, $this->inputUrl->validate()->value);
  }

  /**
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationExists() {
    $_POST["phpunit"] = "http://movlib/foo/bar";
    $this->inputUrl->attributes["data-url-exists"] = "true";
    $this->inputUrl->validate();
  }

  public static function dataProviderValidationInvalid() {
    return [
      [ "http://movlib.org:80/" ],
      [ "http://admin:1234@movlib.org/" ],
      [ "http://admin@movlib.org/" ],
      [ "ftp://movlib.org/" ],
      [ "ldap://movlib.org/" ],
      [ "mailto:user@movlib.org" ],
      [ "//movlib.org" ],
      [ "movlib.org" ],
      [ "www.movlib.org" ],
      [ "//movlib.org/foo/bar" ],
      [ "movlib.org/foo/bar" ],
      [ "www.movlib.org/foo/bar" ],
      [ "MovLib" ],
      [ "\n" ],
      [ "" ],
    ];
  }

  /**
   * @dataProvider dataProviderValidationInvalid
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationInvalid($input) {
    $_POST["phpunit"] = $input;
    $this->inputUrl->validate();
  }

}
