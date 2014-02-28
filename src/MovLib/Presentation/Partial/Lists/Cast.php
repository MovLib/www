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
namespace MovLib\Presentation\Partial\Lists;

use \MovLib\Data\Image\PersonImage;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Lists\Unordered;

/**
 * Special images list for cast.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Cast extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The class to use for fetch_object().
   *
   * @var stdClass
   */
  public $castClass = "\\MovLib\\Data\\Movie\\Cast";

  /**
   * The span size for a single person's description.
   *
   * @var integer
   */
  protected $descriptionSpan;

  /**
   * The person photo's style.
   *
   * @var integer
   */
  public $imageStyle = PersonImage::STYLE_SPAN_01;

  /**
   * The attributes of the list's items.
   *
   * @var array
   */
  public $listItemsAttributes;

  /**
   * The span size for the roles listing.
   *
   * @var integer
   */
  protected $roleSpan;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   * @param \mysqli_result $listItems
   *   The mysqli result object containing the cast.
   * @param string $noItemsText
   *   {@inheritdoc}
   * @param array $listItemsAttributes
   *   {@inheritdoc}
   * @param array $attributes
   *   {@inheritdoc}
   * @param integer $spanSize [optional]
   *   {@inheritdoc}
   */
  public function __construct($listItems, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 10) {
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->addClass("hover-list no-list", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("li s r", $this->listItemsAttributes);
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Person";
    $spanHalf = ($spanSize - 1) / 2;
    $this->descriptionSpan = ceil($spanHalf);
    $this->roleSpan = $spanSize - $this->descriptionSpan - 1;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


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
      if (empty($info["roles"])) {
        $descriptionSpan = $this->descriptionSpan + $this->roleSpan;
      }
      else {
        $descriptionSpan = $this->descriptionSpan;
      }
      $info["roles"] = new Unordered($info["roles"], "", [ "class" => "no-list jobs s s{$this->roleSpan} tar"]);
      $list .=
        "<li{$this->expandTagAttributes($this->listItemsAttributes)}>" .
          $this->a(
            $info["person"]->route,
            "<div class='s s1 tac'>" .
            $this->getImage($info["person"]->displayPhoto->getStyle($this->imageStyle), false, [ "itemprop" => "image" ]) .
            "</div><div class='link-color s s{$descriptionSpan}' itemprop='name'><p>{$info["person"]->name}</p></div>",
            [ "class" => "img fl", "itemprop" => "url" ]
          ) .
        "{$info["roles"]}</li>"
      ;
    }
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
