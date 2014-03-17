<?php

/* !
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

use \MovLib\Data\Movie\FullMovie;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\InputSex;

/**
 * List all movies a person has participated in.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonMovieListing extends \MovLib\Presentation\Partial\Listing\MovieListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The person this listing is for.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new movies listing for a person.
   *
   * @param array $listItems
   *   The movies to display.
   * @param integer $person
   *   The person this listing is for.
   */
  public function __construct(array $listItems, $person) {
    parent::__construct($listItems);
    $this->person = $person;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      if ($this->listItems) {
        $list = null;
        foreach ($this->listItems as $movieArray) {
          $list .= $this->formatListItem($movieArray["#object"]);
        }
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      return (string) new Alert(
        $i18n->t("Seems like {person_name} hasn’t worked on any movies.", [ "person_name" => $this->person->name ]),
        null,
        Alert::SEVERITY_INFO
      );
    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Movie List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

  /**
   * @inheritdoc
   */
  protected function getAdditionalContent($movie) {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!($movie instanceof \MovLib\Data\Movie\FullMovie)) {
      throw new \InvalidArgumentException("\$movie must be of type \\MovLib\\Data\\Movie\\FullMovie");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Prepare jobs list.
    $movieJobs = null;
    // If the person has directed the movie, directly add translated and gendered director job.
    if (isset($this->listItems[$movie->id]["director_job_id"])) {
      $movieJobs .= "<li><a href='{$i18n->r(
        "/job/{0}",
        [ $this->listItems[$movie->id]["director_job_id"] ]
      )}'>{$this->listItems[$movie->id]["director_job_title"]}</a></li>";
    }

    // Construct cast info.
    $roles = null;
    if (isset($this->listItems[$movie->id]["roles"])) {
      $roleSelf = null;
      switch ($this->person->sex) {
        case InputSex::MALE:
          $roleSelf = $i18n->t("Himself");
          break;

        case InputSex::FEMALE:
          $roleSelf = $i18n->t("Herself");
          break;

        default:
          $roleSelf = $i18n->t("Self");
          break;
      }
      foreach ($this->listItems[$movie->id]["roles"] as $roleId => $roleName) {
        $role = null;
        if ($roles) {
          $role .= ", ";
        }

        // Role with no person associated.
        if ($roleName === FullMovie::ROLE_UNDEFINED) {
          $role .= $roleId;
        }
        // This person plays another person.
        else {
          // The person plays himself/herself, replace the role name by the correct gendered translation of "Self".
          if ($roleId === $this->person->id) {
            $roleName = $roleSelf;
          }
          $role .= "<a href='{$i18n->r("/person/{0}", [ $roleId ])}'>{$roleName}</a>";
        }

        $roles .= $role;
      }
    }
    if ($roles) {
      $movieJobs .= "<li><a href='{$i18n->r(
        "/job/{0}",
        [ $this->listItems[$movie->id]["cast_job_id"] ]
      )}'>{$this->listItems[$movie->id]["cast_job_title"]}</a> <em>{$i18n->t("as")}</em> {$roles}</li>";
    }

    // Construct crew info.
    if (isset($this->listItems[$movie->id]["jobs"])) {
      foreach ($this->listItems[$movie->id]["jobs"] as $jobId => $jobName) {
        $movieJobs .= "<li><a href='{$i18n->r("/job/{0}", [ $jobId ])}'>{$jobName}</a></li>";
      }
    }

    if ($movieJobs) {
      $movieJobs = "<ul class='no-list'>{$movieJobs}</ul>";
    }

    return $movieJobs;
  }

}
