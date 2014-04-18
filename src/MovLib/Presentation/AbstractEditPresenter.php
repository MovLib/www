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

use \MovLib\Exception\RedirectException\SeeOtherException;

/**
 * Defines base class for edit presenter.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>v
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractEditPresenter extends \MovLib\Presentation\AbstractPresenter {
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
   * Initialize the edit presenter.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity to present.
   * @param string $breadcrumbIndexTitle
   *   The entity's index title.
   * @param string $additionalSidebarItems [optional]
   *   Additional items for the sidebar.
   * @return this
   */
  final protected function initEdit(\MovLib\Data\AbstractEntity $entity, $breadcrumbIndexTitle, $additionalSidebarItems = []) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->title), "You have to call initPage() before you call initEdit()!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->entity = $entity;
    $this->sidebarInitToolbox($this->entity, $additionalSidebarItems);
    $this->initLanguageLinks("/{$this->entity->singularKey}/{0}/edit", $this->entity->id);
    $this->breadcrumb->addCrumbs([
      [ $this->intl->r("/{$this->entity->pluralKey}"), $breadcrumbIndexTitle ],
      [ $this->entity->route, $this->entity->name ]
    ]);
    return $this;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
    $this->entity->commit();
    $this->alertSuccess($this->intl->t("The {$this->entity->singularKey} was updated successfully."));
    throw new SeeOtherException($this->entity->route);
  }

}
