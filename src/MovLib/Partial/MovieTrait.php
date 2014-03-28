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
 * Add various movie formatting functions to presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait MovieTrait {

  /**
   * Construct movie title information for display.
   *
   * @param \MovLib\Presentation\AbstractPresenter $presenter
   *   The presenting presenter.
   * @param \MovLib\Core\Intl $intl
   *   The active intl instance.
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to display the title information for.
   * @param array $attributes [optional]
   *   Additional attributes to apply to the wrapper.
   * @param string $wrap
   *   The enclosing tag.
   * @return string
   *   The formatted title information.
   */
  final protected function getFormattedMovieTitle(\MovLib\Presentation\AbstractPresenter $presenter, \MovLib\Core\Intl $intl, \MovLib\Data\Movie\Movie $movie, array $attributes = null, $wrap = "p") {
    if ($movie->displayTitle == $movie->originalTitle) {
      $displayTitleItemprop = "name";
      $originalTitle = null;
    }
    else {
      $displayTitleItemprop = "alternateName";
      $originalTitle = "<br><span class='small'>{$this->intl->t("{0} ({1})", [
        "<span property='name'{$presenter->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
        "<i>{$intl->t("original title")}</i>",
      ])}</span>";
    }

    $displayTitle = "<a href='{$movie->route}' property='url'><span property='{$displayTitleItemprop}'{$presenter->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span></a>";
    if (isset($movie->year)) {
      $displayTitle .= " (<span>{$movie->year}</span>)";
    }

    return "<{$wrap}{$presenter->expandTagAttributes($attributes)}>{$displayTitle}{$originalTitle}</{$wrap}>";
  }

}
