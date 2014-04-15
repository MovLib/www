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
namespace MovLib\Presentation\Person;

use \MovLib\Data\Person\Person;

/**
 * Defines the base class for most person presentations.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractPersonPresenter extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;

  /**
   * The person this presentation is for.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $entity;

  /**
   * Initialize the person presentation.
   *
   * @param string $routeKey
   *   The presentations' untranslated route key for the language links.
   * @param string $title
   *   The presentation's translated title.
   * @param string $pageTitle [optional]
   *   The presentation's translated page title (only has to be supplied if different from the title).
   * @param string $breadcrumbTitle
   *   The presentation's translated breadcrumb title (only has to be supplied if different from the title).
   */
  final protected function initPersonPresentation($routeKey, $title, $pageTitle = null, $breadcrumbTitle = null) {
    if (!$this->entity) {
      $this->entity = new Person($this->diContainerHTTP, (integer) $_SERVER["PERSON_ID"]);
    }
    $this->schemaType = "Person";
    $this->initPage($title, $pageTitle, $breadcrumbTitle);
    // There's no single subpage requiring another placeholder, construct language links immediately.
    $this->initLanguageLinks($routeKey, [ $this->id ]);
    $this->breadcrumb->addCrumb($this->intl->t("/persons"), $this->intl->t("Persons"));
    // We're on a subpage, add person breadcrumb.
    if ($routeKey != $this->entity->routeKey) {
      $this->breadcrumb->addCrumb($this->entity->route, $this->entity->name);
    }
    $additionalSidebarItems = null;
    if (!$this->entity->deleted) {
      foreach ([
        [ "movie", "movies", $this->intl->t("Movies"), $this->entity->countMovies ],
        [ "series", "series", $this->intl->t("Series"), $this->entity->countSeries ],
        [ "release", "releases", $this->intl->t("Releases"), $this->entity->countReleases ],
        [ "award separator", "awards", $this->intl->t("Awards"), $this->entity->countAwards ],
      ] as list($icon, $routeAddition, $title, $count)) {
        $additionalSidebarItems[] = [
          $this->intl->r("/person/{0}/{$routeAddition}", $this->entity->id),
          "{$title} <span class='fr'>{$this->intl->format("{0,number}", $count)}</span>",
          [ "class" => "ico ico-{$icon}" ]
        ];
      }
    }
    $this->sidebarInitToolbox($this->entity, $additionalSidebarItems);
  }

  /**
   * Get the formatted born name with structured data.
   *
   * @param \MovLib\Data\Person\Person $person
   * @param type $wrap
   * @param type $wrapAttributes
   */
  protected function getStructuredBornName(\MovLib\Data\Person\Person $person, $wrap = null, $wrapAttributes = null) {
    if ($person->bornName) {
      if ($wrap) {
        $wrapAttributes["property"] = "additionalName";
        return "<{$wrap}{$this->expandTagAttributes($wrapAttributes)}>{$person->bornName}</{$wrap}>";
      }
      return "<span property='additionalName'>{$person->bornName}</span>";
    }
  }

}