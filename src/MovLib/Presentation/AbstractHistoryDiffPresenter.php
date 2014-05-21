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
namespace MovLib\Presentation;

use \MovLib\Data\Revision;
use \MovLib\Data\User\UserSet;
use \MovLib\Exception\RedirectException\TemporaryRedirectException;
use \MovLib\Partial\DateTime;

/**
 * Defines base class for history diff presenter.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistoryDiffPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\Revision\AbstractEntity
   */
  protected $entity;

  /**
   * The revision object.
   *
   * @var \MovLib\Data\Revision
   */
  protected $revision;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Present the actual diff.
   */
  abstract function getDiff();


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $userSet = (new UserSet($this->diContainerHTTP))->loadIdentifiers([
      $this->revision->new->userId,
      $this->revision->old->userId,
    ]);
    $dateTime = new DateTime($this->intl, $this);
    return
      "<div class='r'>" .
        "<div class='s s5'>" .
          "<p>{$this->intl->t("Revision: {date}", [ "date" => $dateTime->format($this->revision->new->id) ])}</p>" .
          "<p>{$this->intl->t("User: {username}", [ "username" => $userSet->entities[$this->revision->new->userId]->name ])}</p>" .
        "</div>" .
        "<div class='s s5'>" .
          "<p>{$this->intl->t("Revision: {date}", [ "date" => $dateTime->format($this->revision->old->id) ])}</p>" .
          "<p>{$this->intl->t("User: {username}", [ "username" => $userSet->entities[$this->revision->old->userId]->name ])}</p>" .
        "</div>" .
      "</div>" .
      "<div class='r'>{$this->getDiff()}</div>"
    ;
  }

  /**
   * Initialize the history presenter.
   *
   * @param \MovLib\Data\Revision\AbstractEntity $revisionEntity
   *   The entity to present.
   * @param string $entityClassName
   *   The entity's class name without namespace.
   * @param string $breadcrumbIndexTitle
   *   The entity's translated index title.
   * @return this
   */
  final protected function initHistoryDiff(\MovLib\Data\Revision\AbstractEntity $revisionEntity, $entityClassName, $breadcrumbIndexTitle) {
    $this->entity = $revisionEntity->getEntity();

    // redirect on wrong user input
    $baseRoute = $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs);
    if (isset($_SERVER["REVISION_OLD"]) && isset($_SERVER["REVISION_NEW"])) {
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_OLD"]}");
      }
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }

    $revisionOld = isset($_SERVER["REVISION_OLD"]) ? $_SERVER["REVISION_OLD"] : null;
    $revisionNew = isset($_SERVER["REVISION_NEW"]) ? $_SERVER["REVISION_NEW"] : null;

    $this->revision = new Revision(
      $this->diContainerHTTP,
      $entityClassName,
      $this->entity->id,
      $revisionOld,
      $revisionNew
    );

    if (!isset($this->entity->name)) {
      if (isset($this->entity->displayTitle)) {
        $this->entity->name = $this->entity->displayTitle;
      }
      elseif (isset($this->entity->title)) {
        $this->entity->name = $this->entity->title;
      }
    }

    $pageTitle = $this->intl->t("History Diff of {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("History"))
      ->sidebarInitToolbox($this->entity, $this->getSidebarItems())
      ->initLanguageLinks("{$this->entity->routeKey}/history", $this->entity->routeArgs)
      ->breadcrumb->addCrumbs([
        [ $this->entity->routeIndex, $breadcrumbIndexTitle ],
        [ $this->entity->route, $this->entity->name ]
      ])
    ;
  }

}
