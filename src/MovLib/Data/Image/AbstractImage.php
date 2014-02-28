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
   * @var integer
   */
  public $created;

  /**
   * Whether this image was deleted or not.
   *
   * There is a huge difference between an image that never existed in the first place and a deleted image. Every and
   * any image always stays in our system and we only change the deleted attribute in the database (plus delete all
   * generated styles). There's also a different error page displayed on the web page for the deleted image.
   *
   * @todo We also need the possibility to look deleted images if it comes to recovery. Total spam material should be
   *       locked or their IDs reused.
   * @var boolean
   */
  public $deleted = false;

  /**
   * The deletion request's unique identifier (if any).
   *
   * @var null|integer
   */
  public $deletionId;

  /**
   * The image's translated description.
   *
   * @var string
   */
  public $description;

  /**
   * The image's translated description's language code.
   *
   * @var string
   */
  public $descriptionLanguageCode;

  /**
   * The image's identifier (unique together with the associated entity).
   *
   * @var integer
   */
  public $id;

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
   * The unique uploader (user) identifier.
   *
   * @var integer
   */
  public $uploaderId;

  /**
   * The image's upvotes.
   *
   * @var integer
   */
  public $upvotes;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Delete the image.
   *
   * @return this
   */
  abstract public function delete();

  /**
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @param boolean $regenerate [optional]
   *   Whether to regenerate existing styles.
   * @return this
   */
  abstract protected function generateStyles($source, $regenerate = false);

  /**
   * Set deletion request's unique identifier.
   *
   * @param integer $id
   *   The deletion request's unique identifier.
   * @return this
   */
  abstract public function setDeletionRequest($id);


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Create the private and public upload directories for this image.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function createDirectories() {
    global $kernel;
    sh::execute("mkdir -p '{$kernel->documentRoot}/private/upload/{$this->directory}' '{$kernel->documentRoot}/public/upload/{$this->directory}'");
    return $this;
  }

  /**
   * Delete all image styles and the directory.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function deleteImageStyles() {
    global $kernel;

    // Unserialize the styles if they are still serialized.
    if (!is_array($this->styles)) {
      $this->styles = unserialize($this->styles);
    }

    // Absolute path to the styles directory of this image.
    $directoryPath = "{$kernel->documentRoot}/public/upload/{$this->directory}";

    // Remove all generated image styles from persistent storage, we can easily regenerate them if we have to recover
    // the record from the original upload.
    if (sh::execute("rm --force --recursive '{$directoryPath}/*'") === false) {
      error_log(new \RuntimeException("Couldn't delete image styles for: '{$this->directory}'"));
    }

    // Delete all empty directories within the complete path to the deleted image styles. This silently fails upon the
    // first directory that's non empty.
    sh::execute("rmdir --ignore-fail-on-non-empty --parent '{$directoryPath}'");

    // Update the instance properties as well.
    $this->imageExists = false;
    $this->styles      = null;

    return $this;
  }

  /**
   * Get the <var>$style</var> for this image.
   *
   * @param mixed $style
   *   The desired style, use the objects <var>STYLE_*</var> class constants. Defaults to <var>STYLE_SPAN_02</var>.
   * @return \MovLib\Data\Image\Style
   *   The image's desired style object.
   */
  public function getStyle($style = self::STYLE_SPAN_02) {
    // Use the style itself for width and height if this image doesn't exist.
    if ($this->imageExists === false && !isset($this->styles[$style])) {
      if (!is_array($this->styles)) {
        $this->styles = [];
      }
      $this->styles[$style] = [ "width" => $style, "height" => $style ];
    }
    elseif (!is_array($this->styles)) {
      $this->styles = unserialize($this->styles);

      // If this style is missing, assume that the styles have changed and regenerate them.
      if (!isset($this->styles[$style])) {
        $this->generateStyles($this->getPath(), true);
      }

      // @devStart
      // @codeCoverageIgnoreStart
      if (!is_file($this->getPath($style))) {
        $this->generateStyles($this->getPath(), true);
      }
      // @devEnd
      // @codeCoverageIgnoreEnd
    }

    // Use cache entry if we already generated this style once.
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $this->alternativeText,
        $this->getURL($style),
        $this->styles[$style]["width"],
        $this->styles[$style]["height"],
        !$this->imageExists,
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
      error_log(__FILE__ . "(" . __LINE__ . "): Couldn't move uploaded image from temporary folder to persistent storage.");
      // @devStart
      // @codeCoverageIgnoreStart
      throw new \LogicException("Couldn't move uploaded image from temporary folder to persistent storage.");
      // @codeCoverageIgnoreEnd
      // @devEnd
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
    sh::execute("convert '{$source}' +profile 'icm' -strip +repage -units PixelsPerInch image -density '72' '{$source}.{$extension}'");
    sh::executeDetached("rm '{$source}'");
    $source .= ".{$extension}";

    // Collect all data we want to know about the newly uploaded image.
    $this->changed  = $this->created = $_SERVER["REQUEST_TIME"];
    $this->height   = $height;
    $this->filesize = filesize($source);
    $this->width    = $width;

    // Let the concrete class create the various image styles.
    $this->generateStyles($source);
    $this->imageExists = true;

    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($this->styles[self::STYLE_SPAN_01]) || !isset($this->styles[self::STYLE_SPAN_02])) {
      throw new \LogicException("Every image instance has to generate the default styles!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $kernel->delayMethodCall([ $this, "moveOriginal" ], [ $source ]);
    return $this;
  }

}
