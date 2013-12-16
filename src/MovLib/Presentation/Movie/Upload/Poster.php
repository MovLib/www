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
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Data\Movie\Movie;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\License;
use \MovLib\Presentation\Partial\Language;

/**
 * Form to upload a new poster or update an existing one.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Poster extends \MovLib\Presentation\Movie\AbstractMoviePage {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form's country selection.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $country;

  /**
   * The form's description input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $description;

  /**
   * The concrete image (namely the poster in this class, but not in child classes).
   *
   * @var \MovLib\Data\Image\MoviePoster
   */
  protected $image;

  /**
   * The form's file input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The form's language selection.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $language;

  /**
   * The movie this image belongs to.
   *
   * @var \MovLib\Data\Movie\Movie
   */
  protected $movie;

  /**
   * The form's license selection.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $license;

  /**
   * Translated short title for this presentation.
   *
   * @var string
   */
  protected $shortTitle;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new upload poster presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;

    // Try to load referenced movie
    try {
      $this->movie = new Movie($_SERVER["MOVIE_ID"]);
    }
    catch (\OutOfBoundsException $e) {
      throw $e;
    }

    // Initialize presentation
    $this->image      = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear);
    $this->shortTitle = $i18n->t("Upload Poster");
    $this->init($i18n->t("Upload poster for {movie_title}", [ "movie_title" => $this->movie->displayTitleWithYear ]), $this->shortTitle);
    $this->pageTitle  = $i18n->t("Upload poster for {movie_title}", [ "movie_title" => "<a href='{$this->routeMovie}'>{$this->movie->displayTitleWithYear}</a>" ]);

    // Alter the sidebar navigation and include the various image types.
    $this->sidebarNavigation->menuitems[0][1] = $i18n->t("Back to movie");
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]), $i18n->t("Posters"), [ "class" => "active" ] ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/lobby-cards", [ $this->movie->id ]), $i18n->t("Lobby Cards") ];
    $this->sidebarNavigation->menuitems[] = [ $i18n->rp("/movie/{0}/photos", [ $this->movie->id ]), $i18n->t("Photos") ];

    // Initialize form elements.
    $this->inputImage  = new InputImage("poster", $i18n->t("Poster"), $this->image, [ "required" ]);
    $this->description = new InputHTML("description", $i18n->t("Description")); //, [ "required" ]
    $this->country     = new Select("country", $i18n->t("Country"), Country::getCountries());
    $this->language    = new Select("language", $i18n->t("Language"), Language::getLanguages(), null, [ "required" ]);
    $this->license     = new Select("license", $i18n->t("License"), License::getLicenses(), 1, [ "required" ]);

    // Initialize form
    $this->form = new Form($this, [
      $this->inputImage,
      $this->description,
      $this->country,
      $this->language,
      $this->license,
    ]);

    // Add submit button
    $this->form->actionElements[] = new InputSubmit($this->shortTitle, [
      "class" => "btn btn-large btn-success",
      "title" => $i18n->t("Continue here after you filled out all mandatory fields."),
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getBreadcrumbs() {
    global $i18n;
    $trail = parent::getBreadcrumbs();
    $trail[] = [ $this->routeMovie, $this->movie->displayTitleWithYear ];
    $trail[] = [ $i18n->rp("/movie/{0}/posters", [ $this->movie->id ]), $i18n->t("Posters") ];
    return $trail;
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

  /**
   * @inheritdoc
   */
  public function validate(array $errors = null) {
    if ($this->checkErrors($errors) === false) {
      $this->image->countryCode  = $this->country->value;
      $this->image->description  = $this->description->value;
      $this->image->languageCode = $this->language->value;
      $this->image->licenseId    = $this->license->value;
      $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
      throw new RedirectSeeOtherException($this->image->route);
    }
    return $this;
  }

}
