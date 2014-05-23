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
 * Defines the alert partial object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Alert {


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
   * Instantiate new alert partial.
   *
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @param string $severity [optional]
   *   The alert's severity level, one of <code>NULL</code> (default), <code>"error"</code>, <code>"info"</code>,
   *   <code>"success"</code>, or <code>"warning"</code>.
   */
  public function __construct($title, $message, $severity = null) {
    $this->title    = $title;
    $this->message  = $message;
    $this->severity = $severity;
  }

  /**
   * Get HTML representation of this alert message.
   *
   * @return string
   *   HTML representation of this alert message.
   */
  public function __toString() {
    // Guardian pattern.
    if (empty($this->title) && empty($this->message)) {
      return "";
    }

    $title    = $this->title    ? "<h2>{$this->title}</h2>"  : null;
    $severity = $this->severity ? " alert-{$this->severity}" : null;

    switch ($this->severity) {
      case "info":
      case "success":
        $live = "polite";
        $role = "status";
        break;

      case "warning":
      case "error":
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
