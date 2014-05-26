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
namespace MovLib\Partial\FormElement;

/**
 * The HTML contenteditable text form element allowing everything except images.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class TextareaHTMLExtended extends \MovLib\Partial\FormElement\TextareaHTML {

  /**
   * Instantiate new HTML form element.
   *
   * @param \MovLib\Core\HTTP\Container $container
   *   HTTP dependency injection container.
   * @param string $id
   *   The text's global identifier.
   * @param string $label
   *   The text's label text.
   * @param mixed $value [optional]
   *   The form element's value, defaults to <code>NULL</code> (no value).
   * @param array $attributes [optional]
   *   Additional attributes for the text, defaults to <code>NULL</code> (no additional attributes).
   */
  public function __construct(\MovLib\Core\HTTP\Container $container, $id, $label, &$value, array $attributes = null) {
    // Allow all tags except for figures.
    $this->allowedTags["blockquote"] = "&lt;blockquote&gt;";
    if (isset($attributes["#level"])) {
      $this->headingLevel = (integer) $attributes["#level"];
      unset($attributes["#level"]);
    }
    for ($i = $this->headingLevel; $i <= 6; ++$i) {
      $this->allowedTags["h{$i}"] = "&lt;h{$i}&gt;";
    }
    $this->allowedTags["ul"] = "&lt;ul&gt;";
    $this->allowedTags["ol"] = "&lt;ol&gt;";
    parent::__construct($container, $id, $label, $value, $attributes);
  }

}
