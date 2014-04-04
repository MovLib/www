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
   * @param string $typeOf
   *   The structured data type of the entity.
   * @param string $titleProperty [optional]
   *   The structure data property of the title, defaults to <code>"name"</code>.
   * @return this
   */
  protected function initShow(\MovLib\Data\AbstractEntity $entity, $typeOf, $titleProperty = "name") {
    $this->entity                = $entity;
    $this->schemaType            = $typeOf;
    $this->headingSchemaProperty = $titleProperty;
    $this->initPage($this->getPageTitle());
    $this->initBreadcrumb([[ $entity->getIndexRoute(), $entity->getPluralName() ]]);
    $this->initLanguageLinks("/{$entity->getSingularKey()}/{0}", $entity->id);
    $this->sidebarInit($this->getSidebarItems());
    if ($entity->isGone()) {
      throw new GoneException("The {$entity->getSingularName()} '{$entity->id}' is no longer available.");
    }
    return $this;
  }

  /**
   * Get the entity's title.
   *
   * @return string
   *   The entity's title.
   */
  protected function getPageTitle() {
    return $this->entity->name;
  }

  /**
   * Get the presenter's sidebar items.
   *
   * @return array
   *   The presenter's sidebar items.
   */
  protected function getSidebarItems() {
    $singularKey = $this->entity->getSingularKey();
    if ($this->entity->isGone()) {
      return [
        [ $this->entity->getRoute(), $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
        [ $this->intl->r("/{$singularKey}/{0}/discussion", $this->entity->id), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
        [ $this->intl->r("/{$singularKey}/{0}/history", $this->entity->id), $this->intl->t("History"), [ "class" => "ico ico-history" ] ]
      ];
    }
    return [
      [ $this->entity->getRoute(), $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
      [ $this->intl->r("/{$singularKey}/{0}/edit", $this->entity->id), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
      [ $this->intl->r("/{$singularKey}/{0}/discussion", $this->entity->id), $this->intl->t("Discuss"), [ "class" => "ico ico-discussion" ] ],
      [ $this->intl->r("/{$singularKey}/{0}/history", $this->entity->id), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
      [ $this->intl->r("/{$singularKey}/{0}/delete", $this->entity->id), $this->intl->t("Delete"), [ "class" => "ico ico-delete separator" ] ],
    ];
  }

}
