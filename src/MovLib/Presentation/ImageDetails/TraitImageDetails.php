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

namespace MovLib\Presentation\ImageDetails;

use \MovLib\Data\AbstractImage;
use \MovLib\Data\MovieImages;
use \MovLib\Presentation\Partial\Lists;

/**
 * Base trait for all image details presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitImageDetails {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The already translated route for edit actions.
   *
   * @var string
   */
  public $editRoute;

  /**
   * The title of the entity the image belongs to.
   *
   * @var string
   */
  public $entityTitle;

  /**
   * The image to display.
   *
   * @var \MovLib\Data\AbstractImage
   */
  protected $image;

  /**
   * The already translated route for images without the image ID portion.
   *
   * @var string
   */
  protected $imagesRoute;

  /**
   * The ID of the last image.
   *
   * @var int
   */
  protected $lastImageId;

  /**
   * The name pattern with replacement parameters for:
   * <ul>
   *   <li>The number of the current image.</li>
   *   <li>The total count of the images.</li>
   *   <li>The entity title.</li>
   * </ul>
   *
   * @var string
   */
  protected $namePattern;

  /**
   * The stream images.
   *
   * @var \MovLib\Data\AbstractImages
   */
  protected $streamImages;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /*
   * Get the image details.
   *
   * @return array
   *   The image details ready for printing a description list with <code>\MovLib\Presentation\Partial\Lists</code>.
   */
  protected abstract function getImageDetails();

  /**
   * Get the stream images.
   *
   * @param int $imageId
   *   The image ID to start the stream from.
   * @param int $paginationSize
   *   The stream pagination size.
   * @return array
   *   Numeric array containing the stream images.
   */
  protected abstract function getStreamImages($imageId, $paginationSize);

  private function pager($direction, $id, $text) {
    return
      "<a class='imagedetails-pager' id='imagedetails-{$direction}' href='{$this->imagesRoute}/{$id}'>" .
        "<i class='icon icon--chevron-{$direction}'></i><span class='visuallyhidden'>{$text}</span>" .
      "</a>"
    ;
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent() {
    global $i18n;
    if ($this->model->deleted === true) {
      return $this->getGoneContent();
    }

    $previous = $next = null;
    if ($this->image->imageId > 1) {
      $previous = $this->pager("left", ($this->image->imageId - 1), $i18n->t("Previous Image"));
    }
    if ($this->image->imageId < $this->lastImageId) {
      $next = $this->pager("right", ($this->image->imageId + 1), $i18n->t("Next Image"));
    }

    $activeImageIndex = MovieImages::STREAM_IMAGE_COUNT / 2;
    $stream = array_fill(0, MovieImages::STREAM_IMAGE_COUNT + 1, "<span class='span span--1'></span>");
    $stream[$activeImageIndex] = $this->getImage(
      $this->image,
      AbstractImage::IMAGESTYLE_DETAILS_STREAM,
      null,
      $this->image->imageUri,
      [ "alt" => $this->image->imageAlt, "class" => "active span span--1" ]
    );
    $streamImages = $this->getStreamImages($this->image->imageId);
    for ($i = 0; $i < MovieImages::STREAM_IMAGE_COUNT; ++$i) {
      if (!isset($streamImages[$i])) {
        break;
      }
      $index = $streamImages[$i]["image_id"] - $this->image->imageId + $activeImageIndex;
      $gradientClass = null;
      if ($index === 0) {
        $gradientClass = "gradient-left ";
      }
      elseif ($index === MovieImages::STREAM_IMAGE_COUNT) {
        $gradientClass = "gradient-right ";
      }
      $stream[$index] = "<a class='{$gradientClass}span span--1' href='{$this->imagesRoute}/{$streamImages[$i]["image_id"]}'><img src='{$streamImages[$i]["src"]}'></a>";
    }
    $stream = implode("", $stream);

    return
      "<div id='image-details--stream'>{$stream}</div>" .
      "<div id='image-details--image'>{$previous}{$this->getImage(
        $this->image,
        AbstractImage::IMAGESTYLE_DETAILS,
        [ "alt" => "{$this->entityTitle} {$this->image->imageAlt}" ],
        $this->image->imageUri
      )}{$next}</div>" .
      (new Lists($this->getImageDetails(), "", [ "class" => "dl--horizontal", "id" => "image-details--description" ]))->getDescriptionList()
    ;
  }

}
