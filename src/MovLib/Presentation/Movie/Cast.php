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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\Movie;
use \MovLib\Presentation\Partial\Listing\PersonCastListing;

/**
 * Presentation of single movie's cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast extends \MovLib\Presentation\AbstractIndexPresenter {

  /**
   * Initialize new movie cast presentation.
   *
   */
  public function init() {
    $this->movie = new Movie($_SERVER["MOVIE_ID"]);
    $this->initPage($this->intl->t("Cast"));
    $this->pageTitle = $this->intl->t(
      "Cast of {0}",
      [ "<a href='{$this->movie->route}' property='url'><span property='name'>{$this->movie->displayTitleWithYear}</span></a>" ]
    );
    $this->initLanguageLinks("/movie/{0}/cast", [ $this->movie->id ]);
    // @todo: Replace with the real set!
    $this->initIndex(new \MovLib\Data\Person\PersonSet($this->diContainerHTTP), "Fix me!", "Fix me!");
  }

  /**
   * {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $item, $delta) {

  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find the cast for this movie.")}</p>",
      $this->intl->t("No Cast"),
      "info"
    );
  }

}
