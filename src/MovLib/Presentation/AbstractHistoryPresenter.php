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

use \MovLib\Data\RevisionEntitySet;
use \MovLib\Data\DateTime;
use \MovLib\Exception\RedirectException\TemporaryRedirectException;
use \MovLib\Partial\DateTime as DateTimePartial;
use \MovLib\Partial\Time;

/**
 * Defines base class for history presenter.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistoryPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\PaginationTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the history presenter.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity to present.
   * @param string $breadcrumbIndexTitle
   *   The entity's translated index title.
   * @return this
   */
  final protected function initHistory(\MovLib\Data\AbstractEntity $entity, $breadcrumbIndexTitle) {
    $this->entity = $entity;

    if (isset($_SERVER["REVISION_OLD"]) && isset($_SERVER["REVISION_NEW"])) {
      $baseRoute = $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs);
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_NEW"]}");
      }
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }

    $pageTitle = $this->intl->t("History of {0}", [ $this->entity->name ]);
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

  /**
   *
   * @param string $entityClassName
   *   The entity's class name without namespace.
   */
  protected function getIndexContent($entityClassName) {
    $revisions = (new RevisionEntitySet($this->diContainerHTTP))
      ->loadRevisions($entityClassName, $this->entity->id, $this->paginationOffset, $this->paginationLimit)->entities;

    if (empty($revisions)) {
      return $this->getNoItemsContent();
    }
    else {
      $revisionItems = "";
      foreach ($revisions as $created => $entity) {
        $created   = new Time($this->intl, $entity->id);
        $baseRoute = $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs);
        $revisionItems .=
          "<li>" .
            "<div class='hover-item r'>" .
              $this->img($entity->user->imageGetStyle("s1"), [ "class" => "s s1", "property" => "image" ], false) .
              "<div class='s s6'>" .
                "<a href='{$entity->user->route}'><h2 class='link-color para' property='name'>{$entity->user->name}</h2></a>" .
                "<p>{$created->formatRelative()}</p>" .
              "</div>" .
              "<div class='s s3 tar'>" .
                "<p>" . (new DateTimePartial($this->intl, $this))->format(new DateTime($entity->id)) . "</p>" .
                "<p><a href='{$baseRoute}/{$entity->revision}'>{$this->intl->t("show diff")}</a></p>" .
              "</div>" .
            "</div>" .
          "</li>"
        ;
      }
      return "<ol class='hover-list no-list'>{$revisionItems}</ol>";
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->calloutInfo(
      "<p>{$this->intl->t("We couldn’t find any revisions.")}</p>",
       $this->intl->t("No Revisions")
    );
  }

}
