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
namespace MovLib\Data\Image;

/**
 * Defines the image style object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ImageStyle {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image style's alternative text in the current locale.
   *
   * @var string
   */
  public $alt = "";

  /**
   * The image style's height in Pixel.
   *
   * @var integer
   */
  public $height;

  /**
   * Whether this image style is a placeholder or not.
   *
   * @var boolean
   */
  public $placeholder = false;

  /**
   * The image style's effect that was applied on it.
   *
   * @var null|\MovLib\Data\Image\ImageEffect
   */
  public $effect;

  /**
   * The image style's route in the current locale.
   *
   * @var string
   */
  public $route;

  /**
   * The image style's URI.
   *
   * @var null|string
   */
  public $uri;

  /**
   * The image style's URL.
   *
   * @var string
   */
  public $url;

  /**
   * The image style's width in Pixel.
   *
   * @var integer
   */
  public $width;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image style.
   *
   * @param string $uri
   *   The image style's URI.
   * @param integer $width
   *   The image style's width in Pixel.
   * @param integer $height
   *   The image style's height in Pixel.
   */
  public function __construct($uri, $width, $height) {
    $this->height = $height;
    $this->uri    = $uri;
    $this->width  = $width;
  }

  /**
   * Called if this object is serialized.
   *
   * @return array
   *   Array containing the names of the propertyies that should be serialized.
   */
  public function __sleep() {
    return [ "height", "effect", "width" ];
  }

}
