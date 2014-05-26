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
use \MovLib\Component\DateTime;
use \MovLib\Exception\RedirectException\TemporaryRedirectException;
use \MovLib\Partial\DateTime as DateTimePartial;

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
    $baseRoute    = $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs);

    // redirect on compare
    if (isset($_GET["old"]) && isset($_GET["new"])) {
      throw new TemporaryRedirectException("{$baseRoute}/{$_GET["old"]}/{$_GET["new"]}");
    }

    // redirect on wrong user input
    if (isset($_SERVER["REVISION_OLD"]) && isset($_SERVER["REVISION_NEW"])) {
      if ($_SERVER["REVISION_OLD"] == $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_NEW"]}");
      }
      elseif ($_SERVER["REVISION_OLD"] > $_SERVER["REVISION_NEW"]) {
        throw new TemporaryRedirectException("{$baseRoute}/{$_SERVER["REVISION_NEW"]}/{$_SERVER["REVISION_OLD"]}");
      }
    }

    if (!isset($this->entity->name)) {
      if (isset($this->entity->displayTitle)) {
        $this->entity->name = $this->entity->displayTitle;
      }
      elseif (isset($this->entity->title)) {
        $this->entity->name = $this->entity->title;
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
   * Build diff content.
   *
   * @param string $entityClassName
   *   The entity's class name without namespace.
   */
  protected function getDiffContent($entityClassName) {
    return $this->checkBackLater("{$entityClassName} diff");
  }

  /**
   * Build paginated revision index.
   *
   * @param string $entityClassName
   *   The entity's class name without namespace.
   */
  protected function getIndexContent($entityClassName) {
    $revisions = (new RevisionEntitySet($this->container))
      ->loadRevisions($entityClassName, $this->entity->id, $this->paginationOffset, $this->paginationLimit)->entities;

    if (empty($revisions)) {
      return $this->getNoItemsContent();
    }
    else {
      $revisionItems = "";
      $c             = 0;
      foreach ($revisions as $entity) {
        $radioButtons = $revisionButton = null;
        $mainSpanSize = 5;
        if (count($revisions) >= 2) {
          $mainSpanSize = 3;
          $radioButtons =
            "<div class='s s2 tar'>" .
              "<label class='radio radio-inline'>" .
                "<input name='new' required type='radio' value='{$entity->revision}'" . (($c==0) ? " checked" : null) . ">{$this->intl->t("new")}" .
              "</label>" .
              "<label class='radio radio-inline'>" .
                "<input name='old' required type='radio' value='{$entity->revision}'" . (($c==1) ? " checked" : null) . ">{$this->intl->t("old")}" .
              "</label>" .
            "</div>"
          ;
          $revisionButton = "<div class='s s10 tar'><input class='btn btn-medium btn-success' name='submit' type='submit' value='{$this->intl->t("Compare Revisions")}'></div>";
        }
        $baseRoute      = $this->intl->r("{$this->entity->routeKey}/history", $this->entity->routeArgs);
        $revisionItems .=
          "<li>" .
            "<div class='hover-item r'>" .
              $this->img($entity->user->imageGetStyle("s1"), [ "class" => "s s1", "property" => "image" ], false) .
              "<div class='s s{$mainSpanSize}'>" .
                "<a href='{$entity->user->route}'><h2 class='link-color para'>{$entity->user->name}</h2></a>" .
                "<p class='small'><a href='{$baseRoute}/{$entity->revision}'>{$this->intl->t("diff to current")}</a></p>" .
              "</div>" .
              "<div class='s s4 tar'>" .
                (new DateTimePartial($this->intl, $this))->format(new DateTime($entity->id)) .
              "</div>" .
              $radioButtons .
            "</div>" .
          "</li>"
        ;
        ++$c;
      }
      return "<form action='{$this->request->uri}'><ol class='hover-list no-list'>{$revisionItems}</ol>{$revisionButton}</form>";
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
