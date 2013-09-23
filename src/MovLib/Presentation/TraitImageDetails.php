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

namespace MovLib\Presentation;

use \MovLib\Data\AbstractImage;
use \MovLib\Presentation\Partial\Lists;

/**
 * Base trait for all image details presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitImageDetails {

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
   * Numeric array containing the stream images as <code>\MovLib\Data\Abstract</code> objects.
   *
   * @var array
   */
  protected $streamImages;

  /**
   * @inheritdoc
   */
  protected function init($title) {
    $this->stylesheets[] = "modules/image-details.css";
    return parent::init($title);
  }

  /*
   * Get the image details.
   *
   * @return array
   *   The image details ready for printing a description list with <code>\MovLib\Presentation\Partial\Lists</code>.
   */
  protected abstract function getImageDetails();

  protected function getImageStream() {
    return "";
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
    /** @todo Display stream, image and details. */
    return
      "<div id='image-details--stream'>{$this->getImageStream()}</div>" .
      "<div id='image-details--image'>{$this->getImage(
        $this->image,
        AbstractImage::IMAGESTYLE_DETAILS,
        [ "alt" => "{$this->entityTitle} {$this->image->imageAlt}" ],
        $this->image->imageUri
      )}</div>" .
      (new Lists($this->getImageDetails(), "", [ "class" => "dl--horizontal", "id" => "image-details--description" ]))->toDescriptionList()
    ;
  }

}
