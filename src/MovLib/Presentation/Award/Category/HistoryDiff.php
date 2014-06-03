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
namespace MovLib\Presentation\Award\Category;

use \MovLib\Data\Award\Category;

/**
 * Defines the award category history diff presentation.
 *
 * @route /award/{id}/category/{id}/history/{ro}/{rn}
 * @property \MovLib\Data\Award\Category $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class HistoryDiff extends \MovLib\Core\Presentation\AbstractHistoryDiff {


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "HistoryDiff";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
//    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);
//    return $this->initHistoryDiff([
//      [ $this->entity->award->set->route, $this->entity->award->set->bundleTitle ],
//      [ $this->entity->award->route, $this->entity->award->lemma ],
//    ]);
    // @todo Remove these lines and uncomment the ones above when the revision class is ready.
    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);
    $this->initPage(
      $this->intl->t("{0}: {1}", [ $this->entity->lemma, $this->intl->t("Difference between revisions") ]),
      null,
      $this->intl->t("Diff")
    );
    $this->sidebarInitToolbox($this->entity);
    $this->breadcrumb->addCrumb($this->entity->award->set->route, $this->entity->award->set->bundleTitle);
    $this->breadcrumb->addCrumb($this->entity->award->route, $this->entity->award->lemma);
    $this->breadcrumb->addCrumb($this->entity->set->route, $this->entity->set->bundleTitle);
    $this->breadcrumb->addCrumb($this->entity->route, $this->entity->lemma);
    $this->breadcrumb->addCrumb($this->entity->r("/history"), $this->intl->t("History"));
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater($this->intl->t("Award category differences"));
  }

}
