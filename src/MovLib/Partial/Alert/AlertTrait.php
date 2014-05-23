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
namespace MovLib\Partial;

/**
 * Defines the alert trait.
 *
 * The alert trait can be used as default implementation for the various alert helper methods required by
 * {@see \MovLib\Presentation\PresentationInterface}.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait AlertTrait {

  /**
   * Used to collect alerts.
   *
   * @var string
   */
  protected $alerts;

  /**
   * Add default alert to presentation.
   *
   * <b>Color: <span style="color:#656565">Gray</span></b> ({@link http://www.colourlovers.com/palette/3311602/MovLib})
   *
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @return this
   */
  final public function alert($title, $message = null) {
    $this->alerts .= new Alert($title, $message);
    return $this;
  }

  /**
   * Add error alert to presentation.
   *
   * <b>Color: <span style="color:#CC0000">Red</span></b>
   *
   * @link @link http://www.colourlovers.com/palette/3311602/MovLib
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @return this
   */
  final public function alertError($title, $message = null) {
    $this->alerts .= new Alert($title, $message, "error");
    return $this;
  }

  /**
   * Add info alert to presentation.
   *
   * <b>Color: <span style="color:#0099CC">Blue</span></b>
   *
   * @link @link http://www.colourlovers.com/palette/3311602/MovLib
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @return this
   */
  final public function alertInfo($title, $message = null) {
    $this->alerts .= new Alert($title, $message, "info");
    return $this;
  }

  /**
   * Add success alert to presentation.
   *
   * <b>Color: <span style="color:#339933">Green</span></b>
   *
   * @link @link http://www.colourlovers.com/palette/3311602/MovLib
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @return this
   */
  final public function alertSuccess($title, $message = null) {
    $this->alerts .= new Alert($title, $message, "success");
    return $this;
  }

  /**
   * Add warning alert to presentation.
   *
   * <b>Color: <span style="color:#FF9900">Orange</span></b>
   *
   * @link @link http://www.colourlovers.com/palette/3311602/MovLib
   * @param string $title
   *   The alert's translated title.
   * @param string $message [optional]
   *   The alert's translated message.
   * @return this
   */
  final public function alertWarning($title, $message = null) {
    $this->alerts .= new Alert($title, $message, "warning");
    return $this;
  }

}
