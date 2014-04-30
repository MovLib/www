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
namespace MovLib\Presentation\Help\Database;

/**
 * Base class for database help presenters.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractDatabasePresenter extends \MovLib\Presentation\AbstractPresenter {

  /**
   * Initialize the database help presentation.
   *
   * @param string $routeKey
   *   The article's route key.
   *   @todo Remove when data layer works smoothly.
   * @param string $title
   *   The tranlated page title.
   * @param string $breadcrumbTitle [optional]
   *   The translated breadcrumb title if different from <code>$title</code>
   */
  public function initDatabasePresentation($routeKey, $title, $breadcrumbTitle = null) {
    $breadcrumbTitle || ($breadcrumbTitle = $title);
    $this->initPage($title, $title, $breadcrumbTitle);
    $this->breadcrumb->addCrumb($this->intl->r("/help"), $this->intl->t("Help"));
    $this->breadcrumb->addCrumb($this->intl->r("/help/database"), $this->intl->t("Database"));
    $this->initLanguageLinks($routeKey);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->contentBefore = "<div class='c'>";
    $this->contentAfter  = "</div>";
    return $this->checkBackLater($this->title);
  }

}
