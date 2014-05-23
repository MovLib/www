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

/**
 * Defines the presenter interface.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
interface PresentationInterface {

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
  public function alert($title, $message = null);

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
  public function alertError($title, $message = null);

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
  public function alertInfo($title, $message = null);

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
  public function alertSuccess($title, $message = null);

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
  public function alertWarning($title, $message = null);

  /**
   * Get the presentation's fully rendered content.
   *
   * @return string
   *   The presentation's fully rendered content.
   */
  public function getContent();

  /**
   * Get the presentation's fully rendered footer.
   *
   * @return string
   *   The presentation's fully rendered footer.
   */
  public function getFooter();

  /**
   * Get the presentation's fully rendered header.
   *
   * @return string
   *   The presentation's fully rendered header.
   */
  public function getHeader();

  /**
   * Get the presentation's formatted head title.
   *
   * The head title is the title that is displayed in <code><title></code> element of the fully rendered presentation.
   *
   * @return string
   *   The presentation's formatted head title.
   */
  public function getHeadTitle();

  /**
   * Get the presentation's fully rendered page.
   *
   * @param string $content
   *   The presentation's content, usually provided by {@see \MovLib\Presentation\PresentationInterface::getContent()}
   *   but exceptions often inject different content into the current presentation.
   * @return string
   *   The presentation's fully rendered page.
   */
  public function getPresentation($content);

}
