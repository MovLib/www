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

use \MovLib\Core\FileSystem;
use \MovLib\Core\Shell;

/**
 * Defines the image effect object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ImageEffect {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Whether this effect requires cropping or not.
   *
   * @var boolean
   */
  public $crop = false;

  /**
   * The image effects filter.
   *
   * @var string
   */
  public $filter = "Lanczos";

  /**
   * The image effect's height in Pixel.
   *
   * @var null|integer
   */
  public $height;

  /**
   * The image effect's quality.
   *
   * @var integer
   */
  public $quality = 80;

  /**
   * The image effect's width in Pixel.
   *
   * @var integer
   */
  public $width;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new image effect object.
   *
   * @param integer $width
   *   The image effect's width.
   * @param integer $height [optional]
   *   The image effect's height, defaults to <code>NULL</code>.
   * @param boolean $crop [optional]
   *   Whether this image effect requires cropping or not, defaults to <code>FALSE</code>.
   */
  public function __construct($width, $height = null, $crop = false) {
    $this->crop   = $crop;
    $this->height = $height;
    $this->width  = $width;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply the image effect.
   *
   * @param \MovLib\Core\FileSystem $fs
   *   The active file system instance.
   * @param string $source
   *   URI or canonical absolute path of the source image.
   * @param string $destination
   *   URI or canonical absolute path of the destination image with the applied effect.
   * @return this
   * @throws \MovLib\Exception\ShellException
   *   If the ImageMagick call fails.
   */
  public function apply(\MovLib\Core\FileSystem $fs, $source, $destination) {
    // Build the ImageMagick resize argument.
    if ($this->crop) {
      $resize = "'{$this->width}x{$this->height}>^' -gravity 'Center' -crop '{$this->width}x{$this->height}+0+0' +repage";
    }
    else {
      $resize = "'{$this->width}x{$this->height}>'";
    }

    // Create the destination's directory if it doesn't exist.
    $directory = dirname($destination);
    if (is_dir($directory) === false) {
      mkdir($directory, FileSystem::MODE_DIR, true);
    }

    // Build canonical absolute paths to source and destination.
    $s = escapeshellarg($fs->realpath($source));
    $d = escapeshellarg($fs->realpath($destination));

    // Try to execute the ImageMagick command.
    (new Shell())->execute("convert {$s} -filter '{$this->filter}' -resize {$resize} -quality {$this->quality} {$d}");

    return $this;
  }

}
