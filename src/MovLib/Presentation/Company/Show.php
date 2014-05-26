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
namespace MovLib\Presentation\Company;

use \MovLib\Data\Company\Company;
use \MovLib\Partial\Date;
use \MovLib\Partial\Place;

/**
 * Defines the company show presentation.
 *
 * @link http://schema.org/Corporation
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/company/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/company/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/company/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/company/{id}
 *
 * @property \MovLib\Data\Company\Company $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Presentation\Company\CompanyTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Company($this->container, $_SERVER["COMPANY_ID"]);
    $this
      ->initPage($this->entity->name)
      ->initShow($this->entity, $this->intl->t("Companies"), "Company", null, $this->getSidebarItems())
    ;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->entity->links        && $this->infoboxAdd($this->intl->t("Sites"), $this->formatWeblinks($this->entity->links));
    $this->entity->foundingDate && $this->infoboxAdd($this->intl->t("Founded"), (new Date($this->intl, $this))->format($this->entity->foundingDate, [ "property" => "foundingDate" ]));
    $this->entity->defunctDate  && $this->infoboxAdd($this->intl->t("Defunct"), (new Date($this->intl, $this))->format($this->entity->defunctDate, [ "property" => "defunctDate" ]));
    //$this->entity->place        && $this->infoboxAdd($this->intl->t("Based in"), new Place($this, $this->intl, $this->entity->place, [ "property" => "location" ]));

    // Build the movie's content and return if we have any.
    $this->entity->description && $this->sectionAdd($this->intl->t("Profile"), $this->entity->description);
    $this->entity->aliases     && $this->sectionAdd($this->intl->t("Also Known As"), $this->formatAliases($this->entity->aliases), false);
    if ($this->sections) {
      return $this->sections;
    }

    // Otherwise let the client know that we have no further information for this company.
    return $this->callout(
      $this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/company/{0}/edit", $this->entity->id)}'>", "</a>" ]),
      $this->intl->t("{sitename} doesn’t have further details about this company.", [ "sitename" => $this->config->sitename ])
    );
  }

}
