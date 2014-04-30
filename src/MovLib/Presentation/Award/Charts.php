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
namespace MovLib\Presentation\Award;

use \MovLib\Data\Award\AwardSet;

/**
 * The award charts presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Charts extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;

  /**
   * Initialize the releases charts presentation.
   */
  public function init() {
    $this->set = new AwardSet($this->diContainerHTTP);
    $this->initPage($this->intl->t("Award Charts"), null, $this->intl->t("Charts"));
    $this->initBreadcrumb([ [ $this->intl->r("/awards"), $this->intl->t("Awards") ] ]);
    $this->initLanguageLinks("/award/charts");
    $this->sidebarInit([
      [ $this->set->route, $this->intl->t("Awards"), [ "class" => "ico ico-{$this->set->singularKey}" ] ],
      [ $this->intl->r("/{$this->set->singularKey}/random"), $this->intl->t("Random"), [ "class" => "ico ico-random" ] ],
      [ $this->intl->r("/{$this->set->singularKey}/charts"), $this->intl->t("Charts"), [ "class" => "ico ico-chart" ] ],
      [ $this->intl->r("/help/database/{$this->set->pluralKey}"), $this->intl->t("Help"), [ "class" => "ico ico-help"] ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater($this->title);
  }

}
