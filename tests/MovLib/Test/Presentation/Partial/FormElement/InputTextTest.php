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

use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputText
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class InputTextTest extends \PHPUnit_Framework_TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public static function dataProviderValidPlainTextStrings() {
    return [
      [ "" ],                           // Valid empty string
      [ "movlib" ],                     // Valid ASCII
      [ " movlib ", "movlib" ],         // Valid ASCII which gets trimmed
      [ "mov < lib" ],                  // Valid ASCII and usage of special HTML character
      [ "mov < lib > mov" ],            // Valid ASCII and usage of special HTML characters
      [ "<>'\"&/" ],                    // Valid character sequence
      [ "\\\";alert('XSS');//" ],       // Valid (standalone) character sequence
      [ "mov < > lib" ],                // Valid as well
      [ "κόσμε" ],                      // Valid UTF-8
      [ "\xc3\xb1" ],                   // Valid 2 Octet Sequence
      [ "\xe2\x82\xa1" ],               // Valid 3 Octet Sequence
      [ "\xf0\x90\x8c\xbc" ],           // Valid 4 Octet Sequence
    ];
  }

  public static function dataProviderInvalidUnicode() {
    return [

    ];
  }

  public static function dataProviderInvalidNFCForm() {
    return [

    ];
  }

  public static function dataProviderLowASCII() {
    return [

    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::validate
   * @dataProvider dataProviderValidPlainTextStrings
   * @group Validation
   */
  public function testValid($actual, $expected = null) {
    $this->assertEquals($expected ?: $actual, (new InputText("phpunit", "PHPUnit", [ "value" => $actual ]))->validate());
  }

}
