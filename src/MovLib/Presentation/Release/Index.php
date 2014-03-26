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
class Index extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;

  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Releases"));
    $this->initLanguageLinks("/releases", null, true);
    $this->initBreadcrumb();
    $this->sidebarInit([
      [ $i18n->rp("/releases"), $i18n->t("Releases"), [ "class" => "ico ico-release" ] ],
      [ $i18n->r("/release/random"), $i18n->t("Random") ],
    ]);
  }

  /**
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  protected function getPageContent() {
    global $i18n, $kernel;

    $list = null;
    $releasesResult = Release::getReleases();
    $releaseRoute = $i18n->r("/release/{0}");

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
