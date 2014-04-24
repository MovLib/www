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
namespace MovLib\Presentation\Series;

use \MovLib\Data\Series\SeriesSet;

/**
 * The series charts presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Charts extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;

  /**
   * Instantiate new series charts presentation.
   */
  public function init() {
    $this->set = new SeriesSet($this->diContainerHTTP);
    $this->initPage($this->intl->t("Series Charts"), null, $this->intl->t("Charts"));
    $this->initBreadcrumb([ [ $this->intl->r("/series"), $this->intl->tp("Series") ] ]);
    $this->initLanguageLinks("/series/charts");
    $this->sidebarInit([
      [ $this->set->route, $this->title, [ "class" => "ico ico-{$this->set->singularKey}" ] ],
      [ $this->intl->r("/{$this->set->singularKey}/random"), $this->intl->t("Random"), [ "class" => "ico ico-random" ] ],
      [ $this->intl->r("/{$this->set->pluralKey}/charts"), $this->intl->t("Charts"), [ "class" => "ico ico-chart" ] ],
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater($this->title);
  }

}
