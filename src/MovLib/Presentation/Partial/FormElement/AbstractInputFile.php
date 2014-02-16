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

use \MovLib\Data\UploadedFile;

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


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Error code for partial upload error message.
   *
   * @var integer
   */
  const ERROR_PARTIAL = 1;

  /**
   * Error code for too large file error message.
   *
   * @var integer
   */
  const ERROR_SIZE = 2;

  /**
   * Error code for unknown error message.
   *
   * @var integer
   */
  const ERROR_UNKNOWN = 3;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the form element's submitted file.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   * @param array $errors
   *   Array to collect error messages.
   * @return this
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function validate(&$errors) {
    global $i18n, $session;

    // Only authenticated user's are allowed to upload files, directly abort if we encounter this error.
    if ($session->isAuthenticated === false) {
      throw new Unauthorized;
    }

    // Instantiate uploaded file object.
    $uploadedFile = new UploadedFile($this->id);

    // Fail if the key is missing within the files array or if no file was uploaded at all.
    if ($uploadedFile->error === UPLOAD_ERR_NO_FILE) {
      // Make sure that the value is really NULL.
      $this->value = null;

      // The missing file is an error if this field is required.
      $this->required && ($errors[self::ERROR_REQUIRED] = $i18n->t("The “{0}” file is required.", [ $this->label ]));
    }
    // Continue file upload if PHP reported no error and the file was really uploaded via HTTP POST.
    elseif ($uploadedFile->error === UPLOAD_ERR_OK && is_uploaded_file($uploadedFile->path) === true) {
      $this->value = $this->validateValue($uploadedFile, $errors);
    }
    // Anything else should be treated as error.
    else {
      switch ($uploadedFile->error) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
          $errors[self::ERROR_SIZE] = $i18n->t(
            "The uploaded file is too large, it must be: {maxsize} or less",
            [ "maxsize" => $i18n->formatBytes(ini_get("upload_max_filesize")) ]
          );
          break;

        case UPLOAD_ERR_PARTIAL:
          $errors[self::ERROR_PARTIAL] = $i18n->t("The upload didn’t complete, please try again.");
          break;

        // Unknown is more than good enough at this point, the user doesn't have to know the specific error!
        default:
          $errors[self::ERROR_UNKNOWN] = $i18n->t("An unknown problem was encountered while processing your upload, please try again.");

          // Be sure to log this error.
          $reasons = [
            UPLOAD_ERR_NO_TMP_DIR => "missing temporary folder",
            UPLOAD_ERR_CANT_WRITE => "failed to write file to disk",
            UPLOAD_ERR_EXTENSION  => "file upload stopped by extension",
          ];

          // I don't know if this situation is even possible (should check the PHP source) but there's nothing wrong
          // with this code because it might be possible that new error codes are defined in the future.
          if (isset($reasons[$_FILES[$this->id]["error"]])) {
            error_log("File upload failed for reason '{$reasons[$_FILES[$this->id]["error"]]}'");
          }
          else {
            error_log("File upload failed for unknown reason, code was: " . $_FILES[$this->id]["error"]);
          }

          break;
      }
    }

    // Mark this form element as invalid if we have any error at this point.
    $errors && $this->invalid();

    return $this;
  }

}
