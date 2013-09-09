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

/**
 * Represents a HTML alert message which can be used inline or on the complete view.
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
  const SEVERITY_ERROR = "error";

  /**
   * Severity level <i>info</i> for alert message (color <i>blue</i>).
   *
   * @var string
   */
  const SEVERITY_INFO = "info";

  /**
   * Severity level <i>success</i> for alert message (color <i>green</i>).
   *
   * @var string
   */
  const SEVERITY_SUCCESS = "success";

  /**
   * Severity level <i>warning</i> (default) for alert message (color <i>yellow</i>).
   *
   * @var string
   */
  const SEVERITY_WARNING = "warning";


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
   * @var string
   */
  public $severity = self::SEVERITY_WARNING;

  /**
   * The alert's title.
   *
   * @var string
   */
  public $title = "";


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new alert.
   *
   * @param string $message
   *   The message that should be displayed to the user.
   * @param null|array $options [optional]
   *   Associative array containing options to customize the look of the alert message. The following offsets are
   *   available:
   *   <ul>
   *     <li><code>"block"</code> determines if the alert message uses a <code><h4></code>- or <code><b></code>-tag
   *     to wrap the <var>$options["title"]</var>. Default is set to <code>FALSE</code> (use <code><b></code>-tag), set
   *     it to <code>TRUE</code> to use the heading instead.</li>
   *     <li><code>"title"</code> will be displayed before the message, either wrapped in a <code><h4></code>- or
   *     <code><b></code>-tag, depending on <var>$options["block"]</var></li>
   *     <li><code>"severity"</code> use the provided <var>SEVERITY_*</var>-class-constants to change the severity
   *     level of this alert. Determines the CSS colors that are applied.</li>
   *   </ul>
   */
  public function __construct($message, array $options = null) {
    $this->message = $message;
    if ($options) {
      foreach ($options as $k => $v) {
        $this->{$k} = $v;
      }
    }
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
    if (!empty($this->title)) {
      $tag = $this->block === true ? "h4" : "b";
      $this->title = "<{$tag} class='alert__title'>{$this->title}</{$tag}>";
    }
    return "<div class='alert alert--{$this->severity}' role='alert'><div class='container'>{$this->title}{$this->message}</div></div>";
  }

}
