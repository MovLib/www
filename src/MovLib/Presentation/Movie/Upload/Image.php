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
namespace MovLib\Presentation\Movie\Upload;

use \MovLib\Data\Country;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\Language;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * Form to upload a new or edit an existing movie image.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Image extends \MovLib\Presentation\Movie\Gallery\Images {
  use \MovLib\Presentation\TraitUpload;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The select form element for the country.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectCountry;

  /**
   * The select form element for the language.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectLanguage;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

  /**
   * Initialize movie image upload page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Presentation\Error\NotFound
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

    $class                         = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image                   = new $class($this->movie->id, $this->movie->displayTitleWithYear, $imageId);
    $this->initPage($title);
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id]);
    $this->pageTitle               = $pageTitle;
    $this->breadcrumb->menuitems[] = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id]), $this->imageTypeNamePlural];
    if ($imageId) {
      $this->breadcrumb->menuitems[] = [
        $i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $imageId]),
        "{$this->imageTypeName} {$imageId}",
      ];
    }
    $this->selectCountry  = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);
    $this->selectLanguage = new Select("language", $i18n->t("Language"), Language::getLanguages(), $this->image->languageCode, [ "required"]);
    $this->initUpload($this->image, [ $this->selectCountry, $this->selectLanguage]);

    return $this;
  }

  /**
   * @inheritdoc
   */
  protected function valid() {
    $this->image->countryCode  = $this->selectCountry->value;
    $this->image->description  = $this->inputDescription->value;
    $this->image->languageCode = $this->selectLanguage->value;
    $this->image->licenseId    = $this->selectLicense->value;
    $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
    throw new SeeOtherRedirect($this->image->route);
  }

}
