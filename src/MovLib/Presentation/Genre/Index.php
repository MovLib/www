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
namespace MovLib\Presentation\Genre;

use \MovLib\Data\Genre\GenreSet;

/**
 * Defines the genre index presentation.
 *
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/genres
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/genres
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/genres
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/genres
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initIndex(
      new GenreSet($this->diContainerHTTP),
      $this->intl->t("Genres"),
      $this->intl->t("Create New Genre")
    );
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Event\Event $event {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $genre, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<div class='s s10'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->rp("/genre/{0}/movies", $genre->id)}' title='{$this->intl->t("Movies")}'>{$genre->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->rp("/genre/{0}/series", $genre->id)}' title='{$this->intl->t("{0,plural,one{Series}other{Series}}")}'>{$genre->seriesCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$genre->route}' property='url'><span property='name'>{$genre->name}</span></a></h2>" .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any genres matching your filter criteria, or there simply aren’t any genres available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an genre{1}?", [ "<a href='{$this->intl->r("/genre/create")}'>", "</a>" ])}</p>",
      $this->intl->t("No Genres"),
      "info"
    );
  }

}
