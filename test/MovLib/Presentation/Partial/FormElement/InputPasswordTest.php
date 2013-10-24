<?php

/* !
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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\InputPassword;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputPassword
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputPasswordTest extends \MovLib\TestCase {

  public function dataProviderValidPasswords() {
    return [
      [ "Test1234" ],
      [ "Pass1worD" ],
      [ "P1assword" ],
      [ "1Password" ],
      [ "1passWord" ],
      [ "1passworD" ],
      [ "paSsword1" ],
      [ "p1aSsword" ],
      [ "p1assworD" ],
      [ "ValidPassword123" ],
      [ "V1alidPassword" ],
      [ "V1alidPássworD" ],
      [ "validPassword123" ],
      [ "v1alidPassword" ],
      [ "v1alidPassworD" ],
    ];
  }

  public static function dataProviderWeakPasswords() {
    return [
      [ "test" ],
      [ "testtest" ],
      [ "TEST" ],
      [ "TESTTEST" ],
      [ "test1234" ],
      [ "TEST1234" ],
      [ "iamaverylongpasswordbutnotstrongenough" ],
      [ "IAmAVeryLongPasswordButNotStrongEnough" ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Helpers


  private function _validate($password = "") {
    $inputPassword        = new InputPassword();
    $inputPassword->value = $password;
    return $inputPassword->validate();
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $inputPassword = new InputPassword("phpunit", "PHPUnit", [ "foo" => "bar" ]);
    foreach ([ "foo", "pattern", "placeholder", "title", "type" ] as $key) {
      $this->assertArrayHasKey($key, $inputPassword->attributes);
    }
    $this->assertEquals("password", $inputPassword->attributes["type"]);
    $this->assertEquals("bar", $inputPassword->attributes["foo"]);
    $this->assertTrue(in_array("required", $inputPassword->attributes));
  }

  /**
   * @coversNothing
   * @dataProvider dataProviderWeakPasswords
   */
  public function testPatternInvalid($password) {
    $pattern = (new InputPassword())->attributes["pattern"];
    $this->assertFalse((boolean) preg_match("/{$pattern}/", $password));
  }

  /**
   * @coversNothing
   */
  public function testPatternValid() {
    $pattern = (new InputPassword())->attributes["pattern"];
    $this->assertRegExp("/{$pattern}/", "Test1234");
  }

  /**
   * @covers ::__toString
   * @depends testConstruct
   */
  public function testToString() {
    $_POST["password"]    = "Test1234";
    $inputPassword        = new InputPassword("password", "Password", [ "value" => "Test1234" ]);
    $inputPassword->value = "Test1234";
    $this->assertNotContains("Test1234", $inputPassword->__toString());
    unset($_POST);
  }

  /**
   * @covers ::validate
   * @depends testConstruct
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage mandatory
   */
  public function testValidateEmpty() {
    $this->_validate();
  }

  /**
   * @covers ::validate
   * @depends testConstruct
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage too short
   */
  public function testValidateTooShort() {
    $this->_validate("test");
  }

  /**
   * @covers ::validate
   * @dataProvider dataProviderWeakPasswords
   * @depends testConstruct
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage not complex enough
   */
  public function testValidateComplexity($password) {
    $this->_validate($password);
  }

  /**
   * @covers ::validate
   * @dataProvider dataProviderValidPasswords
   * @depends testConstruct
   */
  public function testValidate($password) {
    $this->_validate($password);
  }

}
