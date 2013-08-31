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
namespace MovLib\View\HTML\FormElement;

use \MovLib\View\HTML\BaseView;

/**
 * Most basic properties and methods that are shared by all form elements.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormElement extends BaseView {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Global identifier to access this element.
   *
   * @var string
   */
  public $id;

  /**
   * Array containing all attributes of this form element.
   *
   * @var array
   */
  public $attributes = [];


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the HTML for this input element.
   *
   * Every input element has to implement the magic <code>__toString()</code> and output the correct HTML for itself and
   * should output the label, the help if any by calling <code>$this->help()</code>, and the element itself of course.
   *
   * @global \MovLib\Model\I18nModel $i18n
   * @return string
   *   The HTML input element, including label and help (if any).
   */
  abstract public function __toString();

}
