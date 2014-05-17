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
namespace MovLib\Presentation\Help;

use \MovLib\Data\Help\ArticleSet;
use \MovLib\Data\Help\SubCategorySet;

/**
 * Defines the abstract help subcategory index presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractSubCategory extends \MovLib\Presentation\AbstractIndexPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The articles to present.
   *
   * @var \MovLib\Data\Help\ArticleSet
   */
  protected $articleSet;

  /**
   * The subcategory to present.
   *
   * @var \MovLib\Data\Help\SubCategory
   */
  protected $subCategory;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Help\SubCategory $subCategory
   */
  public function initSubCategory(\MovLib\Data\Help\SubCategory $subCategory) {
    $this->set         = new ArticleSet($this->diContainerHTTP);
    $this->subCategory = $subCategory;

    $this->initPage($this->subCategory->title);
    $this->initBreadcrumb([
      [ $this->intl->r("/help"), $this->intl->t("Help") ],
      [ $this->intl->r($this->subCategory->category->routeKey), $this->subCategory->category->title ]
    ]);
    $this->initLanguageLinks($this->subCategory->routeKey);

    $sidebarItems = [ [ $this->subCategory->category->route, "{$this->subCategory->category->title} <span class='fr'>{$this->intl->format("{0,number}", [ $this->subCategory->category->articleCount ])}</span>", [ "class" => "ico {$this->subCategory->category->icon} separator" ] ] ];
    foreach ((new SubCategorySet($this->diContainerHTTP))->getAllBelongingToCategory($this->subCategory->category->id) as $id => $entity) {
      $sidebarItems[] = [ $entity->route, "{$entity->title} <span class='fr'>{$this->intl->format("{0,number}", [ $entity->articleCount ])}</span>", [ "class" => "ico {$entity->icon}" ] ];
    }
    $this->sidebarInit($sidebarItems);

    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("{$this->subCategory->routeKey}/create")}'>{$this->intl->t("Create Help Article")}</a>";

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $items = null;
    foreach ($this->set->getAllBelongingToSubCategory($this->subCategory->id) as $id => $entity) {
      $items .= $this->formatListingItem($entity, $id);
    }
    return isset($items)? $this->getListing($items) : $this->getNoItemsContent();
  }

  /**
   * {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $entity, $delta) {
    return
      "<li class='hover-item r'>" .
        "<article>" .
          "<div class='s s10'>" .
            "<h2 class='para'><a href='{$entity->route}' property='url'><span property='name'>{$entity->title}</span></a></h2>" .
          "</div>" .
        "</article>" .
      "</li>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->calloutInfo(
      $this->intl->t("We couldn’t find any articles in this category."),
      $this->intl->t("No Help In This Category")
    );
  }

}
