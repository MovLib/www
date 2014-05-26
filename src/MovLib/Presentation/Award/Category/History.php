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
 * A award category's history.
 *
 * @route /award/{id}/category/{id}/history/{ro}/{rn}
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class History extends \MovLib\Presentation\AbstractHistoryPresenter {
  use \MovLib\Presentation\Award\Category\CategoryTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Category($this->container, $_SERVER["CATEGORY_ID"]);
    $pageTitle = $this->intl->t("History of {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("History"))
      ->sidebarInitToolbox($this->entity, $this->getSidebarItems())
      ->initLanguageLinks("{$this->entity->routeKey}/edit", $this->entity->routeArgs)
      ->breadcrumb->addCrumbs([
        [ $this->intl->r("/awards"), $this->intl->t("Awards") ],
        [ $this->intl->r("/award/{0}/", [ $this->entity->award->id ]), $this->entity->award->name ],
        [ $this->intl->r("{$this->entity->pluralKey}"), $this->intl->t("Categories") ],
        [ $this->entity->route, $this->entity->name ]
      ])
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater("Award Category History");
  }

}
