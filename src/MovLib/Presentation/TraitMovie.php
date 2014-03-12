<?php

/* !
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
 * Add various movie formatting functions to presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitMovie {


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Construct movie title information for display.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to display the title information for. Can also be <code>\\MovLib\\Data\\Movie\\FullMovie</code>.
   * @param array $attributes [optional]
   *   Additional attributes to apply to the wrapper.
   * @param string $wrap
   *   The enclosing tag.
   * @return string
   *   The formatted title information.
   * @throws \LogicException
   */
  public function getTitleInfo($movie, $attributes = null, $wrap = "p") {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($movie) || !isset($movie->displayTitle)) {
      throw new \LogicException("You have to pass a valid movie object to get title information!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We have to use different micro-data if display and original title differ.
    if ($movie->displayTitle != $movie->originalTitle) {
      $displayTitleItemprop = "alternateName";
      $originalTitle = "<br><span class='small'>{$i18n->t("{0} ({1})", [
        "<span property='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
        "<i>{$i18n->t("original title")}</i>",
      ])}</span>";
    }
    // Simply clear the original title if it's the same as the display title.
    else {
      $displayTitleItemprop = "name";
      $originalTitle = null;
    }
    $displayTitle = "<a href='{$movie->route}' property='url'><span property='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span></a>{0}";

    // Append year enclosed in micro-data to display title if available.
    if (isset($movie->year)) {
      $displayTitle = str_replace("{0}", " (<span property='datePublished'>{$movie->year}</span>)", $displayTitle);
    }
    else {
      $displayTitle = str_replace("{0}", "", $displayTitle);
    }

    return "<{$wrap}{$this->expandTagAttributes($attributes)}>{$displayTitle}{$originalTitle}</{$wrap}>";
  }

}
