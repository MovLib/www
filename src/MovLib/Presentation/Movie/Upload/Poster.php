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
use \MovLib\Data\License;
use \MovLib\Data\Movie\Movie;
use \MovLib\Exception\Client\ErrorNotFoundException;
use \MovLib\Exception\Client\RedirectSeeOtherException;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
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
class Poster extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
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
        // @todo Display appropriate information if movie is deleted.
      }
      elseif (isset($_SERVER["IMAGE_ID"])) {
        $this->image = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear, $_SERVER["IMAGE_ID"]);
      }
      else {
        $title = "<a href='{$i18n->r("/movie/{0}", [ $_SERVER["MOVIE_ID"] ])}'>{$this->movie->displayTitle}</a>";
        if ($this->movie->year) {
          $title = $i18n->t("{movie_title} ({movie_year})", [ "movie_title" => $title, "movie_year" => $this->movie->year ]);
        }
        $title = $i18n->t("Upload new poster for {movie_title}", [ "movie_title" => $title ]);
        $this->image = new MoviePoster($this->movie->id, $this->movie->displayTitleWithYear);
      }
      $this->init($title);
      $this->initSidebar([]);
      $this->inputImage  = new InputImage("poster", $i18n->t("Poster"), $this->image, [ "required" ]);
      $this->description = new InputHTML("description", $i18n->t("Description"), $this->image->description, [ "required" ]);
      $this->country     = new Select("country", $i18n->t("Country"), Country::getCountries(), $this->image->countryCode);
      $this->license     = new Select("license", $i18n->t("License"), License::getLicenses(), $this->image->licenseId ? : 1, [ "required" ]);

      $this->form = new Form($this, [
        $this->inputImage,
        $this->description,
        $this->country,
        $this->license,
      ]);
      $this->form->actionElements[] = new InputSubmit($i18n->t("Upload Poster"), [
        "class" => "btn btn-large btn-success",
        "title" => $i18n->t("Continue here after you filled out all mandatory fields."),
      ]);
    }
    catch (\OutOfBoundsException $e) {
      throw new ErrorNotFoundException("No movie with ID '{$_SERVER["MOVIE_ID"]}'.");
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
    global $i18n;
    if ($this->checkErrors($errors) === false) {
      $this->image->countryCode = $this->country->value;
      $this->image->description = $this->description->value;
      $this->image->licenseId   = $this->license->value;
      $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
      throw new RedirectSeeOtherException($this->image->route);
    }
    return $this;
  }

}
