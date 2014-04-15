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
use \MovLib\Partial\Date;
use \MovLib\Partial\Place;
use \MovLib\Partial\Sex;

/**
 * Presentation of a single person.
 *
 * @link http://schema.org/Person
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/person/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/person/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/person/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/person/{id}
 *
 * @property \MovLib\Data\Person\Person $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\Person\AbstractPersonPresenter {
  use \MovLib\Partial\InfoboxTrait;
  use \MovLib\Partial\SectionTrait;


  // ------------------------------------------------------------------------------------------------------------------- Initialization Methods.


  /**
   * Initialize person presentation.
   *
   * @throws \MovLib\Presentation\Error\NotFound
   */
  public function init() {
    $this->initPersonPresentation();
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    // Enhance the name with the sex specific symbol.
    if ($this->entity->sex !== Sex::UNKNOWN) {
      if ($this->entity->sex === Sex::MALE) {
        $sexTitle = $this->intl->t("Male");
      }
      if ($this->entity->sex === Sex::FEMALE) {
        $sexTitle = $this->intl->t("Female");
      }
      $this->pageTitle .=  "<sup class='ico ico-sex{$this->entity->sex} sex sex-{$this->entity->sex}' content='{$sexTitle}' property='gender' title='{$sexTitle}'></sup>";
    }

    $this->infoboxBefore     = "<span class='vh'>{$this->intl->t("Born as: ")}</span>{$this->getStructuredBornName($this->entity, "p")}";
    $this->infoboxImageRoute = $this->intl->r("/person/{0}/photo", $this->entity->id);

    $date = new Date($this->intl, $this);

    $age       = null;
    $birthInfo = null;
    if ($this->entity->birthDate) {
      $birthInfo = $date->format($this->entity->birthDate, [ "property" => "birthDate" ]);
      $age       = $date->getAge($this->entity->birthDate);
      if ($this->entity->deathDate) {
        $age = $this->intl->t("would be {age}", [ "age" => $age ]);
      }
      else {
        $age = $this->intl->t("aged {age}", [ "age" => $age ]);
      }
    }
    if (($birthPlace = $this->entity->getBirthPlace())) {
      $birthInfo = $this->intl->t("{date} in {place}", [ "date" => $birthInfo, "place" => new Place($this->diContainerHTTP, $birthPlace) ]);
    }
    if ($birthInfo && $age) {
      $birthInfo = $this->intl->t("{0} ({1})", [ $birthInfo, $age ]);
    }

    $deathInfo = null;
    $deathAge = null;
    if ($this->entity->deathDate) {
      if ($this->entity->birthDate) {
        $deathAge = $date->getAge($this->entity->birthDate, $this->entity->deathDate);
      }
      $deathInfo = $date->format($this->entity->deathDate, [ "property" => "deathDate" ]);
    }
    if (($deathPlace = $this->entity->getDeathPlace())) {
      $deathInfo = $this->intl->t("{date} in {place}", [ "date" => $deathInfo, "place" => new Place($this->diContainerHTTP, $deathPlace) ]);
    }
    if ($deathAge) {
      $deathInfo = $this->intl->t("{0} ({1})", [ $deathInfo, $this->intl->t("aged {0}", [ $deathAge ]) ]);
    }

    $birthInfo && $this->infoboxAdd($this->intl->t("Born"), $birthInfo);
    $deathInfo && $this->infoboxAdd($this->intl->t("Died"), $deathInfo);

    $this->entity->biography && $this->sectionAdd($this->intl->t("Biography"), $this->entity->biography);

    if (($aliases = $this->entity->getAliases())) {
      $c            = count($aliases);
      $aliasContent = null;
      for ($i = 0; $i < $c; ++$i) {
        $aliasContent .= "<li class='mb s s3' property='additionalName'>{$aliases[$i]}</li>";
      }
      $this->sectionAdd($this->intl->t("Also Known As"), "<ol class='no-list r'>{$aliasContent}</ol>");
    }

    if (($links = $this->entity->getLinks())) {
      $c           = count($links);
      $linkContent = null;
      for ($i = 0; $i < $c; ++$i) {
        $hostname     = str_replace("www.", "", parse_url($links[$i], PHP_URL_HOST));
        $linkContent .= "<li class='mb10 s s3'><a href='{$links[$i]}' rel='nofollow' target='_blank'>{$hostname}</a></li>";
      }
    }

    if ($this->sections) {
      return $this->sections;
    }
    return $this->callout(
      "<p>{$this->intl->t("{sitename} doesnâ€™t have further details about this person.", [ "sitename" => $this->config->sitename ])}</p>" .
      "<p>{$this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/person/{0}/edit", $this->entity->id)}'>", "</a>" ])}</p>",
      $this->intl->t("No Information"),
      "info"
    );
  }

}
