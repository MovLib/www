<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Company\Logo;

use \MovLib\Data\Company\Company;

/**
 * Image details presentation for a company's logo.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractPresenter {
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
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Company($this->diContainerHTTP, $_SERVER["COMPANY_ID"]);
    $pageTitle    = $this->intl->t("Logo of {0}", [ $this->entity->name ]);
    return $this
      ->initPage($pageTitle, $pageTitle, $this->intl->t("Logo"))
      ->sidebarInitToolbox($this->entity)
      ->initLanguageLinks("/{$this->entity->singularKey}/{0}/logo", $this->entity->id)
      ->breadcrumb->addCrumbs([
        [ $this->intl->r("/companies"), $this->intl->t("Companies") ],
        [ $this->entity->route, $this->entity->name ]
      ])
    ;

  }

  /**
   * @inheritdoc
   * @return \MovLib\Presentation\Partial\Alert
   */
  public function getContent() {
    return $this->checkBackLater($this->intl->t("company logo"));
  }

}