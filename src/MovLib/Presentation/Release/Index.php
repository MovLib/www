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
namespace MovLib\Presentation\Release;

use \MovLib\Data\Release\Release;
use \MovLib\Presentation\Partial\Alert;

/**
 * Latest releases.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;

  public function __construct() {
    $this->initPage($this->intl->t("Releases"));
    $this->initLanguageLinks("/releases", null, true);
    $this->initBreadcrumb();
    $this->sidebarInit([
      [ $this->intl->rp("/releases"), $this->intl->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $this->intl->r("/release/random"), $this->intl->t("Random") ],
    ]);
  }

  /**
   *
   */
  protected function getPageContent() {
    $list = null;
    $releasesResult = Release::getReleases();
    $releaseRoute = $this->intl->r("/release/{0}");

    /* @var $release \MovLib\Data\Release\Release */
    while ($release = $releasesResult->fetch_object("\\MovLib\\Data\\Release\\Release")) {
      $route = str_replace("{0}", $release->id, $releaseRoute);
      $list .=
        "<li class='hover-item r'>" .
          "<a class='s s1 tac no-link' href='{$route}'><img class='placeholder' height='60' src='{$this->getURL("asset://logo/vector.svg")}' width='60'></a>" .

        "</li>"
      ;
    }
  }

}
