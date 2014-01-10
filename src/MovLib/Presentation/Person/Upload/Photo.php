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
namespace MovLib\Presentation\Person\Upload;

use \MovLib\Data\Image\PersonImage;
use \MovLib\Data\License;
use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Error\NotFound;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputURL;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;

/**
 * @todo Description of UploadPhoto
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Photo extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form's description input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $description;

  /**
   * The photo instance.
   *
   * @var \MovLib\Data\Image\PersonImage
   */
  protected $image;

  /**
   * The form's file input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The person this photo belongs to.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;

  /**
   * The form's license selection.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $license;

  /**
   * The form's URL input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputURL
   */
  protected $source;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person upload photo presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function __construct() {
    global $i18n;

    $this->person = new Person($_SERVER["PERSON_ID"]);
    if ($this->person->deleted === true) {
      // @todo Display appropriate information if person is deleted.
    }
    elseif (isset($_SERVER["IMAGE_ID"])) {
      $title       = $i18n->t("Update photo of {0}", [ $this->person->name ]);
      $submit      = $i18n->t("Update Photo");
      $this->image = new PersonImage($this->person->id, $this->person->name, $_SERVER["IMAGE_ID"]);
    }
    else {
      $title       = $i18n->t("Upload new photo for {0}", [ $this->person->name ]);
      $submit      = $i18n->t("Upload Photo");
      $this->image = new PersonImage($this->person->id, $this->person->name);
    }
    $this->init($title);

    $this->inputImage  = new InputImage("photo", $i18n->t("Photo"), $this->image, [ "required" ]);
    $this->description = new InputHTML("description", $i18n->t("Description"), $this->image->description);
    $this->source      = new InputURL("source", $i18n->t("Source"), [ "data-allow-external" => true, "value" => $this->image->source ]);
    $this->license     = new Select("license", $i18n->t("License"), License::getLicenses(), $this->image->licenseId ? : 1, [ "required" ]);

    $this->form = new Form($this, [
      $this->inputImage,
      $this->description,
      $this->source,
      $this->license,
    ]);
    $this->form->actionElements[] = new InputSubmit($submit, [
      "class" => "btn btn-large btn-success",
      "title" => $i18n->t("Continue here after you filled out all mandatory fields."),
    ]);
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
  protected function valid() {
    $this->image->description = $this->description->value;
    $this->image->licenseId   = $this->license->value;
    $this->image->source      = $this->source->value;
    $this->image->upload($this->inputImage->path, $this->inputImage->extension, $this->inputImage->height, $this->inputImage->width);
    throw new SeeOtherRedirect($this->image->route);
  }

}
