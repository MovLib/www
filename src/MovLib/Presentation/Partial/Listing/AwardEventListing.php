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
namespace MovLib\Presentation\Partial\Listing;

use \MovLib\Presentation\Partial\Date;
use \MovLib\Presentation\Partial\Place;

/**
 * Images list for entity instances with series and movie counts.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class AwardEventListing extends \MovLib\Presentation\Partial\Listing\EntityIndexListing {


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getAdditionalContent($event, $listItem) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if(!($event instanceof $this->entity)) {
      throw new \InvalidArgumentException("\$event must be of type {$this->entity}");
    }
    if (!isset($event->place)) {
      throw new \LogicException($i18n->t("\$event->place has to be set!"));
    }
    if (!isset($event->startDate)) {
      throw new \LogicException($i18n->t("\$event->startDate has to be set!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $currentMovieRoute  = str_replace("{{ id }}", $event->id, $this->moviesRoute);
    $currentSeriesRoute = str_replace("{{ id }}", $event->id, $this->seriesRoute);

    // Put the event information together.
    $info = null;
    if (($event->startDate && $event->endDate) && ($event->startDate != $event->endDate)) {
      $info .= "{$i18n->t("from {0} to {1}", [
        (new Date($event->startDate))->format(),
        (new Date($event->endDate))->format()
      ])} ";
    }
    else if ($event->startDate) {
      $info .= "{$i18n->t("on {0}", [ (new Date($event->startDate))->format() ])} ";
    }
    if ($event->place) {
      $info .= $i18n->t("in {0}", [ new Place($event->place) ]);
    }

    return
      "<span class='fr'>" .
        "<a class='ico ico-movie label' href='{$currentMovieRoute}' title='{$this->moviesTitle}'>" .
          " &nbsp; {$event->getMoviesCount()}" .
        "</a>" .
        "<a class='ico ico-series label' href='{$currentSeriesRoute}' title='{$this->seriesTitle}'>" .
          " &nbsp; {$event->getSeriesCount()}" .
        "</a>" .
      "</span>".
      "<div class='small'>{$info}</div>"
    ;
  }

}
