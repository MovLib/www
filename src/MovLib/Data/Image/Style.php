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
namespace MovLib\Data\Image;

/**
 * Default image style implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Style {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image style's translated <code>alt</code> attribute content.
   *
   * @var string
   */
  public $alt;

  /**
   * The image style's absolute URL.
   *
   * @var string
   */
  public $src;

  /**
   * The image style's width.
   *
   * @var int
   */
  public $width;

  /**
   * The image style's height.
   *
   * @var int
   */
  public $height;

  /**
   * The image style's route to its details page.
   *
   * @var string
   */
  public $route;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image style.
   *
   * @param string $alt
   *   The image style's translated <code>alt</code> attribute content.
   * @param string $src
   *   The image style's absolute URL.
   * @param int $width
   *   The image style's width.
   * @param int $height
   *   The image style's height.
   * @param string $route
   *   The image style's route to its details page.
   */
  public function __construct($alt, $src, $width, $height, $route) {
    $this->alt    = $alt;
    $this->src    = $src;
    $this->width  = $width;
    $this->height = $height;
    $this->route  = $route;
  }

}
