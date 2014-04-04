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
 * Default image style implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ImageStyle {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image style's default alternative text.
   *
   * @var string
   */
  public $alt;

  /**
   * The image style's height in Pixel.
   *
   * @var integer
   */
  public $height;

  /**
   * Whether a placeholder image is in use or not.
   *
   * @var boolean
   */
  public $placeholder;

  /**
   * The image's default route arguments.
   *
   * @var mixed
   */
  protected $routeArguments;

  /**
   * The image's default route key.
   *
   * @var string
   */
  protected $routeKey;

  /**
   * The image style's URL.
   *
   * @var string
   */
  public $src;

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
   * @param type $url
   * @param type $alternativeText
   * @param type $width
   * @param type $height
   * @param type $placeholder
   * @param type $routeKey
   * @param type $routeArguments
   */
  public function __construct($url, $alternativeText, $width, $height, $placeholder, $routeKey, $routeArguments) {
    $this->alt            = $alternativeText;
    $this->height         = $height;
    $this->placeholder    = (boolean) $placeholder;
    $this->routeArguments = $routeArguments;
    $this->routeKey       = $routeKey;
    $this->src            = $url;
    $this->width          = $width;
  }

}
