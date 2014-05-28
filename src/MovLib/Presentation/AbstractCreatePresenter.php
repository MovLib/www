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

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "AbstractCreatePresenter";
  // @codingStandardsIgnoreEnd
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
    $this->session->checkAuthorization($this->intl->t(
      "You must be signed in to access this content. Please use the form below to sign in or {0}join {sitename}{1}.",
      [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
    ));
    $this->entity = $entity;

    if (empty($this->entity->routeIndex)) {
      $this->entity->routeIndex = $this->intl->r("/{$this->entity->pluralKey}");
    }

    $this->sidebarInit([
      [ $this->entity->routeIndex, $breadcrumbIndexTitle, [ "class" => "ico ico-{$this->entity->singularKey}"] ],
      [ $this->intl->r("/help/database/{$this->entity->pluralKey}"), $this->intl->t("Help"), [ "class" => "ico ico-help"] ],
    ]);
    $this->initLanguageLinks("/{$this->entity->singularKey}/create");
    $this->breadcrumb->addCrumb($this->entity->routeIndex, $breadcrumbIndexTitle);
    return $this;
  }

  /**
   * Auto-validation of the form succeeded.
   *
   * @return this
   */
  public function valid() {
    $this->entity->create($this->session->userId, $this->request->dateTime);
    $this->alertSuccess($this->intl->t("Successfully created"));
    throw new SeeOtherException($this->entity->route);
  }

}
