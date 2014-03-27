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

use \MovLib\Data\Genre;

/**
 * A genre's discussion.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class History extends \MovLib\Presentation\Genre\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new genre history presentation.
   *
   */
  public function __construct() {
    $this->genre = new Genre((integer) $_SERVER["GENRE_ID"]);
    $this->initPage($this->intl->t("History"));
    $this->pageTitle = $this->intl->t("History of {0}", [ "<a href='{$this->genre->route}'>{$this->genre->name}</a>" ]);
    $this->initLanguageLinks("/genre/{0}/history", [ $this->genre->id ]);
    $this->initGenreBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "genre";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getPageContent() {
    return new \MovLib\Presentation\Partial\Alert($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("genre history") ]), $this->intl->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }

}
