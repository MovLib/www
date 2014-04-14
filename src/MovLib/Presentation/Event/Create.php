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
namespace MovLib\Presentation\Event;

use \MovLib\Data\Event\Event;

/**
 * Allows the creation of a new event event.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Create extends \MovLib\Presentation\AbstractPresenter {

  /**
   * Instantiate new event create presentation.
   */
  public function init() {
    $this->initPage($this->intl->t("Create Event"));
    $this->initBreadcrumb([ [ $this->intl->rp("/events"), $this->intl->t("Events") ] ]);
    $this->breadcrumbTitle = $this->intl->t("Create");
    $this->initLanguageLinks("/event/create");
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "<div class='c'>{$this->checkBackLater($this->intl->t("create event"))}</div>";
  }

}
