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

use \MovLib\Data\Help\HelpCategory;

/**
 * Base presenation of all help pages.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractBase extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar {
    sidebarInit as traitSidebarInit;
  }


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The help article to present.
   *
   * @var \MovLib\Data\Help\HelpArticle
   */
  protected $helpArticle;

  /**
   * The help article's category.
   *
   * @var \MovLib\Data\Help\HelpCantegory
   */
  protected $helpCategory;

  /**
   * The help article' sub category.
   *
   * @var \MovLib\Data\Help\HelpSubCategory
   */
  protected $helpSubCategory;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Init help breadcrumb.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function initHelpBreadcrumb() {
    global $i18n;

    $breadCrumbItems = [ ];
    if (isset($this->helpCategory)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (!($this->helpCategory instanceof \MovLib\Data\Help\HelpCategory)) {
        throw new \LogicException($i18n->t("\$this->helpCategory has to be a valid help category object!"));
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $breadCrumbItems[] = [ $i18n->r("/help"), $i18n->t("Help") ];
      $breadCrumbItems[] = [ $this->helpCategory->route, $this->helpCategory->title ];
    }
    if (isset($this->helpSubCategory)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (!($this->helpSubCategory instanceof \MovLib\Data\Help\HelpSubCategory)) {
        throw new \LogicException($i18n->t("\$this->helpSubCategory has to be a valid help sub category object!"));
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $breadCrumbItems[] = [ $this->helpSubCategory->route, $this->helpSubCategory->title ];
    }
    if (isset($this->helpArticle)) {
      // @devStart
      // @codeCoverageIgnoreStart
      if (!($this->helpArticle instanceof \MovLib\Data\Help\HelpArticle)) {
        throw new \LogicException($i18n->t("\$this->helpArticle has to be a valid help article object!"));
      }
      // @codeCoverageIgnoreEnd
      // @devEnd
      $breadCrumbItems[] = [ $this->helpArticle->route, $this->helpArticle->title ];
    }

    return $this->initBreadcrumb($breadCrumbItems);
  }

  /**
   * Init help article sidebar.
   *
   * @global \MovLib\Data\I18n $i18n
   * @return $this
   * @throws \LogicException
   */
  protected function sidebarInit() {
    global $i18n;

    /* @var $result \mysqli_result */
    $result = HelpCategory::getHelpCategoryIds();
    $sidebarItems = [];
    while ($row = $result->fetch_object()) {
      $category = new HelpCategory($row->id);
      $sidebarItems[] = [ $category->route, $category->title ];
    }

    return $this->traitSidebarInit($sidebarItems);
  }

}
