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

use \MovLib\Data\User;
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
   * Instance to be tested.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputEmail
   */
  public static $inputEmail;

  /**
   * Instantiate input email form element for tests.
   */
  public static function setUpBeforeClass() {
    self::$inputEmail = new InputEmail();
  }

  public function testDefaults() {
    $this->assertEquals("email", self::$inputEmail->id);
    $this->assertEquals("email", self::$inputEmail->attributes["type"]);
  }

  /**
   * @link https://github.com/iamcal/rfc822/blob/master/tests/tests.xml
   * @link http://codefool.tumblr.com/post/15288874550/list-of-valid-and-invalid-email-addresses
   */
  public static function dataProviderValidationValid() {
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

  /**
   * @dataProvider dataProviderValidationValid
   */
  public function testValidationValid($input) {
    $_POST[self::$inputEmail->id] = $input;
    self::$inputEmail->validate();
  }

  public static function dataProviderValidationInvalid() {
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
      // Valid syntax but too long
      [ "123456789012345678901234567890123456789012345678901234567890@12345678901234567890123456789012345678901234567890123456789.12345678901234567890123456789012345678901234567890123456789.123456789012345678901234567890123456789012345678901234567890123.movlib.org" ],
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
      // Syntax error in local part
      [ '"""@movlib.org' ],
      [ '"\"@movlib.org' ],
      [ '"phpunit@movlib.org' ],
      [ 'phpunit"@movlib.org' ],
      [ '"[[ phpunit ]]"@movlib.org' ],
      [ "phpunit phpunit@movlib.org" ],
      [ "phpunit@phpunit@movlib.org" ],
      [ ".phpunit.phpunit@movlib.org" ],
      [ "phpunit.phpunit.@movlib.org" ],
      [ "phpunit..phpunit@movlib.org" ],
      [ "phpunit\@phpunit@movlib.org" ],
      [ '"phpunit phpunit"@movlib.org' ],
      [ "phpunit\\@phpunit@movlib.org" ],
      [ '"phpunit"phpunit"@movlib.org' ],
      [ 'phpunit\\@phpunit@movlib.org' ],
      [ 'phpunit\ "phpunit"\ phpunit@movlib.org' ],
      [ '"phpunit "phpunit" phpunit."@movlib.org' ],
      [ '"phpunit \"phpunit\" phpunit.@movlib.org' ],
      [ '"phpunit \"phpunit\"\ phpunit\.@movlib.org' ],
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
      [ '"(),:;<>[\]@movlib.org' ],
      [ "ハローワールド@movlib.org" ], // hello world
      [ "phpunit@movlib.org (PHPUnit)" ],
      [ "PHPUnit PHPUnit <phpunit@movlib.org>" ],
    ];
  }

  /**
   * @dataProvider dataProviderValidationInvalid
   * @expectedException \MovLib\Exception\ValidatorException
   */
  public function testValidationInvalid($input) {
    $_POST[self::$inputEmail->id] = $input;
    self::$inputEmail->validate();
  }

}
