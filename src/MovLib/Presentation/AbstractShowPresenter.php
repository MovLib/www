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

use \MovLib\Exception\ClientException\GoneException;

/**
 * Defines base class for show presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractShowPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\SectionTrait;
  use \MovLib\Partial\InfoboxTrait;

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractShowPresenter";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The entity to present.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Initialize the show presenter.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity to present.
   * @param string $pluralName
   *   The entity's plural name.
   * @param string $singularName
   *   The entity's singular name.
   * @param string $typeOf
   *   The structured data type of the entity.
   * @param string $titleProperty [optional]
   *   The structure data property of the title, defaults to <code>"name"</code>.
   * @param string $additionalSidebarItems [optional]
   *   Additional items for the sidebar.
   * @return this
   */
  final protected function initShow(\MovLib\Data\AbstractEntity $entity, $breadcrumbIndexTitle, $typeOf, $pageTitleProperty = "name", $additionalSidebarItems = []) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->title), "You have to call initPage() before you call initShow()!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->entity                = $entity;
    $this->schemaType            = $typeOf;
    $this->headingSchemaProperty = $pageTitleProperty;
    $this->breadcrumb->addCrumb($entity->set->route, $breadcrumbIndexTitle);
    $this->initLanguageLinks("/{$entity->set->singularKey}/{0}", $entity->id);
    $this->sidebarInitToolbox($entity, $additionalSidebarItems);
    if ($entity->deleted) {
      throw new GoneException("The {$this->entity->set->singularKey} {$this->entity->id} is no longer available.");
    }
    return $this;
  }

  /**
   * Get the presenter's sidebar items.
   *
   * @deprecated
   * @return array
   *   The presenter's sidebar items.
   */
  protected function getSidebarItems() {
    if ($this->entity->deleted === true) {
      return [
        [ $this->entity->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
        [ $this->entity->r("/discussion", [ $this->entity->route->args ]), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->entity->r("/history", [ $this->entity->route->args ]), $this->intl->t("History"), [ "class" => "ico ico-history" ] ]
      ];
    }
    return [
      [ $this->entity->route, $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->entity->r("/edit", [ $this->entity->route->args ]), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->entity->r("/discussion", [ $this->entity->route->args ]), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->entity->r("/history", [ $this->entity->route->args ]), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->entity->r("/delete", [ $this->entity->route->args ]), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],
    ];
  }

}
