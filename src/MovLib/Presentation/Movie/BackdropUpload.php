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
class BackdropUpload extends \MovLib\Presentation\Movie\Backdrops {
  use \MovLib\Presentation\TraitFormPage;
  use \MovLib\Presentation\TraitUpload;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The concrete image that is going to be uploaded.
   *
   * @var \MovLib\Data\Image\AbstractImage
   */
  protected $image;

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

    // Try to load the movie with the identifier from the request and set the breadcrumb title.
    $this->initMoviePage($i18n->t("Upload New {image_type_name}", [ "image_type_name" => $this->imageTypeName]));

    // Create absolute for the image and instantiate an empty one.
    $class       = "\\MovLib\\Data\\Image\\Movie{$this->imageClassName}";
    $this->image = new $class($this->movie->id, $this->movie->displayTitleWithYear);

    // Translate the title once, we don't need Intl for replacing. The head title has no anchors and the page title has.
    $title           = $i18n->t("Upload new {image_type_name} for {title}");
    $search          = [ "{image_type_name}", "{title}" ];
    $this->initPage(str_replace($search, [ $this->imageTypeName, $this->movie->displayTitleWithYear ], $title));
    $this->pageTitle = str_replace($search, [ $this->imageTypeName, "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>" ], $title);

    // Initialize the language links for this page.
    $this->initLanguageLinks("/movie/{0}/{$this->routeKey}/upload", [ $this->movie->id]);

    // Add entry to parent page.
    $this->breadcrumb->menuitems[] = [ $i18n->rp("/movie/{0}/{$this->routeKeyPlural}", [ $this->movie->id]), $this->imageTypeNamePlural];

    // Instantiate additional input elments and initialize the upload form.
    $this->selectCountry  = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);
    $this->selectLanguage = new Select("language", $i18n->t("Language"), Language::getLanguages(), $this->image->languageCode ?: "xx", [ "required"]);
    $this->initUpload([ $this->selectCountry, $this->selectLanguage]);

    // Initialize the sidebar.
    return $this->initSidebar();
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\User\Session $session
   */
  protected function valid() {
    global $session;
    $this->image->countryCode  = $this->selectCountry->value;
    $this->image->description  = $this->inputDescription->value;
    $this->image->languageCode = $this->selectLanguage->value;
    $this->image->uploaderId   = $session->userId;
    $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
    throw new SeeOtherRedirect($this->image->route);
  }

}
