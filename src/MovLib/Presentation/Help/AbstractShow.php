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

/**
 * Abstract Presentation of a single help article.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractShow extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Presentation\Help\HelpTrait;

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Help\Article $article
   *   The help article to present.
   */
  public function initArticle(\MovLib\Data\Help\Article $article) {
    $this->entity = $article;
    $this->initPage($this->entity->title);

    $breadcrumbItems = $this->getArticleBreadCrumbs();
    array_pop($breadcrumbItems);
    $this->initBreadcrumb($breadcrumbItems);

    $this->sidebarInitToolbox($this->entity);
    $this->initLanguageLinks($this->entity->routeKey, [ $this->entity->id ]);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->htmlDecode($this->entity->text);
  }

}
