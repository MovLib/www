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
namespace MovLib\Presentation;

/**
 * Special page for error presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ErrorPage extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The translated page's content.
   *
   * @var mixed
   */
  public $content;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new empty page.
   *
   * @param string $title
   *   The translated page's title.
   * @param mixed $content
   *   The translated page's content.
   */
  public function __construct($title, $content) {
    $this->initPage($title);
    $this->initBreadcrumb();
    $this->content = $content;
  }


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Get the presentation's page content.
   *
   * @return string
   */
  protected function getContent() {
    return $this->content;
  }

}
