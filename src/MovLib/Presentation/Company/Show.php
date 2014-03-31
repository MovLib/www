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
use \MovLib\Partial\Place;
use \MovLib\Partial\QuickInfo;

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
  use \MovLib\Partial\DateTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initShow(new Company($this->diContainerHTTP), "Corporation", "name");
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {

    // Build the company's header.
    $this->headingBefore = "<div class='r'><div class='s s10'>";
    $infos = new QuickInfo($this->intl);
    $this->entity->links        && $infos->add($this->intl->t("Sites"), $this->formatWeblinks($this->entity->links));
    $this->entity->foundingDate && $infos->add($this->intl->t("Founded"), $this->dateFormat($this->entity->foundingDate, [ "property" => "foundingDate" ]));
    $this->entity->defunctDate  && $infos->add($this->intl->t("Defunct"), $this->dateFormat($this->entity->defunctDate, [ "property" => "defunctDate" ]));
    $this->entity->place->id    && $infos->add($this->intl->t("Based in"), new Place($this, $this->intl, $this->entity->place, [ "property" => "location" ]));
    $this->entity->wikipedia    && $infos->addWikipedia($this->entity->wikipedia);
    $this->headingAfter .= "{$infos}</div><div id='company-logo' class='s s2'><img alt='' src='{$this->getExternalURL("asset://img/logo/vector.svg")}' width='140' height='140'></div></div>";

    $this->addContentSection($this->intl->t("Profile"), $this->entity->description);
    return $this->getContentSections();

    $companyAliases = $this->company->aliases;
    if (!empty($companyAliases)) {
      $aliases = null;
      $c       = count($companyAliases);
      for ($i = 0; $i < $c; ++$i) {
        $aliases .= "<li class='mb10 s s10' property='additionalName'>{$companyAliases[$i]}</li>";
      }
      $content .= $this->getSection("aliases", $this->intl->t("Also Known As"), "<ul class='grid-list r'>{$aliases}</ul>");
    }

    if ($content) {
      return $content;
    }

    return new Alert(
      $this->intl->t("{sitename} has no further details about this company.", [ "sitename"    => $this->config->sitename ]),
      $this->intl->t("No Data Available"),
      Alert::SEVERITY_INFO
    );
  }

  /**
   * Construct a section in the main content and add it to the sidebar.
   *
   * @param string $id
   *   The section's unique identifier.
   * @param string $title
   *   The section's translated title.
   * @param string $content
   *   The section's content.
   * @return string
   *   The section ready for display.
   */
  protected function getSection($id, $title, $content) {
    // Add the section to the sidebar as anchor.
    $this->sidebarNavigation->menuitems[] = [ "#{$id}", $title ];

    return "<div id='{$id}'><h2>{$title}</h2>{$content}</div>";
  }

}
