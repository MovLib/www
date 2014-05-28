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
use \MovLib\Exception\FileSystemException;

/**
 * Defines the base class for all image effects.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImageEffect {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractImageEffect";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image to apply the style on.
   *
   * @var \MovLib\Data\Image\AbstractReadOnlyImageEntity
   */
  protected $image;

  /**
   * The name of the style that the concrete image gave this image effect.
   *
   * @var string
   */
  protected $styleName;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods



  /**
   * Called if this object is serialized.
   *
   * @return array
   *   Array containing the names of the properties that should be serialized.
   */
  public function __sleep() {
    return [ "styleName" ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Convert the source image to the image effect.
   *
   * @param \MovLib\Core\Shell $shell
   *   A shell instance to execute commands.
   * @param string $source
   *   Canonical absolute path to the source file.
   * @param string $destination
   *   Canonical absolute path to the destination file.
   * @return this
   */
  abstract protected function convert(\MovLib\Core\Shell $shell, $source, $destination);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Apply the image effect.
   *
   * @param \MovLib\Data\Image\AbstractReadOnlyImageEntity $image
   *   The image to apply the effect on.
   * @param \MovLib\Core\FileSystem $fs
   *   The active file system instance.
   * @param string $styleName
   *   The image style's name.
   * @param string $source
   *   URI or canonical absolute path of the source image.
   * @param string $destination
   *   URI or canonical absolute path of the destination image with the applied effect.
   * @return this
   * @throws \MovLib\Exception\ShellException
   *   If the ImageMagick call fails.
   */
  public function apply(\MovLib\Data\Image\AbstractReadOnlyImageEntity $image, \MovLib\Core\FileSystem $fs, $styleName, $source, $destination) {
    static $shell = null;
    $this->image     = $image;
    $this->styleName = $styleName;
    // Create the destination's directory if it doesn't exist.
    $directory = dirname($destination);
    if (!is_dir($directory)) {
      mkdir($directory, FileSystem::MODE_DIR, true);
    }
    if (!$shell) {
      $shell = new Shell();
    }
    if (!($source = $fs->realpath($source))) {
      throw new FileSystemException("Couldn't generate real path for source file '{$source}'!");
    }
    if (!($destination = $fs->realpath($destination))) {
      throw new FileSystemException("Couldn't generate real path for destination file '{$destination}'!");
    }
    return $this->convert($shell, $source, $destination);
  }

}
