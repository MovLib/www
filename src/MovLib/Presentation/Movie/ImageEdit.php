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
namespace MovLib\Presentation\Movie;

/**
 * @todo Description of ImageEdit
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ImageEdit extends \MovLib\Presentation\Movie\Image {
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
   * Initialize movie image edit page.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \MovLib\Presentation\Error\NotFound
   */
  protected function initImagePage() {
    global $i18n;

    // Try to load the movie with the identifier from the request and set the breadcrumb title.
    $this->initMoviePage($i18n->t("Edit {image_type_name}", [ "image_type_name" => $this->imageTypeName ]));

    // Create absolute class name for the image and try to load it with the identifier from the request.
    $class       = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image = new $class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);

    // Initialize the page without anchor in it and then set the page title with the anchors.
    $this->initPage($i18n->t("Edit {title} {image_type_name} {id}", [
      "title" => $this->movie->displayTitleWithYear,
      "id" => $i18n->format("{0,number}", [ $this->image->id ]),
      "image_type_name" => $this->imageTypeName,
    ]));
    $this->pageTitle = $i18n->t("Edit {title} {image_type_name} {id}", [
        "title"           => "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>",
        "id"              => "<a href='{$i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $this->image->id])}'>{$this->image->id}</a>",
        "image_type_name" => $this->imageTypeName,
    ]);

    // Initialize the language links for this page.
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id]);

    // Add the necessary trails to the breadcrumb.
    $this->breadcrumb->menuitems[] = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id]), $this->imageTypeNamePlural];
    $this->breadcrumb->menuitems[] = [ $i18n->r("/movie/{0}/{$this->routeKey}/{1}", [ $this->movie->id, $this->image->id ]), "{$this->imageTypeName} {$this->image->id}" ];

    // Instantiate the additional input elements and initialize the upload form.
    $this->selectCountry  = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);
    $this->selectLanguage = new Select("language", $i18n->t("Language"), Language::getLanguages(), $this->image->languageCode ?: "xx", [ "required"]);
    $this->initUpload([ $this->selectCountry, $this->selectLanguage]);

    // Lastly initialize the sidebar.
    return $this->initSidebar();
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
