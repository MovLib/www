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
 * Special images list for cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonCastListing extends \MovLib\Presentation\Partial\Listing\PersonListing {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


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

      if ($this->noItemsText) {
        return (string) $this->noItemsText;
      }
      return new Alert(
        $i18n->t("No cast assigned yet, please edit this page to provide this information."),
        null,
        Alert::SEVERITY_INFO
      );
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
    if(!($person instanceof \MovLib\Data\Person\Person)) {
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
  /**
   * @inheritdoc
   */
  protected function render() {
    global $i18n;
    $personRoles = [];
    $roleHimself      = $i18n->t("Himself");
    $roleHerself      = $i18n->t("Herself");
    $roleSelf         = $i18n->t("Self");
    /* @var $cast \MovLib\Data\Movie\Cast */
    while ($cast = $this->listItems->fetch_object($this->castClass)) {
      // Initialize the offset in the person role array.
      if (!isset($personRoles[$cast->personId])) {
        $personRoles[$cast->personId]["person"] = $cast->getPerson();
        $personRoles[$cast->personId]["roles"]  = [];
      }

      // Add the role to the person's information in the role array.
      // Simple role with just a name.
      if ($cast->roleName) {
        $personRoles[$cast->personId]["roles"][] = $cast->roleName;
      }
      // Persons playing themselves.
      elseif ($cast->role === true) {
        if ($personRoles[$cast->personId]["person"]->sex === 1) {
          $role = $roleHimself;
        }
        elseif ($personRoles[$cast->personId]["person"] === 2) {
          $role = $roleHerself;
        }
        else {
          $role = $roleSelf;
        }
        $personRoles[$cast->personId]["roles"][] = $this->a($personRoles[$cast->personId]["person"]->route, $role);
      }
      // Role with own person page.
      elseif (isset ($cast->role)) {
        $personRoles[$cast->personId]["roles"][] = $this->a($cast->role->route, $cast->role->name);
      }
    }

    // No cast was found, return descriptive text.
    if (empty($personRoles)) {
      return (string) new Alert($this->noItemsText, null, Alert::SEVERITY_INFO);
    }

    // Construct the cast list.
    $list = null;
    foreach ($personRoles as $id => $info) {
      $roles = null;
      if (!empty($info["roles"])) {
        $roles = implode(", ", $info["roles"]);
        $roles = "<small>{$i18n->t(
          "{begin_emphasize}as{end_emphasize} {roles}",
          [ "roles" => $roles, "begin_emphasize" => "<em>", "end_emphasize" => "</em>" ]
        )}</small>";
      }

      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          $this->getImage($info["person"]->getStyle($this->imageStyle), $info["person"]->route, [ "property" => "image" ], [ "class" => "s s1 tac" ]) .
          "<div class='s s{$this->descriptionSpan}'>" .
            "<p><a href='{$info["person"]->route}' property='name url'>{$info["person"]->name}</a></p>{$roles}" .
          "</div>" .
        "</li>"
      ;
    }
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
