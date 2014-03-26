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

use \MovLib\Data\Movie\Movie;
use \MovLib\Presentation\Partial\Country;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputDateSeparate;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\Language;
use \MovLib\Presentation\Redirect\SeeOther;

/**
 * Base class for all movie upload (and edit) classes.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ImageUpload extends \MovLib\Presentation\Movie\AbstractBase {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The class name of the image we're going to upload.
   *
   * @var string
   */
  protected $class;

  /**
   * The image we're going to upload.
   *
   * @var \MovLib\Data\Image\AbstractMovieImage
   */
  protected $image;

  /**
   * The input image form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The input description form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputDescription;

  /**
   * The select country from element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $inputCountryCode;

  /**
   * The select language form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $inputLanguageCode;

  /**
   * The input date form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputDateSeparate
   */
  protected $inputPublishedDate;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movie image upload presentation.
   *
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    // Try to load movie and image.
    $this->movie = new Movie((integer) $_SERVER["MOVIE_ID"]);
    $this->class = "\\MovLib\\Data\\Image\\Movie{$_SERVER["IMAGE_CLASS"]}";
    $this->image = new $this->class($this->movie->id, $this->movie->displayTitleWithYear);

    // Translate title once.
    switch ($this->image->routeKey) {
      case "poster":
        $title = $this->intl->t("Upload new poster for {title}");
        break;

      case "lobby-card":
        $title = $this->intl->t("Upload new lobby card for {title}");
        break;

      case "backdrop":
        $title = $this->intl->t("Upload new backdrop for {title}");
        break;
    }

    // Initialize the page, no need for micro-data as this page is only accessible for authenticated users and no bots.
    $this->initPage(str_replace("{title}", $this->movie->displayTitleWithYear, $title));
    $this->pageTitle = str_replace("{title}", "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>", $title);

    // Initialize the rest of the page.
    $this->initLanguageLinks("/movie/{0}/{$this->image->routeKey}/upload", [ $this->movie->id ]);
    $this->initBreadcrumb([[ $this->intl->rp("/movie/{0}/{$this->image->routeKeyPlural}", [ $this->movie->id ]), $this->image->namePlural ]]);
    $this->breadcrumbTitle = $this->intl->t("Upload");
    $this->initSidebar();

    // Initialize the upload form.
    $this->inputImage             = new InputImage("image", $this->image->name, $this->image, [ "required" => true ]);
    $this->inputDescription       = new InputHTML("description", $this->intl->t("Description"), $this->image->description);
    $this->inputDescription->allowExternalLinks()->allowLists();
    $this->inputCountryCode       = new Select("country", $this->intl->t("Country"), Country::getCountries(), $this->image->countryCode);
    $this->inputLanguageCode      = new Select("language", $this->intl->t("Language"), Language::getLanguages(), $this->image->languageCode, [ "required" => true ]);
    $this->inputPublishedDate     = new InputDateSeparate("date", $this->intl->t("Publishing Date"), $this->image->publishingDate, null, [ "year_max" => date("Y"), "year_min" => 1800 ]);
    $this->form                   = new Form($this, [ $this->inputImage, $this->inputDescription, $this->inputCountryCode, $this->inputLanguageCode, $this->inputPublishedDate ]);
    $this->form->multipart();
    $this->form->actionElements[] = new InputSubmit($this->intl->t("Upload"), [ "class" => "btn btn-large btn-success" ]);
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @return string
   *   The page's content.
   */
  protected function getPageContent() {
    return "{$this->form}<p>{$this->intl->t(
      "By clicking on upload you confirm that you created this photo or scan yourself. For more information about the " .
      "licensing of your contributions read our {terms_of_use}.",
      [ "terms_of_use" => "<a href='{$this->intl->r("/terms-of-use")}'>{$this->intl->t("Terms of Use")}</a>" ]
    )}</p>";
  }

  /**
   * Upload image after form validation.
   *
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function valid() {
    $this->image->description    = $this->inputDescription->value;
    $this->image->countryCode    = $this->inputCountryCode->value;
    $this->image->languageCode   = $this->inputLanguageCode->value;
    $this->image->publishingDate = $this->inputPublishedDate->value;
    $this->image->uploaderId     = $session->userId;
    $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
    throw new SeeOther($this->image->route);
  }

}
