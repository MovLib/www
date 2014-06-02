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
    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);

   // We can assume that the entity exists at this point.
    $historyRoute = $this->entity->r("/history");

    // We have to make sure that the request actually makes sense, if not redirect. We use a temporary redirect, may
    // be that the route we redirect now has some purpose in the future.
    if (isset($_SERVER["REVISION_NEW"])) {
      // We can't display a diff between two revisions that are actually the same, doesn't make sense.
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_OLD"]}");
      }
      // We only support diff view between old and new.
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }
    else {
      $_SERVER["REVISION_NEW"] = null;
    }

    // Configure the presentation.
    $this->initPage(
      $this->intl->t("{lemma}: Difference between revisions", [ "lemma" => $this->entity->lemma ]),
      null,
      $this->intl->t("Diff")
    );
    $this->sidebarInitToolbox($this->entity);
    $this->breadcrumb->addCrumb($this->entity->award->set->route, $this->entity->award->set->bundleTitle);
    $this->breadcrumb->addCrumb($this->entity->award->route, $this->entity->award->lemma);
    $this->breadcrumb->addCrumb($this->entity->set->route, $this->entity->set->bundleTitle);
    $this->breadcrumb->addCrumb($this->entity->route, $this->entity->lemma);
    $this->breadcrumb->addCrumb($historyRoute, $this->intl->t("History"));

    return $this;
  }

  public function getContent() {
    $history = new \MovLib\Data\History\History((string) $this->entity, $this->entity->id, $_SERVER["REVISION_OLD"], $_SERVER["REVISION_NEW"], "\\MovLib\\Data\\Award");

    // @todo Should we try to recover from a backup?
    return \Krumo::dump($history, KRUMO_RETURN);
  }
}
