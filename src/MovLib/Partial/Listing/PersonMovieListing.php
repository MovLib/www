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
namespace MovLib\Partial\Listing;

use \MovLib\Partial\Alert;

/**
 * Listing for all movies a person has participated in.
 *
 * @see \MovLib\Data\Person\FullPerson::getMovies()
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PersonMovieListing extends \MovLib\Partial\Listing\MovieListing {

  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct($diContainer, $listItems, $noItemsText = null) {
    if (isset($listItems) && $listItems !== (array) $listItems) {
      throw new \InvalidArgumentException("\$listItems must be an array");
    }
    parent::__construct($diContainer, $listItems, $noItemsText);
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  /**
   * @inheritdoc
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      if ($this->listItems) {
        $list = null;
        foreach ($this->listItems as $personMovie) {
          $list .= $this->formatListItem($personMovie->movie, $personMovie);
        }
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if ($this->noItemsText) {
        $this->noItemsText = new Alert(
          $this->intl->t("This person hasn’t worked on any movies yet."),
          $this->intl->t("No Movies"),
          Alert::SEVERITY_INFO
        );
      }

      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Movie List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Movie\FullMovie $movie
   *   {@inheritdoc}
   * @param \MovLib\Stub\Data\Person\PersonMovie $personMovie
   *   The list item containing all director, role and job information.
   */
  protected function getAdditionalContent($movie, $personMovie = null) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($movie instanceof \MovLib\Data\Movie\FullMovie)) {
      throw new \InvalidArgumentException("\$movie must be of type \\MovLib\\Data\\Movie\\FullMovie");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $jobs = null;

    if (isset($personMovie->director)) {
      $jobs .= "<li><a href='{$this->intl->r("/job/{0}", [ $personMovie->director->id ])}'>{$personMovie->director->title}</a></li>";
    }

    // Construct cast info.
    if (isset($personMovie->cast)) {
      $roles = null;
      foreach ($personMovie->roles as list($id, $name)) {
        if ($roles) {
          $roles .= ", ";
        }
        if ($id) {
          $roles .= "<a href='{$this->intl->r("/person/{0}", [ $id ])}'>{$name}</a>";
        }
        else {
          $roles .= $name;
        }
      }
      if ($roles) {
        $roles = " <em>{$this->intl->t("as")}</em> {$roles}";
      }
      $jobs .= "<li><a href='{$this->intl->r("/job/{0}", [ $personMovie->cast->id ])}'>{$personMovie->cast->title}</a>{$roles}</li>";
    }

    foreach ($personMovie->jobs as $id => $title) {
      $jobs .= "<li><a href='{$this->intl->r("/job/{0}", [ $id ])}'>{$title}</a></li>";
    }

    if ($jobs) {
      return "<br><ul class='no-list small'>{$jobs}</ul>";
    }
  }

}
