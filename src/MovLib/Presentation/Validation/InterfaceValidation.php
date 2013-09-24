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
namespace MovLib\Presentation\Validation;

/**
 * Validation interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
interface InterfaceValidation {

  /**
   * Get the validation classes last value that should be validated or has been validated (depends, if validate() was
   * called or not).
   *
   * @return string
   */
  public function __toString();

  /**
   * Set the value that should be validated. This is important for input elements, so they can set their value without
   * knowing the name of the property containing the value that should be validated.
   *
   * @return this
   */
  public function set();

  /**
   * Validate the value.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return mixed
   *   The valid value.
   * @throws \MovLib\Exception\ValidationException
   */
  public function validate();

}
