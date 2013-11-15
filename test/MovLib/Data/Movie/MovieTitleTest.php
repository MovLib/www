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
namespace MovLib\Data\Movie;

use \MovLib\Data\Movie\MovieTitle;

/**
 * @coversDefaultClass \MovLib\Data\Movie\MovieTitle
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class MovieTitleTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Data\Movie\MovieTitle */
  protected $movieTitle;


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstruct() {
    $this->movieTitle = new MovieTitle();
    $this->assertInstanceOf("\\MovLib\\Data\\Movie\\MovieTitle", $this->movieTitle);
    $this->assertNull($this->movieTitle->id);
    
    $this->movieTitle = new MovieTitle(MovieTitle::FROM_ID, 1);
    $this->assertEquals(1, $this->movieTitle->id);
    $this->assertEquals("Die Verurteilten", $this->movieTitle->title);
    $this->assertEquals([ "de" => "deutsches Kommentar", "en" => "english comment" ], $this->movieTitle->dynComments);
  }

}
