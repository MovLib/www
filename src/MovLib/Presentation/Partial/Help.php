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
namespace MovLib\Presentation\Partial;

/**
 * Represents an HTML help element (either a pop-up or simply floating).
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Help {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The help's (HTML) content.
   *
   * @var string
   */
  protected $content;

  /**
   * The help's unique identifier, note that <code>"-help"</code> is <b>always</b> appended to this!
   *
   * @var string
   */
  protected $id;

  /**
   * Whether this help is a popup or not, defaults to <code>TRUE</code>.
   *
   * @var boolean
   */
  protected $popup;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help partial.
   *
   * @param string $content
   *   The help's (HTML) content.
   * @param string $id [optional]
   *   The help's unique identifier, note that <code>"-help"</code> is <b>always</b> appended to this!
   * @param boolean $popup [optional]
   *   Whether this help is a popup or not, defaults to <code>TRUE</code>.
   */
  public function __construct($content, $id = null, $popup = true) {
    $this->content = $content;
    $this->id      = $id;
    $this->popup   = $popup;
  }

  /**
   * Get help's string representation.
   *
   * <b>IMPORTANT!</b> A help is always a HTML block element, do not include it in a paragraph, or similar elements!
   *
   * @todo We should consider to rename it from <i>form-help</i> to simply <i>help</i>.
   * @return string
   *   Help's string representation.
   */
  public function __toString() {
    $id = $this->id ? " id='{$this->id}-help'" : null;
    if ($this->popup === true) {
      return "<div class='form-help ico ico-help popup-c'{$id} role='note'><small class='popup'>{$this->content}</small></div>";
    }
    return "<small class='form-help'{$id} role='note'>{$this->content}</small>";
  }

}
