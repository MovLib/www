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
namespace MovLib\Presentation\User;

use \MovLib\Component\Date;
use \MovLib\Data\Movie\Movie;
use \MovLib\Data\Series\Series;
use \MovLib\Partial\Date as DatePartial;
use \MovLib\Partial\Country;
use \MovLib\Partial\Helper\MovieHelper;
use \MovLib\Partial\Helper\SeriesHelper;
use \MovLib\Partial\Time;

/**
 * Public user profile presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\User\AbstractUserPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Show";
  // @codingStandardsIgnoreEnd

  /**
   * Instantiate new user presentation.
   */
  public function init() {
    return $this->initPage(null);
  }

  /**
   * {@inheritdoc}
   */
  public function getContent(){
    // http://schema.org/Person
    $this->schemaType = "Person";

    if ($this->entity->realName && !$this->entity->private) {
      $this->headingSchemaProperty = "additionalName";
    }
    else {
      $this->headingSchemaProperty = "name";
    }

    // Wrap the complete header content in a row and the heading itself in a span.
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    // Create user info.
    $personalData = $website = null;

    if (!$this->entity->private) {
      // Format the user's birthday if available.
      if ($this->entity->birthdate) {
        $age = (new DatePartial($this->intl, $this))->getAge($this->entity->birthdate);
        $personalData[] = "<time datetime='{$this->entity->birthdate}' property='birthDate'>{$age}</time>";
      }
      if ($this->entity->sex > 0) {
        $gender     = $this->entity->sex === 1 ? $this->intl->t("Male") : $this->intl->t("Female");
        $personalData[] = "<span property='gender'>{$gender}</span>";
      }
      if ($this->entity->countryCode) {
        $country        = (new Country($this->container))->format($this->entity->countryCode);
        $personalData[] = "<span property='nationality'>{$country}</span>";
      }

      // Link the user's real name to the website if we have both properties.
      if ($this->entity->realName) {
        array_unshift($personalData, "<span property='name'>{$this->entity->realName}</span>");
      }
    }
    if ($this->entity->website) {
      $hostname = parse_url($this->entity->website, PHP_URL_HOST);
      $website  = "<br><a href='{$this->entity->website}' property='url' rel='nofollow' target='_blank'>{$hostname}</a>";
    }

    if ($personalData) {
      $personalData = implode(", ", $personalData);
      $personalData = "<p>{$personalData}{$website}</p>";
    }

    // Display additional info about this user after the name and the avatar to the right of it.
    $this->headingAfter =
        $personalData .
        "<small>{$this->intl->t("Joined {date} and was last seen {time}.", [
          "date" => (new DatePartial($this->intl, $this->container->presenter))->format(new Date($this->entity->created->format('Y-m-d'))),
          "time" => (new Time($this->intl, $this->entity->access))->formatRelative(),
        ])}</small>" .
      "</div>" .
      $this->img($this->entity->imageGetStyle(), [ "class" => "s s2", "property" => "image" ], false) .
    "</div>";

    $this->entity->aboutMe && $this->sectionAdd($this->intl->t("About"), $this->entity->aboutMe);


    // ----------------------------------------------------------------------------------------------------------------- Rating Stream


    $ratingStream = null;
    $ratedEntities = $this->entity->loadRatedEntities();
    if (empty($ratedEntities)) {
      if ($this->session->userId === $this->entity->id) {
        $ratingStream = $this->intl->t(
          "You haven’t rated a single movie or series yet, use the {0}search{1} to explore movies or series you already know.",
          [ "<a href='{$this->intl->r("/search")}'>", "</a>" ]
        );
      }
      else {
        $ratingStream = $this->intl->t(
          "{username} hasn’t rated a single movie or series yet, that makes us a sad panda.",
          [ "username" => $this->entity->name ]
        );
      }
    }
    else {
      $movieHelper  = new MovieHelper($this->container);
      $seriesHelper = new SeriesHelper($this->container);

      $ratingStream = "<ol class='hover-list no-list'>";
      $c = count($ratedEntities);
      for ($i = 0; $i < $c; ++$i) {
        if ($ratedEntities[$i]->entity instanceof Movie) {
          $ratingStream .=
            "<li class='hover-item r'>" .
              "<article typeof='Movie'>" .
                "<div class='s s1' property='image'>{$this->img($ratedEntities[$i]->entity->imageGetStyle("s1"))}</div>" .
                "<div class='s s8'>" .
                  "<h2 class='para'>{$movieHelper->getStructuredDisplayTitle($ratedEntities[$i]->entity)}</h2>" .
                  $movieHelper->getStructuredOriginalTitle($ratedEntities[$i]->entity, "small") .
                  $this->intl->t("Rated: {0}", [ (new Time($this->intl, $ratedEntities[$i]->created))->formatRelative() ]) .
                "</div>" .
                "<div class='s s1 rating-mean tac'>{$this->intl->format("{0,number}", $ratedEntities[$i]->rating)}</div>" .
              "</article>" .
            "</li>"
          ;
        }
        elseif ($ratedEntities[$i]->entity instanceof Series) {
          $ratingStream .=
            "<li class='hover-item r'>" .
              "<article>" .
                "<div class='s s1' property='image'></div>" .
                "<div class='s s8'>" .
                  "<h2 class='para'>{$seriesHelper->getStructuredDisplayTitle($ratedEntities[$i]->entity)}</h2>" .
                  $seriesHelper->getStructuredOriginalTitle($ratedEntities[$i]->entity, "small") .
                  $this->intl->t("Rated: {0}", [ (new Time($this->intl, $ratedEntities[$i]->created))->formatRelative() ]) .
                "</div>" .
                "<div class='s s1 rating-mean tac'>{$this->intl->format("{0,number}", $ratedEntities[$i]->rating)}</div>" .
              "</article>" .
            "</li>"
          ;
        }
      }
      $ratingStream .= "</ol>";
    }

    $this->sectionAdd($this->intl->t("Recently Rated"), isset($ratingStream)? $ratingStream : $noRatingsText);
    return $this->sections;
  }

}