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
namespace MovLib\Presentation\Genre;

use \MovLib\Data\Genre\Genre;
use \MovLib\Data\Revision\Revision;
use \MovLib\Exception\RedirectException\TemporaryRedirectException;

/**
 * Defines the genre history diff presentation.
 *
 * @route /genre/{id}/history/{ro}/{rn}
 * @property \MovLib\Data\Genre\Genre $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class HistoryDiff extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;

  /**
   * The revision object for loading and patching of entity revisions.
   *
   * @var \MovLib\Data\Revision
   */
  protected $revision;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    // We simply assume that the genre actually exists.
    $historyRoute = $this->intl->r("/genre/{0}/history", $_SERVER["GENRE_ID"]);

    // We have to make sure that the request actually makes sense, if not redirect. We use a temporary redirect, may
    // be that the route we redirect now has some purpose in the future.
    if (isset($_SERVER["REVISION_NEW"])) {
      // We can't display a diff between two revisions that are actually the same, doesn't make sense.
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_OLD"]}");
      }
      // We only support diff view between old and new.
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$historyRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }
    else {
      $_SERVER["REVISION_NEW"] = null;
    }

    // Now we try to instantiate the actual genre for presentation purposes. This will throw the not found exception if
    // the genre doesn't exist at all.
    $this->entity = Genre::createFromId($this->intl, $_SERVER["GENRE_ID"]);

    // No exception, let's start configuring the presentation.
    $this->initPage(
      $this->intl->t("{0}: {1}", [ $this->entity->name, $this->intl->t("Difference between revisions") ]),
      null,
      $this->intl->t("Diff")
    );
    $this->sidebarInitToolbox($this->entity);
    $this->breadcrumb->addCrumb($this->intl->r("/genres"), $this->intl->t("Genres"));
    $this->breadcrumb->addCrumb($this->intl->r("/genre/{0}"), $this->entity->name);
    $this->breadcrumb->addCrumb($historyRoute, $this->intl->t("History"));

    // Now we can restore the old the revisions of the entity. Note that REVISION_OLD is always present, as it is a
    // validated by nginx via a regular expression in the location block and the REVISION_NEW is validated at the top
    // and either contains a revision identifier or is NULL, in which case we load the current revision of the entity.
    $this->revision = new Revision($this->entity->getRevision());
    $this->revision->restore($_SERVER["REVISION_OLD"], $_SERVER["REVISION_NEW"]);
  }

  public function getContent() {
    ob_start();
    \Krumo::dump($this->revision);
    return ob_get_clean();
  }

}
