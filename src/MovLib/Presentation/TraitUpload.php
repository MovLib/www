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
namespace MovLib\Presentation;

use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\Select;
use \MovLib\Presentation\Partial\License;

/**
 * Implements the form elements that are necessary for any upload presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitUpload {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The input HTML form element for the image description.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputDescription;

  /**
   * The input image form element for the image.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The select form element for the license.
   *
   * @var \MovLib\Presentation\Partial\FormElement\Select
   */
  protected $selectLicense;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the image upload.
   *
   * This will create the form elements that are needed by all image upload presentations.
   *
   * @param array $formElements [optional]
   *   Additional form elements.
   * @return this
   * @throws \LogicException
   */
  protected function initUpload(array $formElements = []) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($this->breadcrumb) || empty($this->breadcrumbTitle)) {
      throw new \LogicException("You have to initialize the breadcrumb and the breadcrumb title property first.");
    }
    if (!isset($this->image)) {
      throw new \LogicException("You have to define an \$image property in your upload class.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->inputDescription       = new InputHTML("description", $i18n->t("Description"), $this->image->description); // @todo required
    $this->inputDescription->allowExternalLinks();
    $this->inputImage             = new InputImage("image", $i18n->t("Image"), $this->image);
    if (!isset($_SERVER["IMAGE_ID"])) {
      $this->inputImage->attributes[] = "required";
    }
    $this->selectLicense          = new Select("license", $i18n->t("License"), License::getLicenses(), $this->image->licenseId ? : 1, [ "required" ]);
    $this->form                   = new Form($this, array_merge([ $this->inputImage, $this->inputDescription, $this->selectLicense ], $formElements));
    $this->form->actionElements[] = new InputSubmit($this->breadcrumbTitle, [ "class" => "btn btn-large btn-success" ]);
    $this->form->multipart();
    return $this;
  }

}
