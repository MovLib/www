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
use \MovLib\Presentation\Partial\Alert;
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
class ImageEdit extends \MovLib\Presentation\Movie\AbstractBase {
  use \MovLib\Presentation\TraitFormPage;


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
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Exception\DatabaseException
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;

    // Try to load movie and image.
    $this->movie = new Movie((integer) $_SERVER["MOVIE_ID"]);
    $this->class = "\\MovLib\\Data\\Image\\Movie{$_SERVER["IMAGE_CLASS"]}";
    $this->image = new $this->class($this->movie->id, $this->movie->displayTitleWithYear, (integer) $_SERVER["IMAGE_ID"]);

    // Translate title once.
    $title       = $i18n->t("Upload new {image_name} for {title}");
    $search      = [ "{image_name}", "{title}" ];

    // Initialize the page, no need for micro-data as this page is only accessible for authenticated users and no bots.
    $this->initPage(str_replace($search, [ $this->image->name, $this->movie->displayTitleWithYear ], $title));
    $this->pageTitle = str_replace($search, [ $this->image->name, "<a href='{$this->movie->route}'>{$this->movie->displayTitleWithYear}</a>" ], $title);

    // Initialize the rest of the page.
    $this->initLanguageLinks("/movie/{0}/{$this->image->routeKey}/upload", [ $this->movie->id ]);
    $this->initBreadcrumb([[ $i18n->rp("/movie/{0}/{$this->image->routeKeyPlural}", [ $this->movie->id ]), $this->image->namePlural ]]);
    $this->breadcrumbTitle = $i18n->t("Upload");
    $this->initSidebar();

    // Initialize the upload form.
    $this->inputImage             = new InputImage("image", $this->image->name, $this->image);
    $this->inputDescription       = new InputHTML("description", $i18n->t("Description"), $this->image->description);
    $this->inputCountryCode       = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);
    $this->inputLanguageCode      = new Select("language", $i18n->t("Language"), Language::getLanguages(), $this->image->languageCode, [ "required" ]);
    $this->inputPublishedDate     = new InputDateSeparate("date", $i18n->t("Publishing Date"), [ "value" => $this->image->publishingDate ], date("Y"), 1800);
    $this->form                   = new Form($this, [ $this->inputImage, $this->inputDescription, $this->inputCountryCode, $this->inputLanguageCode, $this->inputPublishedDate ]);
    $this->form->multipart();
    $this->form->actionElements[] = new InputSubmit($i18n->t("Upload"), [ "class" => "btn btn-large btn-success" ]);
  }

  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return string
   *   The page's content.
   */
  protected function getPageContent() {
    global $i18n;
    return "{$this->form}<p>{$i18n->t(
      "By clicking on upload you confirm that you created this photo or scan yourself. For more information about the " .
      "licensing of your contributions read our {terms_of_use}.",
      [ "terms_of_use" => "<a href='{$i18n->r("/terms-of-use")}'>{$i18n->t("Terms of Use")}</a>" ]
    )}</p>";
  }

  /**
   * Upload image after form validation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function valid() {
    global $i18n, $kernel, $session;

    // Export everything to image class scope.
    $this->image->description    = $this->inputDescription->value;
    $this->image->countryCode    = $this->inputCountryCode->value;
    $this->image->languageCode   = $this->inputLanguageCode->value;
    $this->image->publishingDate = $this->inputPublishedDate->value;

    // Only attempt to upload an image if the user submitted a new one ...
    if ($this->inputImage->path) {
      $this->image->uploaderId = $session->userId;
      $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
    }
    // ... otherwise only update the image's properties.
    else {
      $this->image->commit();
    }

    // Let the user know that the update was successful and redirect to the image detail page.
    $kernel->alerts .= new Alert(null, $i18n->t("Successfully Edited"), Alert::SEVERITY_SUCCESS);
    throw new SeeOther($this->image->route);
  }

}
