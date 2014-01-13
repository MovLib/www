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

use \MovLib\Presentation\Partial\Alert;

/**
 * Simple page with a form attached.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitFormPage {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The page's form.
   *
   * @var \MovLib\Presentation\Partial\Form
   */
  protected $form;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Called if the form's auto-validation didn't came up with any errors.
   *
   * @return this
   */
  protected abstract function valid();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Transforms <var>$errors</var> to alerts.
   *
   * It's a very common pattern to collect error messages within an array if validating data. Otherwise one would have
   * to set an alert message for each error that occurs. This method let's you pass the possibly collected errors and
   * checks if there are any, if there are any it will create and set the alert message for you.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param mixed $errors
   *   The collected errors to check.
   * @return boolean
   *   Returns <code>TRUE</code> if there were any errors, otherwise <code>FALSE</code>.
   */
  protected function checkErrors($errors = null) {
    global $i18n;
    if ($errors) {
      if (is_array($errors)) {
        $errors = implode("<br>", $errors);
      }
      $this->alerts .= new Alert($errors, $i18n->t("Validation Error"), Alert::SEVERITY_ERROR);
      return true;
    }
    return false;
  }

  /**
   * The page's validation callback, this must be public to enable a form instance to call this method.
   *
   * The {@see valid()} method is automatically called if no errors were encountered.
   *
   * @param null|array $errors
   *   Array containing all validation exception message from the form, if any.
   * @return this
   */
  public function validate($errors) {
    if ($this->checkErrors($errors) === false) {
      $this->valid();
    }
    return $this;
  }

}
