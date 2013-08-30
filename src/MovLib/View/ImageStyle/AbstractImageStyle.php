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
namespace MovLib\View\ImageStyle;

/**
 * Description of AbstractImageStyle
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageStyle {

  /**
   * The dimensions of this style.
   *
   * @var string
   */
  public $dimensions;

  /**
   * The target image's height.
   * @var int
   */
  public $height;

  /**
   * The target path of the image to convert.
   *
   * @var string
   */
  public $path;

  /**
   * The source path of the image to convert.
   *
   * @var string
   */
  public $sourcePath;

  /**
   * The output image's URI.
   *
   * @var string
   */
  public $uri;

  /**
   * The target image's width.
   * @var int
   */
  public $width;

  /**
   * Initialize a new image style.
   *
   * @param string $dimensions
   *   The dimensions of the output image in the format <code>"{$width}x{$height}"</code>. Please note that this string
   *   should not contain ImageMagick operators or it will fail.
   */
  public function __construct($dimensions) {
    $this->dimensions =  $dimensions;
  }

  /**
   * Convert the image.
   */
  public function convert() {
    if (!is_dir(($dir = dirname($this->path)))) {
      mkdir($dir);
      exec("chmod 777 {$dir}");
    }
    exec("convert {$this->sourcePath} {$this->getImageMagickOptions()} {$this->path} && chmod 777 {$this->path}");
    if (filesize($this->path) > 10240) {
      $this->interlace();
    }
  }

  /**
   * Get the ImageMagick options for the conversion
   *
   * @return string
   *   The ImageMagick options for the conversion
   */
  protected abstract function getImageMagickOptions();

    /**
   * Interlace the target image.
   */
  private function interlace() {
    exec("convert {$this->path} -interlace 'line' {$this->path}");
  }

}