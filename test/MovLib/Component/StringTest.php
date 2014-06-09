<?php

/*!
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
namespace MovLib\Component;

use \MovLib\Component\String;

/**
 * @coversDefaultClass \MovLib\Component\String
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class StringTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- String::camelCase


  public function dataProviderCamelCase() {
    return [
      [ "camelCase", "Camel Case" ],
      [ "camelCase", "camel Case" ],
      [ "camelCase", "Camel case" ],
      [ "camelCase", "camel case" ],
      [ "camelCase", "Camel-Case", "-" ],
      [ "camelCase", "camel-Case", "-" ],
      [ "camelCase", "Camel-case", "-" ],
      [ "camelCase", "camel-case", "-" ],
      [ "camelCase", "Camel_case", "-" ], // Wrong but assumed delimiter
      [ "camel/case", "Camel/case", "-" ], // Wrong delimiter
      [ "τάχιστηΑλώπηξΒαφήςΨημένηΓη,ΔρασκελίζειΥπέρΝωθρούΚυνός", "Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός" ],
    ];
  }

  /**
   * @covers String::camelCase
   * @dataProvider dataProviderCamelCase
   * @param string $expected
   * @param string $string
   * @param string $delimiter [optional]
   */
  public function testCamelCase($expected, $string, $delimiter = null) {
    $this->assertEquals($expected, String::camelCase($string, $delimiter));
  }


  // ------------------------------------------------------------------------------------------------------------------- String::pascalCase


  public function dataProviderPascalCase() {
    return [
      [ "PascalCase", "Pascal Case" ],
      [ "PascalCase", "pascal Case" ],
      [ "PascalCase", "Pascal case" ],
      [ "PascalCase", "pascal case" ],
      [ "PascalCase", "Pascal-Case", "-" ],
      [ "PascalCase", "pascal-Case", "-" ],
      [ "PascalCase", "Pascal-case", "-" ],
      [ "PascalCase", "pascal-case", "-" ],
      [ "PascalCase", "Pascal_case", "-" ], // Wrong but assumed delimiter
      [ "Pascal/case", "pascal/case", "-" ], // Wrong delimiter
      [ "ΤάχιστηΑλώπηξΒαφήςΨημένηΓη,ΔρασκελίζειΥπέρΝωθρούΚυνός", "Τάχιστη αλώπηξ βαφής ψημένη γη, δρασκελίζει υπέρ νωθρού κυνός" ],
    ];
  }

  /**
   * @covers String::pascalCase
   * @dataProvider dataProviderPascalCase
   * @param string $expected
   * @param string $string
   * @param string $delimiter [optional]
   */
  public function testPascalCase($expected, $string, $delimiter = null) {
    $this->assertEquals($expected, String::pascalCase($string, $delimiter));
  }

}
