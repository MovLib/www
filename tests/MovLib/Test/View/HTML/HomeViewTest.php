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
namespace MovLib\Test\View\HTML;

use \MovLib\Entity\Language;
use \MovLib\View\HTML\HomeView;
use \PHPUnit_Framework_TestCase;

/**
 * Description of HomeViewTest
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HomeViewTest extends PHPUnit_Framework_TestCase {

  /**
   * Instance of HomeView.
   *
   * @var \MovLib\View\HTML\HomeView
   */
  private $homeView;

  /**
   * Fixture before any test is executed.
   */
  public function setUp() {
    $this->homeView = new HomeView(new Language());
  }

  /**
   * Test if the correct body class is returned.
   */
  public function testBodyClass() {
    $this->assertEquals("home", $this->homeView->getBodyClass());
  }

  /**
   * The home view is the only page with a special logo mark-up.
   */
  public function testHeaderLogo() {
    $this->assertEquals(
      "<h1 id='logo' class='inline'>MovLib <small>the <em>free</em> movie library</small></h1>",
      $this->homeView->getHeaderLogo()
    );
  }

  /**
   * The home view is the only page with a special head title pattern.
   */
  public function testHeadTitle() {
    $this->assertEquals("MovLib, the free movie library.", $this->homeView->getHeadTitle());
  }

}
