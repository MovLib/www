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
  public $created;

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
   * Generate all supported image styles.
   *
   * @param string $source
   *   Absolute path to the uploaded image.
   * @return this
   */
  protected abstract function generateStyles($source);

  /**
   * Update the existing image's database record.
   *
   * @return this
   */
  protected abstract function update();


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
   * Deletes the original image, all styles and the directory (if empty) from the persistent storage.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function delete() {
    global $kernel;

    // Unserialize the styles if they are still serialized.
    if (!is_array($this->styles)) {
      $this->styles = unserialize($this->styles);
    }

    // Add the original file to the styles array (DRY), this is why getImagePath() and getImageURL() check with empty()
    // against their parameter.
    sh::execute("rm -rf '{$kernel->documentRoot}/private/upload/{$this->directory}' '{$kernel->documentRoot}/public/upload/{$this->directory}'");

    $this->imageExists = false;
    $this->styles = null;
    return $this;
  }

  /**
   * Delete all generated styles.
   *
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function deleteStyles() {
    global $kernel;
    sh::execute("rm -f '{$kernel->documentRoot}/public/upload/{$this->directory}/*'");
    $this->styles = null;
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
    // The image exists but we're missing this particular style. We assume that the styles have changed for this image
    // and therefore delete all of them and generate them again.
    //
    // @todo It would be more efficient to generate only the missing style, but that would break the generation chain
    //       (from best quality down to worst quality to get best quality for each resized image).
    elseif (!isset($this->styles[$style])) {
      $this->deleteStyles()->generateStyles($this->getPath());
    }

    if (!is_array($this->styles)) {
      $this->styles = unserialize($this->styles);
    }

    // Use cache entry if we already generated this style once.
    if (!isset($this->stylesCache[$style])) {
      $this->stylesCache[$style] = new Style(
        $this->alternativeText,
        $this->getURL($style),
        $this->styles[$style]["width"],
        $this->styles[$style]["height"],
        $this->imageExists,
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

    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($this->styles[self::STYLE_SPAN_01]) || !isset($this->styles[self::STYLE_SPAN_02])) {
      throw new \LogicException("Every image instance has to generate the default styles!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Must be last because extending classes use it to determine if they have to update or insert.
    $this->imageExists = true;

    $kernel->delayMethodCall([ $this, "moveOriginal" ], [ $source ]);
    return $this;
  }

}
