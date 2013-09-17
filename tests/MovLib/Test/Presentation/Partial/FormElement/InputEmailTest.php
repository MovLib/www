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

use \MovLib\Presentation\Partial\FormElement\InputEmail;

/**
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmailTest extends \PHPUnit_Framework_TestCase {

  /**
   * Instantiate input email form element for test.
   *
   * @return \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  public static function getInput($value) {
    $_POST["email"] = $value;
    $input = new InputEmail();
    $input->value = $value;
    return $input;
  }

  /**
   * @link https://github.com/iamcal/rfc822/blob/master/tests/tests.xml
   * @link http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses
   */
  public static function dataProviderValid() {
    return [
      [ self::getInput('""@movlib.org') ],
      [ self::getInput("+1~1+@movlib.org") ],
      [ self::getInput('$a12345@movlib.org') ],
      [ self::getInput("phpunit@movlib.org") ],
      [ self::getInput("_______@movlib.org") ],
      [ self::getInput("_phpunit@movlib.org") ],
      [ self::getInput('"phpunit"@movlib.org') ],
      [ self::getInput("phpunit@about.museum") ],
      [ self::getInput("phpunit@amazon.co.jp") ],
      [ self::getInput("0123456789@movlib.org") ],
      [ self::getInput("1234567890@movlib.org") ],
      [ self::getInput("{_phpunit_}@movlib.org") ],
      [ self::getInput("phpunit@dev.movlib.org") ],
      [ self::getInput("!abc!xyz%abc@movlib.org") ],
      [ self::getInput("phpunit@blue-tomato.com") ],
      [ self::getInput("phpunit@fussenegger.info") ],
      [ self::getInput("phpunit@api.dev.movlib.org") ],
      [ self::getInput("phpunit.phpunit@movlib.org") ],
      [ self::getInput("phpunit+phpunit@movlib.org") ],
      [ self::getInput("phpunit-phpunit@movlib.org") ],
      [ self::getInput("phpunit*phpunit@movlib.org") ],
      [ self::getInput('phpunit."phpunit"@movlib.org') ],
      [ self::getInput('"phpunit\phpunit"@movlib.org') ],
      [ self::getInput('"phpunit@phpunit"@movlib.org') ],
      [ self::getInput('"phpunit\"phpunit"@movlib.org') ],
      [ self::getInput('"phpunit\@phpunit"@movlib.org') ],
      [ self::getInput('"phpunit\\phpunit"@movlib.org') ],
      [ self::getInput('"phpunit\ phpunit"@movlib.org') ],
      [ self::getInput('"phpunit.\\phpunit"@movlib.org') ],
      [ self::getInput("phpunit/phpunit=phpunit@movlib.org") ],
      [ self::getInput('very.unusual."@".unusual.com@movlib.org') ],
      [ self::getInput('very."(),:;<>[]".very."very@\\\\\\ \"very".unusual@dev.movlib.org') ],
      [ self::getInput("phpunit@x23456789012345678901234567890123456789012345678901234567890123.movlib.org") ],
      // The following address has exactly 254 characters!
      [ self::getInput("12345678901234567890123456789012345678901234567890123456789@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org") ],
    ];
  }

  public static function dataProviderInvalid() {
    return [
      // Valid syntax but no DNS record
      [ self::getInput("phpunit@123.123.123.x123") ],
      [ self::getInput("phpunit@[123.123.123.123]") ],
      [ self::getInput("phpunit@[ipv6:::12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[12.34.56.78]") ],
      [ self::getInput("phpunit@[ipv6:1111:2222:3333::4444:5555:6666]") ],
      [ self::getInput("phpunit@[ipv6:1111:2222:3333:4444:5555:6666::]") ],
      [ self::getInput("phpunit@[ipv6:::1111:2222:3333:4444:5555:6666]") ],
      [ self::getInput("phpunit@[ipv6:1111:2222:3333::4444:12.34.56.78]") ],
      [ self::getInput("phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888]") ],
      [ self::getInput("phpunit@[ipv6:1111:2222:3333:4444:5555:6666:12.34.56.78]") ],
      [ self::getInput("x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x2") ],
      // Valid syntax but too long
      [ self::getInput("123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org") ],
      // Invalid IP address as host
      [ self::getInput("phpunit.phpunit@[.12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[12.34.56.789]") ],
      [ self::getInput("phpunit.phpunit@[::12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[IPv5:::12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:333x::4444:5555]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:33333::4444:5555]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222::3333::4444:5555:6666]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333::4444:5555:6666:7777]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333::4444:5555:12.34.56.78]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]") ],
      [ self::getInput("phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]") ],
      // No local part at all
      [ self::getInput("movlib.org") ],
      [ self::getInput("@movlib.org") ],
      // Syntax error in local part
      [ self::getInput('"""@movlib.org') ],
      [ self::getInput('"\"@movlib.org') ],
      [ self::getInput('"phpunit@movlib.org') ],
      [ self::getInput('phpunit"@movlib.org') ],
      [ self::getInput('"[[ phpunit ]]"@movlib.org') ],
      [ self::getInput("phpunit phpunit@movlib.org") ],
      [ self::getInput("phpunit@phpunit@movlib.org") ],
      [ self::getInput(".phpunit.phpunit@movlib.org") ],
      [ self::getInput("phpunit.phpunit.@movlib.org") ],
      [ self::getInput("phpunit..phpunit@movlib.org") ],
      [ self::getInput("phpunit\@phpunit@movlib.org") ],
      [ self::getInput('"phpunit phpunit"@movlib.org') ],
      [ self::getInput("phpunit\\@phpunit@movlib.org") ],
      [ self::getInput('"phpunit"phpunit"@movlib.org') ],
      [ self::getInput('phpunit\\@phpunit@movlib.org') ],
      [ self::getInput('phpunit\ "phpunit"\ phpunit@movlib.org') ],
      [ self::getInput('"phpunit "phpunit" phpunit."@movlib.org') ],
      [ self::getInput('"phpunit \"phpunit\" phpunit.@movlib.org') ],
      [ self::getInput('"phpunit \"phpunit\"\ phpunit\.@movlib.org') ],
      // No host part at all
      [ self::getInput("phpunit") ],
      [ self::getInput("phpunit.phpunit") ],
      [ self::getInput("phpunit.phpunit@") ],
      // Syntax error in host part
      [ self::getInput("phpunit@org") ],
      [ self::getInput("phpunit@movlib.123") ],
      [ self::getInput("phpunit@movlib.org.") ],
      [ self::getInput("phpunit@-movlib.org") ],
      [ self::getInput("phpunit@movlib-.org") ],
      [ self::getInput("phpunit@movlib.org,com") ],
      // Invalid, just invalid
      [ self::getInput("") ],
      [ self::getInput("\n") ],
      [ self::getInput("#@%^%#$@#$@#.org") ],
      [ self::getInput('"(),:;<>[\]@movlib.org') ],
      [ self::getInput("ハローワールド@movlib.org") ], // hello world
      [ self::getInput("phpunit@movlib.org (PHPUnit)") ],
      [ self::getInput("PHPUnit PHPUnit <phpunit@movlib.org>") ],
    ];
  }

  /**
   * @covers InputEmail::__constructor
   */
  public function testDefaults() {
    $input = self::getInput(null);
    $this->assertEquals("email", $input->id);
    $this->assertEquals("email", $input->attributes["type"]);
    $this->assertEquals(254, $input->attributes["maxlength"]);
  }

  /**
   * @todo Extend regular expression to be more precise!
   * @covers InputUrl::__construct
   * @dataProvider dataProviderInvalid
   */
//  public function testValidationRegExInvalid(InputEmail $input) {
//    $this->assertFalse((bool) preg_match("/{$input->attributes["pattern"]}/", $input->value));
//  }

  /**
   * @covers InputUrl::__construct
   * @dataProvider dataProviderValid
   */
  public function testValidationRegExValid(InputEmail $input) {
    $this->assertTrue((bool) preg_match("/{$input->attributes["pattern"]}/", $input->value));
  }

  /**
   * @covers InputEmail::validate
   * @dataProvider dataProviderInvalid
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationInvalid(InputEmail $input) {
    $input->validate();
  }

  /**
   * @covers InputEmail::validate
   * @dataProvider dataProviderValid
   */
  public function testValidationValid(InputEmail $input) {
    $input->validate();
  }

}
