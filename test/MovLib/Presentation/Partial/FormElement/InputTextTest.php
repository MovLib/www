<?php

/* !
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
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

use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputText
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputTextTest extends \MovLib\TestCase {

  // ------------------------------------------------------------------------------------------------------------------- Data Providers


  public function dataProviderValidPlainTextStrings() {
    return [
      [ "" ], // Valid empty string
      [ "movlib" ], // Valid ASCII
      [ " movlib ", "movlib" ], // Valid ASCII which gets trimmed
      [ "mov < lib", "mov &lt; lib" ], // Valid ASCII and usage of special HTML character
      [ "mov < lib > mov", "mov &lt; lib &gt; mov" ], // Valid ASCII and usage of special HTML characters
      [ "<>'\"&/", "&lt;&gt;&apos;&quot;&amp;/" ], // Valid character sequence
      [ "\\\";alert('XSS');//", "\&quot;;alert(&apos;XSS&apos;);//" ], // Valid (standalone) character sequence
      [ "κόσμε", "κόσμε" ], // Valid UTF-8 but not NFC form
      [ "\xc3\xb1" ], // Valid 2 Octet Sequence
      [ "\xe2\x82\xa1" ], // Valid 3 Octet Sequence
      [ "\xf0\x90\x8c\xbc" ], // Valid 4 Octet Sequence
    ];
  }

  public function dataProviderInvalidUnicode() {
    return [
    ];
  }

  public function dataProviderInvalidNFCForm() {
    return [
    ];
  }

  public function dataProviderLowASCII() {
    return [
    ];
  }

  // ------------------------------------------------------------------------------------------------------------------- Tests

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $inputText = new InputText("phpunit", "PHPUnit");
    $this->assertArrayHasKey("type", $inputText->attributes);
    $this->assertEquals("text", $inputText->attributes["type"]);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage mandatory
   */
  public function testValidateRequired() {
    (new InputText("phpunit", "PHPUnit", [ "required" ]))->validate();
  }

  /**
   * @covers ::validate
   * @dataProvider dataProviderValidPlainTextStrings
   */
  public function testValid($actual, $expected = null) {
    $inputText = new InputText("phpunit", "PHPUnit", [ "value" => $actual ]);
    $this->assertEquals($inputText, $inputText->validate());
    $this->assertEquals($expected ? : $actual, $inputText->value);
  }

}
