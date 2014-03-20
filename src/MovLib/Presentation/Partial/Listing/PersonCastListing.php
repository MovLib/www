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

use \MovLib\Presentation\Partial\Alert;

/**
 * Images list for cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class PersonCastListing extends \MovLib\Presentation\Partial\Listing\PersonListing {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  // @devStart
  // @codeCoverageIgnoreStart
  public function __construct($listItems, $listItemProperty = null, $noItemsText = null) {
    if (isset($listItems) && $listItems !== (array) $listItems) {
      throw new \InvalidArgumentException("\$listItems must be an array");
    }
    parent::__construct($listItems, $listItemProperty, $noItemsText);
  }
  // @codeCoverageIgnoreEnd
  // @devEnd

  public function __toString() {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      if ($this->listItems) {
        $list = null;
        /* @var $moviePerson \MovLib\Stub\Data\Movie\MoviePerson */
        foreach ($this->listItems as $moviePerson) {
          $list .= $this->formatListItem($moviePerson->person, $moviePerson);
        }
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = new Alert(
          $i18n->t("No cast assigned yet, please edit this page to provide this information."),
          null,
          Alert::SEVERITY_INFO
        );
      }

      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Movie Cast List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get additional content to display on a person list item.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Person\FullPerson $person
   *   {@inheritdoc}
   * @param \MovLib\Stub\Data\Movie\MoviePerson $moviePerson [optional]
   *   The list item containing all role information.
   * @return string
   *   {@inheritdoc}
   */
  protected function getAdditionalContent($person, $moviePerson = null) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if (!($person instanceof \MovLib\Data\Person\Person)) {
      throw new \InvalidArgumentException("\$person must be of type \\MovLib\\Data\\Person\\Person");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $roles = null;
    /* @var $role \MovLib\Stub\Data\Person\PersonRole */
    foreach ($moviePerson->roles as $role) {
      if ($roles) {
        $roles .= ", ";
      }

      if ($role->id) {
        $roles .= "<a href='{$i18n->r("/person/{0}", [ $role->id ])}'>{$role->name}</a>";
      }
      else {
        $roles .= $role->name;
      }
    }

    if ($roles) {
      return "<small><em>{$i18n->t("as")}</em> {$roles}</small>";
    }
  }

}
