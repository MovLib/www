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
namespace MovLib\Core\Routing;

use \MovLib\Core\IntlDouble;
use \MovLib\Core\Routing\Route;

/**
 * @coversDefaultClass \MovLib\Core\Routing\Route
 * @group Routing
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RouteTest extends \MovLib\TestCase {

  public function dataProviderCompile() {
    return [
      [ "/path", "/path" ],
      [ "/path/argument", "/path/{0}", [ "argument" ] ],
      [ "/path?parameter=1", "/path", null, [ "query" => [ "parameter" => true ] ] ],
      [ "/path#fragment", "/path", null, [ "fragment" => "fragment" ] ],
      [ "https://en.movlib.org/path", "/path", null, [ "absolute" => true ] ],
      [ "http://phpunit.de/path/argument?parameter=1#fragment", "/path/{0}", [ "argument" ], [
        "absolute" => true,
        "fragment" => "fragment",
        "hostname" => "phpunit.de",
        "query"    => [ "parameter" => true ],
        "scheme"   => "http",
      ] ],
    ];
  }

  /**
   * @dataProvider dataProviderCompile
   */
  public function testToStringAndCompile($expected, $path, array $arguments = null, array $options = null) {
    $this->assertEquals($expected, (string) new Route(new IntlDouble(), $path, $arguments, $options));
  }

}
