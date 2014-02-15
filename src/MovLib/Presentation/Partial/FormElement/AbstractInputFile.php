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
namespace MovLib\Presentation\Partial\FormElement;

/**
 * Overrides the default validate method to validate the <var>$_FILES</var> array instead of the <var>$_POST</var>
 * array and allows other classes to check against this abstract class instead of concrete classes if they want to
 * check if the concrete class they're dealing with is a input file form element.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractInputFile extends \MovLib\Presentation\Partial\FormElement\AbstractFormElement {

  /**
   * Validate the form element's submitted file.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return this
   * @throws \RuntimeException
   */
  public function validate() {
    global $i18n, $session;

    // Only authenticated user's are allowed to upload files.
    if ($session->isAuthenticated === false) {
      throw new Unauthorized;
    }

    // Fail if the key is missing within the files array or if no file was uploaded at all.
    if (empty($_FILES[$this->id]) || $_FILES[$this->id]["error"] === UPLOAD_ERR_NO_FILE) {
      $this->value = null;
      if ($this->required) {
        throw new \RuntimeException($i18n->t("The “{0}” file is required.", [ $this->label ]));
      }
    }
    // Continue file upload if PHP reported no error and the file was really uploaded via HTTP POST.
    elseif ($_FILES[$this->id]["error"] === UPLOAD_ERR_OK && is_uploaded_file($_FILES[$this->id]["tmp_name"]) === true) {
      $errors = null;
      $this->value = $this->validateValue($_FILES[$this->id], $errors);
      if ($errors) {
        if ($errors === (array) $errors) {
          $errors = implode("<br>", $errors);
        }
        throw new \RuntimeException($errors);
      }
    }
    // Anything else should be treated as error.
    else {
      switch ($_FILES[$this->id]["error"]) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          throw new \RuntimeException($i18n->t(
            "The uploaded file is too large, it must be: {maxsize} or less",
            [ "maxsize" => $i18n->formatBytes(ini_get("upload_max_filesize")) ]
          ));

        case UPLOAD_ERR_PARTIAL:
          throw new \RuntimeException($i18n->t("The upload didn’t complete, please try again."));

        default:
          throw new \RuntimeException($i18n->t("An unknown problem was encountered while processing your upload, please try again."));
      }
    }

    return $this;
  }

}
