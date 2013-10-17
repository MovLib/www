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
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputEmail
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputEmailTest extends \MovLib\Test\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public static function dataProviderInvalid() {
    return [
      // Valid syntax but no DNS record
      [ "phpunit@123.123.123.x123" ],
      [ "phpunit@[123.123.123.123]" ],
      [ "phpunit@[ipv6:::12.34.56.78]" ],
      [ "phpunit.phpunit@[12.34.56.78]" ],
      [ "phpunit@[ipv6:1111:2222:3333::4444:5555:6666]" ],
      [ "phpunit@[ipv6:1111:2222:3333:4444:5555:6666::]" ],
      [ "phpunit@[ipv6:::1111:2222:3333:4444:5555:6666]" ],
      [ "phpunit@[ipv6:1111:2222:3333::4444:12.34.56.78]" ],
      [ "phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888]" ],
      [ "phpunit@[ipv6:1111:2222:3333:4444:5555:6666:12.34.56.78]" ],
      [ "x@x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x23456789.x2" ],
      // Invalid IP address as host
      [ "phpunit.phpunit@[.12.34.56.78]" ],
      [ "phpunit.phpunit@[12.34.56.789]" ],
      [ "phpunit.phpunit@[::12.34.56.78]" ],
      [ "phpunit.phpunit@[IPv5:::12.34.56.78]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:333x::4444:5555]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:33333::4444:5555]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222::3333::4444:5555:6666]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333::4444:5555:6666:7777]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:12.34.56.78]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333::4444:5555:12.34.56.78]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:8888:9999]" ],
      [ "phpunit.phpunit@[ipv6:1111:2222:3333:4444:5555:6666:7777:12.34.56.78]" ],
      // No local part at all
      [ "movlib.org" ],
      [ "@movlib.org" ],
      // No host part at all
      [ "phpunit" ],
      [ "phpunit.phpunit" ],
      [ "phpunit.phpunit@" ],
      // Syntax error in host part
      [ "phpunit@org" ],
      [ "phpunit@movlib.123" ],
      [ "phpunit@movlib.org." ],
      [ "phpunit@-movlib.org" ],
      [ "phpunit@movlib-.org" ],
      [ "phpunit@movlib.org,com" ],
      // Invalid, just invalid
      [ "" ],
      [ "\n" ],
      [ "#@%^%#$@#$@#.org" ],
      [ "ハローワールド@movlib.org" ], // hello world
      [ "phpunit@movlib.org (PHPUnit)" ],
      [ "PHPUnit PHPUnit <phpunit@movlib.org>" ],
    ];
  }

  public static function dataProviderInvalidPHP() {
    return array_merge(self::dataProviderInvalid(), [
      // Valid syntax but too long (exactly 255 characters)
      [ "123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org" ],
      // Syntax error in local part
      [ '"\"@movlib.org' ],
      [ '"""@movlib.org' ],
      [ '""""@movlib.org' ],
      [ '"phpunit@movlib.org' ],
      [ 'phpunit"@movlib.org' ],
      [ '"(),:;<>[\]@movlib.org' ],
      [ '"[[ phpunit ]]"@movlib.org' ],
      [ "phpunit phpunit@movlib.org" ],
      [ "phpunit@phpunit@movlib.org" ],
      [ ".phpunit.phpunit@movlib.org" ],
      [ "phpunit.phpunit.@movlib.org" ],
      [ "phpunit..phpunit@movlib.org" ],
      [ "phpunit\@phpunit@movlib.org" ],
      [ '"phpunit"phpunit"@movlib.org' ],
      [ '"phpunit phpunit"@movlib.org' ],
      [ "phpunit\\@phpunit@movlib.org" ],
      [ 'phpunit\\@phpunit@movlib.org' ],
      [ 'phpunit\ "phpunit"\ phpunit@movlib.org' ],
      [ '"phpunit "phpunit" phpunit."@movlib.org' ],
      [ '"phpunit \"phpunit\" phpunit.@movlib.org' ],
      [ '"phpunit \"phpunit\"\ phpunit\.@movlib.org' ],
    ]);
  }

  public static function dataProviderValid() {
    return [
      [ '""@movlib.org' ],
      [ "+1~1+@movlib.org" ],
      [ '$a12345@movlib.org' ],
      [ "phpunit@movlib.org" ],
      [ "_______@movlib.org" ],
      [ "_phpunit@movlib.org" ],
      [ '"phpunit"@movlib.org' ],
      [ "phpunit@about.museum" ],
      [ "phpunit@amazon.co.jp" ],
      [ "0123456789@movlib.org" ],
      [ "1234567890@movlib.org" ],
      [ "{_phpunit_}@movlib.org" ],
      [ "phpunit@dev.movlib.org" ],
      [ "!abc!xyz%abc@movlib.org" ],
      [ "phpunit@blue-tomato.com" ],
      [ "phpunit@fussenegger.info" ],
      [ "phpunit@api.dev.movlib.org" ],
      [ "phpunit.phpunit@movlib.org" ],
      [ "phpunit+phpunit@movlib.org" ],
      [ "phpunit-phpunit@movlib.org" ],
      [ "phpunit*phpunit@movlib.org" ],
      [ 'phpunit."phpunit"@movlib.org' ],
      [ '"phpunit\phpunit"@movlib.org' ],
      [ '"phpunit@phpunit"@movlib.org' ],
      [ '"phpunit\"phpunit"@movlib.org' ],
      [ '"phpunit\@phpunit"@movlib.org' ],
      [ '"phpunit\\phpunit"@movlib.org' ],
      [ '"phpunit\ phpunit"@movlib.org' ],
      [ '"phpunit.\\phpunit"@movlib.org' ],
      [ "phpunit/phpunit=phpunit@movlib.org" ],
      [ 'very.unusual."@".unusual.com@movlib.org' ],
      [ 'very."(),:;<>[]".very."very@\\\\\\ \"very".unusual@dev.movlib.org' ],
      [ "phpunit@x23456789012345678901234567890123456789012345678901234567890123.movlib.org" ],
      // The following address has exactly 254 characters!
      [ "12345678901234567890123456789012345678901234567890123456789@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  private function _validate($email = "") {
    $inputEmail = new InputEmail();
    $inputEmail->value = $email;
    return $inputEmail->validate();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
    */
  public function testConstruct() {
    $inputEmail = new InputEmail("phpunit", "PHPUnit", [ "foo" => "bar" ]);
    foreach ([ "foo", "maxlength", "pattern", "placeholder", "title", "type" ] as $key) {
      $this->assertArrayHasKey($key, $inputEmail->attributes);
    }
    $this->assertEquals(254, $inputEmail->attributes["maxlength"]);
    $this->assertEquals("email", $inputEmail->attributes["type"]);
    $this->assertEquals("bar", $inputEmail->attributes["foo"]);
    $this->assertTrue(in_array("required", $inputEmail->attributes));
  }

  /**
   * @coversNothing
   * @dataProvider dataProviderInvalid
    */
  public function testPatternInvalid($email) {
    $pattern = strtr((new InputEmail())->attributes["pattern"], "/", "\/");
    $this->assertFalse((boolean) preg_match("/{$pattern}/", $email));
  }

  /**
   * @coversNothing
   * @dataProvider dataProviderValid
    */
  public function testPatternValid($email) {
    $pattern = strtr((new InputEmail())->attributes["pattern"], "/", "\/");
    $this->assertRegExp("/{$pattern}/", $email);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage mandatory
    */
  public function testValidateEmpty() {
    $this->_validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage too long
    */
  public function testValidateTooLong() {
    $this->_validate(str_repeat("a", 255));
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage invalid
    */
  public function testValidateSyntax() {
    $this->_validate("phpunit");
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage unreachable
    */
  public function testValidateDNS() {
    $this->_validate("user@foo.bar");
  }

  /**
   * @covers ::validate
    */
  public function testValidate() {
    $this->_validate("phpunit@movlib.org");
  }

}
