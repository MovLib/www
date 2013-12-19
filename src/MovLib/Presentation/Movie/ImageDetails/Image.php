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
namespace MovLib\Presentation\Movie\ImageDetails;

/**
 * Present a single movie image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Image extends \MovLib\Presentation\Movie\Gallery\Images {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The movie image to present.
   *
   * @var \MovLib\Data\Image\MovieImage
   */
  protected $image;

  
  // ------------------------------------------------------------------------------------------------------------------- Methods
  
  
  /**
   * Get the page's content.
   * 
   * @return string
   */
  protected function getPageContent() {
    return "";
  }

  /**
   * Initialize the movie image show page.
   * 
   * @global \MovLib\Data\I18n $i18n
   * @return this
   */
  protected function initImagePage() {
    global $i18n;

    if (isset($_SERVER["IMAGE_ID"])) {
      $imageId   = $_SERVER["IMAGE_ID"];
      $this->initMoviePage($i18n->t("Edit {image_type_name}", [ "image_type_name" => $this->imageTypeName]));
      $title     = $i18n->t("Edit {title} {image_type_name} {id}", [
        "title"           => $this->movie->displayTitleWithYear,
        "id"              => $i18n->t("{0,number,integer}", [ $imageId]),
        "image_type_name" => $this->imageTypeName,
      ]);
      $pageTitle = $i18n->t("Edit {title} {image_type_name} {id}", [
        "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
        "id"              => "<a href='{$i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $imageId])}'>{$i18n->format("{0,number,integer}", [ $imageId])}</a>",
        "image_type_name" => $this->imageTypeName,
      ]);
    }
    else {
      $imageId   = null;
      $this->initMoviePage($i18n->t("Upload New {image_type_name}", [ "image_type_name" => $this->imageTypeName]));
      $title     = $i18n->t("Upload new {image_type_name} for {title}", [
        "title"           => $this->movie->displayTitleWithYear,
        "image_type_name" => $this->imageTypeName,
      ]);
      $pageTitle = $i18n->t("Upload new {image_type_name} for {title}", [
        "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
        "image_type_name" => $this->imageTypeName,
      ]);
    }

    $formattedId = $i18n->format("{0,number,integer}", [ $_SERVER["IMAGE_ID"] ]);
    $this->initMoviePage($i18n->t("{image_type_name} {id}", [ "id" => $formattedId, "image_type_name" => $this->imageTypeName ]));
    $class                         = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image                   = new $class($this->movie->id, $this->movie->displayTitleWithYear, $_SERVER["IMAGE_ID"]);
    $this->initPage($i18n->t("{image_type_name} {id} of {title}", [ "image_type_name" => $this->imageTypeName, "id" => $formattedId ]));
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id]);
    $this->pageTitle               = $i18n->t("{image_type_name} {id} of {title}", [
      "image_type_name" => $this->imageTypeName,
      "id"              => $formattedId,
      "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
    ]);
    $this->breadcrumb->menuitems[] = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id]), $this->imageTypeNamePlural];

    return $this;
  }

  /**
   * Valid form callback.
   * 
   * @return this
   */
  protected function valid() {
    return $this;
  }

}
