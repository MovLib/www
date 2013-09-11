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

/**
 * Represents an HTML alert message which can be used inline or attached to a pages heading.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Alert {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  /**
   * Severity level <i>error</i> for alert message (color <i>red</i>).
   *
   * @var string
   */
  const SEVERITY_ERROR = " alert--error";

  /**
   * Severity level <i>info</i> for alert message (color <i>blue</i>).
   *
   * @var string
   */
  const SEVERITY_INFO = " alert--info";

  /**
   * Severity level <i>success</i> for alert message (color <i>green</i>).
   *
   * @var string
   */
  const SEVERITY_SUCCESS = " alert--success";

  /**
   * Severity level <i>warning</i> (default) for alert message (color <i>yellow</i>).
   *
   * @var string
   */
  const SEVERITY_WARNING = " alert--warning";


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Flag indicating if the alert message is using block or inline elements.
   *
   * @var boolean
   */
  public $block = false;

  /**
   * The alert's message.
   *
   * @var string
   */
  public $message;

  /**
   * The alert's severity level.
   *
   * Please use the provided class contants to set the severity level. The <var>Alert::SEVERITY_WARNING</var> style is
   * applied if no severity level is set.
   *
   * @var string
   */
  public $severity;

  /**
   * The alert's title.
   *
   * @var string
   */
  public $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new alert.
   *
   * @param string $message
   *   The message that should be displayed to the user.
   */
  public function __construct($message) {
    $this->message = $message;
  }

  /**
   * Get HTML representation of this alert message.
   *
   * @link http://www.w3.org/TR/wai-aria/roles#alert
   * @link http://www.w3.org/TR/wai-aria/states_and_properties#aria-live
   * @return string
   *   HTML representation of this alert message.
   */
  public function __toString() {
    if ($this->title) {
      $tag = $this->block === true ? "h4" : "b";
      $this->title = "<{$tag} class='alert__title'>{$this->title}</{$tag}>";
    }
    return "<div class='alert{$this->severity}' role='alert'><div class='container'>{$this->title}{$this->message}</div></div>";
  }

}