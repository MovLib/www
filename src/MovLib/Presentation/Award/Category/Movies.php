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
 * Movies with a certain award category associated.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Presentation\Award\Category\CategoryTrait;


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
    $this->entity = new Category($this->diContainerHTTP, $_SERVER["CATEGORY_ID"]);
    $pageTitle    = $this->intl->t("Movies related to {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Movies"))
      ->sidebarInitToolbox($this->entity, $this->getSidebarItems())
      ->initLanguageLinks("/{$this->entity->singularKey}/{0}/movies", $this->entity->id)
      ->breadcrumb->addCrumbs($this->getBreadCrumbs())
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->checkBackLater($this->intl->t("award category movies"));
  }

}
