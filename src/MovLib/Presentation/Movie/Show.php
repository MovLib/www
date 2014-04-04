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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Movie\Movie;
use \MovLib\Partial\QuickInfo;

/**
 * Defines the movie presentation.
 *
 * @link http://schema.org/Movie
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/movie/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/movie/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/movie/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/movie/{id}
 *
 * @property \MovLib\Data\Movie\Movie $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Presentation\Movie\MovieTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initShow(new Movie($this->diContainerHTTP, $_SERVER["MOVIE_ID"]), "Movie", null);
    $this->stylesheets[] = "movie";
    $this->javascripts[] = "Movie";
    $this->pageTitle     = $this->getStructuredDisplayTitle($this->entity, false, true);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    $infos = new QuickInfo($this->intl);


    $this->headingAfter .= "{$infos}</div><div class='s s2'><img alt='' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='140' height='140'></div></div>";
  }

  /**
   * {@inheritdoc}
   */
  protected function getPageTitle() {
    return $this->entity->displayTitleAndYear;
  }

}
