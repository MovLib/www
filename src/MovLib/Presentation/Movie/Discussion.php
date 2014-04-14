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
use \MovLib\Presentation\Partial\Alert;

/**
 * A movie's discussion
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Discussion extends \MovLib\Presentation\Movie\AbstractBase {

  /**
   * Initialize person discussion presentation.
   */
  public function init() {
    $this->movie = new Movie($this->diContainerHTTP, (integer) $_SERVER["MOVIE_ID"]);
//    $this->initPage($this->intl->t("Discuss"));
//    $this->initPage($this->intl->t("Discuss {title}", [ "title" => $this->movie->displayTitleWithYear ]));
//    $this->initLanguageLinks("/movie/{0}/discussion", [ $this->movie->id ]);
//    $this->breadcrumb
//      ->addCrumb($this->movie->routeIndex, $this->intl->t("Movies"))
//      ->addCrumb($this->movie->route, $this->movie->displayTitleWithYear)
//    ;
    $this->initPage($this->intl->t("Discuss {title}", [ "title" => $this->movie->displayTitle ]));
    $this->contentBefore = "<div class='c'>";
    $this->contentAfter  = "</div>";
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->callout($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("discuss movie") ]), $this->intl->t("Check back later"), "info");
  }

}
