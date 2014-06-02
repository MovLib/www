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

use \MovLib\Data\History\HistorySet;

/**
 * Shows the history of an article.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistory extends \MovLib\Core\Presentation\AbstractHistory {
  use \MovLib\Presentation\Help\HelpTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractHistory";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Help\Article $article
   *   The help article to present.
   */
  public function initArticle(\MovLib\Data\Help\Article $article) {
    $this->entity = $article;
    $pageTitle    = $this->intl->t("History of {0}", [ $this->entity->title ]);
    $this->initPage($pageTitle, $pageTitle, $this->intl->t("History"));
    $this->sidebarInitToolbox($this->entity);
    $this->initLanguageLinks("{$this->entity->route->route}/history", $this->entity->id);
    $this->breadcrumb->addCrumbs($this->getArticleBreadCrumbs());
    $this->historySet = new HistorySet("Article", $this->entity->id, "\\MovLib\\Data\\Help");
    $this->paginationInit($this->historySet->getTotalCount());
    $this->historySet->load($this->container, $this->paginationOffset, $this->paginationLimit);

    return $this;
  }

}
