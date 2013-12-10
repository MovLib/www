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

use \MovLib\Data\UnixShell as sh;
use \MovLib\Data\Image\Style;

/**
 * Default image implementation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractImage extends \MovLib\Data\Image\AbstractBaseImage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The image's alternative text.
   *
   * @var string
   */
  protected $alternativeText;

  /**
   * The image's creation timestamp.
   *
   * @var int
   */
  protected $created;

  /**
   * The image's translated description.
   *
   * @var string
   */
  public $description;

  /**
   * The image's identifier (unique together with the associated entity).
   *
   * @var integer
   */
  public $id;

  /**
   * The image's license identifier.
   *
   * @var integer
   */
  public $licenseId;

  /**
   * The image's route to its own details page or to the upload page if this image doesn't exist yet.
   *
   * @var string
   */
  public $route;

  /**
   * The photo's source URL.
   *
   * @var string
   */
  public $source;

  /**
   * The image's upvotes.
   *
   * @var integer
   */
  public $upvotes;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @return this
   */
  protected abstract function generateStyles($source);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create the private and public upload directories for this image.
   *
   * @global \MovLib\Kernel $kernel
   * @return $this
   */
  protected function createDirectories() {
    global $kernel;
    sh::execute("mkdir -p '{$kernel->documentRoot}/private/upload/{$this->directory}' '{$kernel->documentRoot}/public/upload/{$this->directory}'");
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getStyle($style = self::STYLE_SPAN_02) {
    // Use the style itself for width and height if this image doesn't exist.
    if ($this->exists === false) {
      $this->styles[$style]["width"] = $this->styles[$style]["height"] = $style;
    }
    elseif (!is_array($this->styles)) {
      $this->styles = unserialize($this->styles);
    }

    // Use cache entry if we already generated this style once.
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $this->alternativeText,
        $this->getURL($style),
        $this->styles[$style]["width"],
        $this->styles[$style]["height"],
        $this->exists,
        $this->route
      );
    }

    return $this->stylesCache[$style];
  }

  /**
   * Move the originally uploaded file from the system temporary folder to the persistent storage.
   *
   * @delayed
   * @param string $source
   *   Absolute path to the uploaded image (already stripped and repaged).
   * @return $this
   */
  public function moveOriginal($source) {
    if (sh::execute("mv '{$source}' '{$this->getPath()}' && rm '{$source}'") === false) {
      error_log("Couldn't move uploaded image from temporary folder to persistent storage.");
    }
    return $this;
  }

  /**
   * Upload the <var>$source</var>, overriding any existing image.
   *
   * @global \MovLib\Kernel $kernel
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param string $extension
   *   The three letter image extension (e.g. <code>"jpg"</code>).
   * @param integer $height
   *   The height of the uploaded image in pixels.
   * @param integer $width
   *   The width of the uploaded image in pixels.
   * @return this
   * @throws \LogicException
   * @throws \RuntimeException
   */
  public function upload($source, $extension, $height, $width) {
    global $kernel;

    // We have to export the extension to class scope in order to move the original image.
    $this->extension = $extension;

    // Clean the uploaded image, ImageMagick needs the extension to determine the algorithm.
    sh::execute("convert '{$source}' -strip +repage '{$source}.{$extension}'");
    sh::executeDetached("rm '{$source}'");
    $source .= ".{$extension}";

    // Collect all data we want to know about the newly uploaded image.
    $this->changed  = $this->created = $_SERVER["REQUEST_TIME"];
    $this->height   = $height;
    $this->filesize = filesize($source);
    $this->width    = $width;

    // Let the concrete class create the various image styles.
    $this->generateStyles($source);
    if (!isset($this->styles[self::STYLE_SPAN_01]) || !isset($this->styles[self::STYLE_SPAN_02])) {
      throw new \LogicException("Every image instance has to generate the default styles!");
    }

    // Must be last because extending classes use it to determine if they have to update or insert.
    $this->exists = true;

    $kernel->delayMethodCall([ $this, "moveOriginal" ], [ $source ]);
    return $this;
  }


}
