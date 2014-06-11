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
namespace MovLib\Core\Presentation;

use \MovLib\Data\History\HistorySet;
use \MovLib\Partial\DateTime;

/**
 * Defines the abstract history base class.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractHistory extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\PaginationTrait;


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractHistory";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The history set containing the entity's revisions to present.
   *
   * @var \MovLib\Data\History\HistorySet
   */
  protected $historySet;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    // We can create the entity's class name from the namespace of the concrete presentation.
    $classParts  = explode("\\", static::class);
    array_pop($classParts);
    $entityName  = end($classParts);
    $entityClass = "\\MovLib\\Data\\{$entityName}\\{$entityName}";
    $serverVar   = strtoupper($entityName);

    // Instantiate the entity, the history set and configure the presentation.
    $this->entity     = new $entityClass($this->container, $_SERVER["{$serverVar}_ID"]);
    $this->historySet = new HistorySet($entityName, $this->entity->id);
    $this->initPage(
      /// The {lemma} will be enclosed in localized quotes, e.g.: History of “Cat Movie”
      $this->intl->t("History of {lemma}", [ "lemma" => $this->entity->lemma ]),
      null,
      $this->intl->t("History")
    );
    $this->sidebarInitToolbox($this->entity);
    $this->breadcrumb->addCrumbs([
      [ $this->entity->set->route, $this->entity->set->bundleTitle ],
      [ $this->entity->route, $this->entity->lemma ],
    ]);
    $this->paginationInit($this->historySet->getTotalCount());
    $this->historySet->load($this->container, $this->paginationOffset, $this->paginationLimit);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $listItems = null;

    if ($this->paginationTotalResults > 1) {
      $button = "";
    }

    $created  = $this->entity->created->formatInteger();
    $current  = $this->entity->changed->formatInteger();
    $dateTime = new DateTime($this->intl, $this, $this->session->userTimezone);

    /* @var $revision \MovLib\Data\Revision\AbstractRevisionEntity */
    foreach ($this->historySet as $revision) {
      $createdInfo = null;
      if ($revision->id === $created) {
        $createdInfo = "<br><span class='small'>{$this->intl->t("Created")}</span>";
      }
      if ($revision->id === $current) {
        $diffToCurrentVersion = $this->intl->t("Current revision.");
      }
      else {
        $diffToCurrentVersion =
          "<a href='{$this->entity->r("/history/{0}", [ $revision->id ])}'>" .
            $this->intl->t("Compare to current revision.") .
          "</a>"
        ;
      }
      $listItems .=
        "<li><div class='hover-item r'>" .
          $this->img($revision->user->imageGetStyle("s1"), [ "class" => "s s1", "property" => "image" ], false) .
          "<div class='s s5'>" .
            "<h2 class='para'><a href='{$revision->user->route}'>{$revision->user->name}</a></h2>" .
            "<small>{$diffToCurrentVersion}</small>" .
          "</div>" .
          "<p class='s s4 tar'>{$dateTime->formatRelative($revision->created, $this->request->dateTime)}{$createdInfo}</p>" .
        "</div></li>"
      ;
    }

    return "<form action='{$this->request->uri}'><ol class='hover-list no-list'>{$listItems}</ol></form>";
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->calloutWarning($this->intl->t("We couldn’t find any revisions."), $this->intl->t("No Revisions"));
  }

}
