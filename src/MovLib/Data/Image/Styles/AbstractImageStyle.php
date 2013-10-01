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
namespace MovLib\Data\Image\Styles;

/**
 * The default image style, strips meta data from the image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageStyle {


  // ------------------------------------------------------------------------------------------------------------------- Properties


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
   * The image style's path.
   *
   * @var string
   */
  public $path;

  /**
   * The image style's source image path.
   *
   * @var string
   */
  protected $source;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image style.
   *
   * @param string $sourcePath
   *   The absolute path to the source image.
   */
  public function __construct($sourcePath){
    $this->source = $sourcePath;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Convert the image.
   *
   * @return this
   */
  public function convert() {
    exec("convert {$this->source} {$this->getImageMagickOptions()} {$this->path} && chmod 777 {$this->path}");
    if (filesize($this->path) > 10240) {
      exec("convert {$this->path} -interlace 'line' {$this->path}");
    }
    return $this;
  }

  /**
   * Get the ImageMagick options for this image style.
   *
   * @return string
   *   The ImageMagick options for this image style.
   */
  abstract protected function getImageMagickOptions();

}
