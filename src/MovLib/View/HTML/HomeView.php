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
namespace MovLib\View\HTML;

use \MovLib\View\HTML\AbstractView;

/**
 * The <b>Home</b> view contains the HTML layout for the MovLib home page.
 *
 * The home page should be generated without a single database call. This should simply increase the performance of the
 * home page, as it is most certainly the most viewed page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class HomeView extends AbstractView {

  /**
   * The home page is the only page that is using a different title scheme than the other pages.
   */
  public function __construct() {
    $this->title = 'MovLib, the free movie library.';
    $this->renderedView = '<p>' . _('Hello World!') . '</p>';
  }

  /**
   * Overwrite the default header logo method.
   *
   * The home page has to feature our own brand and should not link to itself. Therefor we overwrite the default method
   * where the logo is a link to the home page. The <code>&lt;h1&gt;</code>-element has to be unique on the home page.
   *
   * @return string
   */
  public function getHeaderLogo() {
    return '<h1 id="logo">MovLib <small>the <em class="serif">free</em> movie library</small></h1>';
  }

}
