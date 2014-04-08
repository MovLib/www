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
  use \MovLib\Partial\CompanyTrait;
  use \MovLib\Partial\ContentSectionTrait;
  use \MovLib\Partial\InfoboxTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initShow(
      new Company($this->diContainerHTTP, $_SERVER["COMPANY_ID"]),
      $this->intl->t("Companies"),
      $this->intl->t("Company"),
      "Corporation"
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // Build the movie's infobox.
    $this->infoboxInit($this->entity);
    $this->entity->links        && $this->infoboxAdd($this->intl->t("Sites"), $this->formatWeblinks($this->entity->links));
    $this->entity->foundingDate && $this->infoboxAdd($this->intl->t("Founded"), (new Date($this->intl, $this))->format($this->entity->foundingDate, [ "property" => "foundingDate" ]));
    $this->entity->defunctDate  && $this->infoboxAdd($this->intl->t("Defunct"), (new Date($this->intl, $this))->format($this->entity->defunctDate, [ "property" => "defunctDate" ]));
    $this->entity->place        && $this->infoboxAdd($this->intl->t("Based in"), new Place($this, $this->intl, $this->entity->place, [ "property" => "location" ]));

    // Build the movie's content and return if we have any.
    $this->entity->description && $this->addContentSection($this->intl->t("Profile"), $this->entity->description);
    $this->entity->aliases     && $this->addContentSection($this->intl->t("Also Known As"), $this->formatAliases($this->entity->aliases), false);
    if (($content = $this->getContentSections())) {
      return $content;
    }

    // Otherwise let the client know that we have no further information for this movie.
    return new Alert(
      "<p>{$this->intl->t("{sitename} doesn’t have further details about this company.", [ "sitename" => $this->config->sitename ])}</p>" .
      "<p>{$this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/company/{0}/edit", $this->entity->id)}'>", "</a>" ])}</p>",
      $this->intl->t("No Info")
    );
  }

}
