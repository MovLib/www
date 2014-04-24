<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright Â© 2013-present {@link https://movlib.org/ MovLib}.
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
namespace MovLib\Presentation\Movie;

use \MovLib\Data\Cast\CastSet;
use \MovLib\Data\Director\DirectorSet;
use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Movie\MovieTitleSet;
use \MovLib\Partial\Country;
use \MovLib\Partial\Duration;
use \MovLib\Partial\Genre;
use \MovLib\Partial\Sex;
use \MovLib\Partial\StarRatingForm;

/**
 * Defines the movie presentation.
 *
 * @link http://schema.org/Movie
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/movie/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/movie/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/movie/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/movie/{id}
 *
 * @property \MovLib\Data\Movie\Movie $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\Movie\AbstractMoviePresenter {
  use \MovLib\Partial\InfoboxTrait;
  use \MovLib\Partial\SectionTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initMoviePresenation();
    $this->stylesheets[] = "movie";
    $this->javascripts[] = "Movie";
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $starRating = new StarRatingForm($this->diContainerHTTP, $this->entity);
    $this->infoboxBefore =
      "{$this->getStructuredOriginalTitle($this->entity, "p")}{$this->getStructuredTagline($this->entity)}{$starRating}"
    ;
    $this->infoboxImageRoute = $this->intl->r("/movie/{0}/posters", $this->entity->id);

    $directorsInfo = null;
    $directorJob   = null;
    $directors     = new DirectorSet($this->diContainerHTTP);
    /* @var $director \MovLib\Data\Director\Director */
    foreach ($directors->loadMovieDirectorsLimited($this->entity) as $director) {
      if (!$directorJob) {
        $directorJob = $director->names[Sex::UNKNOWN];
      }
      if ($directorsInfo) {
        $directorsInfo .= ", ";
      }
      $directorsInfo .= "<a href='{$this->intl->r("/person", $director->personId)}'>{$director->personName}</a>";
    }
    if ($directorsInfo) {
      $this->infoboxAdd($directorJob, $directorsInfo);
    }

    $castInfo = null;
    $cast     = new CastSet($this->diContainerHTTP);
    foreach ($cast->loadMovieCastLimited($this->entity) as $castMember) {
      if ($castInfo) {
        $castInfo .= ", ";
      }
      $castInfo .= "<a href='{$this->intl->r("/person", $castMember->personId)}'>{$castMember->personName}</a>";
    }
    if ($castInfo) {
      $this->infoboxAdd($this->intl->t("Cast"), $castInfo);
    }

    $this->entity->runtime   && $this->infoboxAdd($this->intl->t("Runtime"), (new Duration($this->diContainerHTTP))->formatMinutes($this->entity->runtime, [ "property" => "runtime" ]));
    $this->entity->genreSet  && $this->infoboxAdd($this->intl->t("Genres"), (new Genre($this->diContainerHTTP))->getList($this->entity->genreSet));
    $this->entity->countries && $this->infoboxAdd($this->intl->t("Countries"), (new Country($this->diContainerHTTP))->getList($this->entity->countries, "contentLocation"));

    $this->entity->synopsis && $this->sectionAdd($this->intl->t("Synopsis"), $this->entity->synopsis, true, "callout");
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($this->entity->synopsis)) {
      $this->sectionAdd($this->intl->t("Synopsis"), "<div class='quotes'>{$this->blindtext()}</div>");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $titleSet = new MovieTitleSet($this->diContainerHTTP, $this->entity->id);
    $titles = null;
    /* @var $title \MovLib\Data\Title\Title */
    foreach ($titleSet->loadEntityTitles() as $title) {
      $titles .=
        "<tr>" .
          "<td class='s8'>{$title->title}</td>" .
          "<td class='s2'>{$this->intl->getTranslations("languages")[$title->languageCode]->name}</td>" .
        "</tr>"
      ;
    }
    if ($titles) {
      $titles =
        "<table class='table table-striped'>" .
          "<thead>" .
            "<tr>" .
              "<th>{$this->intl->t("Title")}</th>" .
              "<th>{$this->intl->t("Language")}</th>" .
            "</tr>" .
          "</thead>" .
          "<tbody>{$titles}</tbody>" .
        "</table>"
      ;
      $this->sectionAdd($this->intl->t("Alternative Titles"), $titles, true, null, $this->intl->r("{$this->entity->routeKey}/titles", $this->entity->id));
    }
    $this->sectionAdd($this->intl->t("Trailers"), "Not implemented yet!", false, "callout callout-warning");
    $this->sectionAdd($this->intl->t("Weblinks"), "Not implemented yet!", false, "callout callout-danger");

    if ($this->sections) {
      return $this->sections;
    }

    return "";
  }

  /**
   * {@inheritdoc}
   */
  protected function getPageTitle() {
    return $this->entity->displayTitleAndYear;
  }

}
