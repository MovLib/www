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
 * 
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Discussion extends \MovLib\Presentation\Movie\AbstractBase {

  public function __construct() {
    global $i18n;
    $this->movie = new Movie($_SERVER["MOVIE_ID"]);
    $this->initPage($i18n->t("Discuss {title}", [ "title" => $this->movie->displayTitleWithYear ]));
    $this->initLanguageLinks("/movie/{0}/discussion", [ $this->movie->id ]);
    $this->initBreadcrumb();
    $this->breadcrumbTitle = $i18n->t("Discuss");
    $this->alerts .= new Alert(
      $i18n->t("The discuss movie feature isn’t implemented yet."),
      $i18n->t("Check back later"),
      Alert::SEVERITY_INFO
    );
  }

  protected function getPageContent() {

  }

}