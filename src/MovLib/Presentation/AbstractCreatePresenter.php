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
 * Defines base class for all create presenter.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>v
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractCreatePresenter extends \MovLib\Presentation\AbstractPresenter {
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
   * Initialize the create presenter.
   *
   * @param \MovLib\Data\AbstractEntity $entity
   *   The new entity to create.
   * @param string $breadcrumbIndexTitle
   *   The entity's index title.
   * @return this
   */
  final protected function initCreate(\MovLib\Data\AbstractEntity $entity, $breadcrumbIndexTitle) {
    $this->entity = $entity;

    if (!isset($this->entity->routeIndex)) {
      $this->entity->routeIndex = $this->intl->r("/{$this->entity->pluralKey}");
    }

    $this->sidebarInit([
      [ $this->entity->routeIndex, $breadcrumbIndexTitle, [ "class" => "ico ico-{$this->entity->singularKey}"] ],
      [ $this->intl->r("/help/database/{$this->entity->pluralKey}/create"), $this->intl->t("Help"), [ "class" => "ico ico-help"] ],
    ]);
    $this->initLanguageLinks("/{$this->entity->singularKey}/create");
    $this->breadcrumb->addCrumbs([
      [ $this->entity->routeIndex, $breadcrumbIndexTitle ],
    ]);
    return $this;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
    $this->entity->create();
    $this->alertSuccess($this->intl->t("The {$this->entity->singularKey} was created successfully."));
    throw new SeeOtherException($this->entity->route);
  }

}
