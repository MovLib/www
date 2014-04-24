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

use \MovLib\Data\Award\Award;
use \MovLib\Data\Award\CategorySet;
use \MovLib\Partial\Date;

/**
 * Defines the award category index presentation.
 *
 * @property \MovLib\Data\Award\CategorySet $set
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {
  use \MovLib\Presentation\Award\AwardTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity        = new Award($this->diContainerHTTP, $_SERVER["AWARD_ID"]);
    $this->set           = new CategorySet($this->diContainerHTTP);
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/award/{0}/category/create", [ $this->entity->id ])}'>{$this->intl->t("Create Category")}</a>";
    $pageTitle    = $this->intl->t("Categories of {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Categories"))
      ->sidebarInitToolbox($this->entity, $this->getSidebarItems())
      ->initLanguageLinks("/{$this->entity->singularKey}/{0}/categories", $this->entity->id, true)
      ->breadcrumb->addCrumbs([
        [ $this->intl->r("/awards"), $this->intl->t("Awards") ],
        [ $this->entity->route, $this->entity->name ]
      ])
    ;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Award\Category $category {@inheritdoc}
   */
  public function formatListingItem(\MovLib\Data\AbstractEntity $category, $delta) {
    $categoryDates = (new Date($this->intl, $this))->formatFromTo(
      $category->firstYear,
      $category->lastYear,
      [ "title" => $this->intl->t("From") ],
      [ "title" => $this->intl->t("To") ],
      true
    );
    if ($categoryDates) {
      $categoryDates = "<small>{$categoryDates}</small>";
    }
    $route = $category->route;
    return
      "<li class='hover-item r'>" .
        "<article typeof='Organization'>" .
          "<a class='no-link s s1' href='{$route}'>" .
            "<img alt='{$category->name}' src='{$this->fs->getExternalURL("asset://img/logo/vector.svg")}' width='60' height='60'>" .
          "</a>" .
          "<div class='s s9'>" .
            "<div class='fr'>" .
              "<a class='ico ico-movie label' href='{$this->intl->r("/award/{0}/category/{1}/movies", [ $category->award->id, $category->id ])}' title='{$this->intl->t("Movies")}'>{$category->movieCount}</a>" .
              "<a class='ico ico-series label' href='{$this->intl->r("/award/{0}/category/{1}/series", [ $category->award->id, $category->id ])}' title='{$this->intl->tp("Series")}'>{$category->seriesCount}</a>" .
              "<a class='ico ico-person label' href='{$this->intl->r("/award/{0}/category/{1}/persons", [ $category->award->id, $category->id ])}' title='{$this->intl->t("Persons")}'>{$category->seriesCount}</a>" .
              "<a class='ico ico-company label' href='{$this->intl->r("/award/{0}/category/{1}/companies", [ $category->award->id, $category->id ])}' title='{$this->intl->t("Companies")}'>{$category->seriesCount}</a>" .
            "</div>" .
            "<h2 class='para'><a href='{$route}' property='url'><span property='name'>{$category->name}</span></a></h2>" .
            $categoryDates .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $items = null;
    foreach ($this->set->loadOrdered("`created` DESC", $this->paginationOffset, $this->paginationLimit, "`award_id` = {$this->entity->id}") as $id => $entity) {
      $items .= $this->formatListingItem($entity, $id);
    }
    return $this->getListing($items);
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->callout(
      "<p>{$this->intl->t("We couldn’t find any award categories matching your filter criteria, or there simply aren’t any awards categories available.")}</p>" .
      "<p>{$this->intl->t("Would you like to {0}create an award category{1}?", [ "<a href='{$this->intl->r("/award/{0}/category/create", [ $this->entity->id ])}'>", "</a>" ])}</p>",
      $this->intl->t("No Award Categories"),
      "info"
    );
  }

}
