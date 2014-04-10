<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Â© 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Partial;

/**
 * Represents an HTML alert message which can be used inline or attached to a pages heading.
 *
 * @see \MovLib\Presentation\AbstractPresenter::getAlert()
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Alert {


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
   * The alert's message.
   *
   * @var string
   */
  protected $message;

  /**
   * The alert's severity level.
   *
   * @var string
   */
  protected $severity;

  /**
   * The alert's title.
   *
   * @var string
   */
  protected $title;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new alert.
   *
   * @param string $message
   *   The alert's translated message.
   * @param string $title [optional]
   *   The alert's translated title, defaults to no title.
   * @param string $severity [optional]
   *   The alert's severity level, default to no severity which is the CSS default. Use the class constants.
   */
  public function __construct($message, $title = null, $severity = null) {
    $this->message  = $message;
    $this->title    = $title;
    $this->severity = $severity;
  }

  /**
   * Get HTML representation of this alert message.
   *
   * @return string
   *   HTML representation of this alert message.
   */
  public function __toString() {
    $title    = $this->title    ? "<h2>{$this->title}</h2>" : null;
    $severity = $this->severity ? " alert-{$severity}"      : null;
    switch ($severity) {
      case self::SEVERITY_INFO:
      case self::SEVERITY_SUCCESS:
        $live = "polite";
        $role = "status";
        break;

      case self::SEVERITY_WARNING:
      case self::SEVERITY_ERROR:
        $live = "assertive";
        $role = "alert";
        break;

      default:
        $live = "polite";
        $role = "log";
    }
    return "<div aria-live='{$live}' class='alert{$severity}' role='{$role}'><div class='c'>{$title}{$this->message}</div></div>";
  }

}
