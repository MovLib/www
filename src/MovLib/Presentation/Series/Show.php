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
namespace MovLib\Presentation\Series;

use \MovLib\Data\Series\Series;
use \MovLib\Data\Series\TitleSet;
use \MovLib\Partial\Date;
use \MovLib\Partial\StarRatingForm;

/**
 * Defines the series show presentation.
 *
 * @link http://schema.org/Series
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/series/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/series/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/series/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/series/{id}
 *
 * @property \MovLib\Data\Series\Series $entity
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Presentation\Series\SeriesTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->entity = new Series($this->diContainerHTTP, $_SERVER["SERIES_ID"]);
    $this
      ->initPage($this->entity->displayTitle, $this->getStructuredDisplayTitle($this->entity, false, true))
      ->initShow($this->entity, $this->intl->tp(-1, "Series"), "Series", null, $this->getSidebarItems())
    ;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $starRating = new StarRatingForm($this->diContainerHTTP, $this->entity);
    $this->infoboxBefore =
      "{$this->getStructuredOriginalTitle($this->entity, "p")}{$starRating}"
    ;

    $this->entity->startYear && $this->infoboxAdd($this->intl->t("From"), (new Date($this->intl, $this))->format($this->entity->startYear));
    $this->entity->endYear   && $this->infoboxAdd($this->intl->t("To"), (new Date($this->intl, $this))->format($this->entity->endYear));
    $this->entity->status    && $this->infoboxAdd($this->intl->t("Status"), $this->getStatus());

    $this->entity->synopsis  && $this->sectionAdd($this->intl->t("Synopsis"), $this->entity->synopsis);

    $this->sectionAdd($this->intl->t("Titles"), $this->getTitleSection(), true, null, $this->intl->r("/series/{0}/titles", $this->entity->id));

    if ($this->sections) {
      return $this->sections;
    }

    return $this->callout(
      $this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/series/{0}/edit", $this->entity->id)}'>", "</a>" ]),
      $this->intl->t("{sitename} doesn’t have further details about this series.", [ "sitename" => $this->config->sitename ])
    );
  }

  protected function getTitleSection() {
    $items = null;
    foreach ((new TitleSet($this->diContainerHTTP))->loadEntitiesBySeries($this->entity)->entities as $entity) {
      $original = $entity["isOriginalTitle"]? " ({$this->intl->t("Originaltitel")})" : null;
      $items .=
        "<tr>" .
          "<td class='s8'>{$entity["title"]}{$original}</td>" .
          "<td class='s2'>{$this->intl->getTranslations("languages")[$entity["languageCode"]]->name}</td>" .
        "</tr>"
      ;
    }
    return
      "<table class='table table-striped'>" .
        "<thead>" .
          "<tr>" .
            "<th>{$this->intl->t("Title")}</th>" .
            "<th>{$this->intl->t("Language")}</th>" .
          "</tr>" .
        "</thead>" .
        "<tbody>{$items}</tbody>" .
      "</table>"
    ;
  }

}
