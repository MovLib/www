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
namespace MovLib\View\HTML;

use \MovLib\View\HTML\AbstractView;

/**
 * The abstract form view contains utility methods for views with forms.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractFormView extends AbstractView {

  /**
   * The attributes that will be applied to the <code>&lt;form&gt;</code>-element.
   *
   * <b>IMPORTANT!</b> Default CSS classes for the element type will be included automatically!
   *
   * @var array
   */
  protected $attributes = [];

  /**
   * Get the rendered content, without HTML head, header or footer.
   *
   * @global \MovLib\Model\UserModel $user
   *   The global user model instance.
   * @return string
   */
  public function getRenderedContent() {
    global $user;
    $csrf = "";
    if ($token = $user->getCsrfToken()) {
      $csrf = "<input name='csrf_token' type='hidden' value='{$token}'>";
    }
    if (isset($this->attributes["action"])) {
      $this->attributes["action"] = $_SERVER["REQUEST_URI"];
    }
    if (isset($this->attributes["method"])) {
      $this->attributes["method"] = "post";
    }
    $this->addClass("form form-{$this->getShortName()}", $this->attributes);
    return "<form {$this->expandTagAttributes($this->attributes)}>{$csrf}{$this->getRenderedFormContent()}</form>";
  }

  /**
   * The HTML content of the <code>&lt;form&gt;</code>-element.
   *
   * <b>IMPORTANT!</b> Do not include opening and closing <code>form</code>-tags!
   *
   * @return string
   *   The HTML content of the <code>&lt;form&gt;</code>-element.
   */
  abstract public function getRenderedFormContent();

}
