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

use \MovLib\Data\Series\Series;

/**
 * Awards of a series.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Awards extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Awards";
  // @codingStandardsIgnoreEnd
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Presentation\Series\SeriesTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Series($this->container, $_SERVER["SERIES_ID"]);
    $pageTitle    = $this->intl->t("Awards of {0}", [ $this->entity->displayTitle ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Awards"))
      ->sidebarInitToolbox($this->entity, $this->getSidebarItems())
      ->initLanguageLinks("/{$this->entity->set->singularKey}/{0}/awards", $this->entity->id)
      ->breadcrumb->addCrumbs([
        [ $this->intl->r("/series"), $this->intl->tp(-1, "Series") ],
        [ $this->entity->route, $this->entity->displayTitle ]
      ])
    ;

  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater("series awards");
  }

}
