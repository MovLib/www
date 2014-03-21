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
namespace MovLib\Presentation\Award\Event;

use \MovLib\Data\Award;
use \MovLib\Data\AwardEvent;
use \MovLib\Presentation\Redirect\SeeOther as SeeOtherRedirect;


/**
 * Series with a certain award event associated.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Series extends \MovLib\Presentation\Award\Event\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new award event series presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->award        = new Award((integer) $_SERVER["AWARD_ID"]);
    $this->awardEvent   = new AwardEvent((integer) $_SERVER["AWARD_EVENT_ID"]);

    if ($this->award->id != $this->awardEvent->awardId) {
      throw new SeeOtherRedirect($i18n->rp("/award/{0}/event/{1}/series", [
        $this->awardEvent->awardId,
        $this->awardEvent->id
      ]));
    }

    $this->initPage($i18n->t("Series with {0}", [ $this->awardEvent->name ]));
    $this->pageTitle    =
      $i18n->t("Series with {0}", [ "<a href='{$this->awardEvent->route}'>{$this->awardEvent->name}</a>" ])
    ;
    $this->breadcrumbTitle = $i18n->t("Series");
    $this->initLanguageLinks("/award/{0}/event/{1}/series", [ $this->award->id, $this->awardEvent->id ], true);
    $this->initAwardEventBreadcrumb();
    $this->sidebarInit();

    $kernel->stylesheets[] = "award";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


 /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   * @return \MovLib\Presentation\Partial\Alert
   */
  protected function getPageContent() {
    global $i18n;
    return new \MovLib\Presentation\Partial\Alert($i18n->t("The {0} feature isn’t implemented yet.", [ $i18n->t("series with award event") ]), $i18n->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
  }

}
