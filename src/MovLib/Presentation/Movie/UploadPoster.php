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
use \MovLib\Data\Image\MoviePoster;
use \MovLib\Data\License;
use \MovLib\Data\Movie\Movie;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Exception\MovieException;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\Select;

/**
 * Form to upload a new poster or update an existing one.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class UploadPoster extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $country;

  /**
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
   * The input image form element.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The movie this image belongs to.
   *
   * @var \MovLib\Data\Movie\Movie
   */
  protected $movie;

  /**
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $license;

  /**
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $source;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new upload poster presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    try {
      $this->movie = new Movie($_SERVER["MOVIE_ID"]);
      if ($this->movie->deleted === true) {

      }
      elseif (isset($_SERVER["IMAGE_ID"])) {
        $this->image = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear, $_SERVER["IMAGE_ID"]);
      }
      else {
        $title = $i18n->t("Upload new poster for {0}", [ $this->movie->displayTitleWithYear ]);
        $this->image = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear);
      }
      $this->init($title);

      $this->inputImage = new InputImage("poster", $i18n->t("Poster"), $this->image, [ "required" ]);

      $this->description = new InputHTML("description", $i18n->t("Description"), $this->image->description);

      $this->source = new InputURL("source", $i18n->t("Source"), [ "data-allow-external" => true, "value" => $this->image->source ]);

      $this->country = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);

      $this->license = new Select("license", $i18n->t("License"), License::getLicenses(), $this->image->licenseId ?: 1, [ "required" ]);

      $this->form = new Form($this, [
        $this->inputImage,
        $this->description,
        $this->source,
        $this->country,
        $this->license,
      ]);
      $this->form->actionElements[] = new InputSubmit($i18n->t("Upload Image"), [
        "class" => "button button--large button--success",
        "title" => $i18n->t("Continue here after you filled out all mandatory fields."),
      ]);
    }
    catch (MovieException $e) {
      throw new ErrorNotFoundException("No movie with ID '{$_SERVER["MOVIE_ID"]}'.");
    }
    catch (ImageException $e) {
      throw new ErrorNotFoundException("No image with ID '{$_SERVER["IMAGE_ID"]}' for movie with ID '{$_SERVER["MOVIE_ID"]}'.");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  protected function getPageContent() {
    return $this->form;
  }

  protected function getSecondaryNavigationMenuitems() {
    return [];
  }

  /**
   * @inheritdoc
   */
  public function validate(array $errors = null) {
    global $i18n;
    // The description can't be empty if the source is empty and vice versa.
    if (empty($this->description->value) && empty($this->source->value)) {
      $errors = $i18n->t("Description and source URL missing, you have to fill out at least one of these fields.");
    }
    if ($this->checkErrors($errors) === false) {
      $this->image->countryCode = $this->country->value;
      $this->image->description = $this->description->value;
      $this->image->licenseId   = $this->license->value;
      $this->image->source      = $this->source->value;
      $this->image->uploadImage($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
      throw new RedirectSeeOtherException($this->image->route);
    }
    return $this;
  }

}
