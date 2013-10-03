<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Partial;

use \MovLib\Presentation\Partial\FormElement\InputImage;

/**
 * Multipart form for file uploads.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class FormMultipart extends \MovLib\Presentation\Partial\Form {

  /**
   * Instantiate new multipart form.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Presentation\Page $page
   *   The page instance this form is attached to.
   * @param array $elements
   *   Form elements that should be attached to this form and auto-validated.
   * @param string $id [optional]
   *   The global identifier of this form. If no value is supplied the identifier of the passed page is used.
   * @param string $validationCallback [optional]
   *   The name of the method that should be called if the form's auto-validation is completed an no errors were
   *   detected. Defaults to <code>"validate"</code>.
   */
  public function __construct($page, array $elements, $id = null, $validationCallback = "validate") {
    global $i18n;
    parent::__construct($page, $elements, $id, $validationCallback);
    $this->attributes["enctype"] = "multipart/form-data";
    if (isset($_SERVER["ENTITY_TOO_LARGE"])) {
      $page->checkErrors([ $i18n->t("The image is too large: it must be {0,number} {1} or less.", $this->formatBytes(ini_get("upload_max_filesize"))) ]);
    }
  }

  /**
   * Validate all attached form elements.
   *
   * @param \MovLib\Presentation\Partial\FormElement\AbstractFormElement $formElement
   *   The form element to validate.
   * @param null|array $errors
   *   The errors array to collect all error messages.
   * @param null|boolean $mandatoryError
   *   The flag indicating if there is one or more mandatory field emtpy.
   * @return this
   */
  protected function validate($formElement, &$errors, &$mandatoryError) {
    if (($formElement instanceof InputImage && (empty($_FILES[$formElement->id]) || $_FILES[$formElement->id]["error"] === UPLOAD_ERR_NO_FILE)) || empty($_POST[$formElement->id])) {
      if (isset($formElement->attributes["aria-required"])) {
        $formElement->invalid();
        $mandatoryError = true;
      }
    }
    else {
      try {
        $formElement->validate();
      }
      catch (\MovLib\Exception\ValidationException $e) {
        $formElement->invalid();
        $errors[] = $e->getMessage();
      }
    }
    return $this;
  }

}
