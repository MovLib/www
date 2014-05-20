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
    $this->session->checkAuthorization($this->intl->t(
      "You must be signed in to access this content. Please use the form below to sign in or {0}join {sitename}{1}.",
      [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
    ));
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($this->title), "You have to call initPage() before you call initEdit()!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->entity = $entity;
    $this->sidebarInitToolbox($this->entity, $additionalSidebarItems);
    $this->initLanguageLinks("/{$this->entity->singularKey}/{0}/edit", $this->entity->id);

    if (!isset($this->entity->name)) {
      if (isset($this->entity->displayTitle)) {
        $this->entity->name = $this->entity->displayTitle;
      }
      elseif (isset($this->entity->title)) {
        $this->entity->name = $this->entity->title;
      }
    }

    $this->breadcrumb->addCrumbs([
      [ $this->entity->routeIndex, $breadcrumbIndexTitle ],
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
    $this->alertSuccess($this->intl->t("Successfully updated"));
    throw new SeeOtherException($this->entity->route);
  }

}
