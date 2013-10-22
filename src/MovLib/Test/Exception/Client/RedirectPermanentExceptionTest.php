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
namespace MovLib\Test\Exception\Client;

use \MovLib\Exception\Client\RedirectPermanentException as Redirect;

/**
 * @coversDefaultClass \MovLib\Exception\Client\RedirectPermanentException
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class RedirectPermanentExceptionTest extends \MovLib\Test\TestCase {

  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $r = new Redirect("/phpunit");
    $this->assertEquals(301, http_response_code());
    $this->assertContains("301 Moved Permanently", $r->presentation);
  }

}
