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
namespace MovLib\Presentation\Forum;

use \MovLib\Data\Forum\Forum;

/**
 * Defines the base class for all forum index presentations.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractForum extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractForum";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The forum to present.
   *
   * @var \MovLib\Data\Forum\Forum
   */
  protected $forum;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Get the forum's unique identifier.
   *
   * @return integer
   *   The forum's unique identifier.
   */
  abstract protected function getForumId();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->forum = new Forum($this->container, $this->getForumId());
    $this->initPage($this->forum->title);
    $this->initLanguageLinks($this->forum->route->route);
    $this->breadcrumb->addCrumb("/forums", $this->intl->t("Forums"));
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingAfter = "<p>{$this->forum->description}</p>";
    return "<div class='c'>" . \Krumo::dump($this->forum, KRUMO_RETURN) . "</div>";
  }

}
