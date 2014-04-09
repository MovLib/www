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

use \MovLib\Data\Movie\Movie;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Country;
use \MovLib\Partial\Duration;
use \MovLib\Partial\Genre;
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
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Partial\SectionTrait;
  use \MovLib\Partial\InfoboxTrait;
  use \MovLib\Presentation\Movie\MovieTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initShow(
      new Movie($this->diContainerHTTP, $_SERVER["MOVIE_ID"]),
      $this->intl->t("Movies"),
      $this->intl->t("Movie"),
      "Movie",
      null
    );
    $this->stylesheets[] = "movie";
    $this->javascripts[] = "Movie";
    $this->pageTitle     = $this->getStructuredDisplayTitle($this->entity, false, true);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $originalTitle = $this->getStructuredOriginalTitle($this->entity, "p");
    $tagline       = $this->getStructuredTagline($this->entity);
    $rating        = new StarRatingForm($this->diContainerHTTP, $this->entity);

    $this->infoboxInit(
      $this->entity,
      $this->intl->r("/movie/{0}/posters", $this->entity->id),
      "{$originalTitle}{$tagline}{$rating}"
    );

    $this->entity->runtime   && $this->infoboxAdd($this->intl->t("Runtime"), (new Duration($this->diContainerHTTP))->formatMinutes($this->entity->runtime, [ "property" => "runtime" ]));
    $this->entity->genreSet  && $this->infoboxAdd($this->intl->t("Genres"), (new Genre($this->diContainerHTTP))->getList($this->entity->genreSet));
    $this->entity->countries && $this->infoboxAdd($this->intl->t("Countries"), (new Country($this->diContainerHTTP))->getList($this->entity->countries, "contentLocation"));

    $this->entity->synopsis && $this->sectionAdd($this->intl->t("Synopsis"), $this->entity->synopsis);
    $this->sectionAdd(
      "Quote Test",
      "<blockquote>Quotes are rendered in the current locale…</blockquote><p>Meet the <q><code>&lt;q&gt;</code></q> tag.</p><blockquote lang='ja'>日本語はどうですか？</blockquote>" .
      "<blockquote lang='fr'>Nous avons également quelques citations de français. <q lang='en'>This even includes a nested quote in a different language!</q></blockquote>",
      false,
      "callout"
    );
    $this->sectionAdd($this->intl->t("Alternative Titles"), "Not implemented yet!", false, "callout callout-info");
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
