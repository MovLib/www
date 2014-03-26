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

use \MovLib\Presentation\Partial\Alert;

/**
 * Base class for HTML lists.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractListing extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The list's attributes.
   *
   * @var array
   */
  public $attributes;

  /**
   * The list's closure method to call for each item.
   *
   * @var null|\Closure
   */
  public $closure;

  /**
   * The list's items.
   *
   * @var array
   */
  public $listItems;

  /**
   * The list's translated text if no items are present.
   *
   * @var string
   */
  public $noItemsText;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new list.
   *
   * @param mixed $listItems
   *   The list's items.
   * @param string $noItemsText [optional]
   *   The list's translated text if no items are present, defaults to no text.
   * @param array $attributes [optional]
   *   The list's attributes, defaults to no attributes.
   */
  public function __construct($listItems, $noItemsText = "", array $attributes = null) {
    $this->noItemsText = $noItemsText;
    $this->listItems   = $listItems;
    $this->attributes  = $attributes;
  }

  /**
   * Get the string representation of the list.
   *
   * @return string
   *   The string representation of the list.
   */
  public function __toString() {
    global $i18n;
    try {
      return $this->render();
    }
    catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", $i18n->t("Error Rendering List"), Alert::SEVERITY_ERROR);
    }
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get string representation of concrete list.
   *
   * @deprecated since version 0.0.1-dev
   * @return string
   *   String representation of concrete list.
   */
  protected function render() {
    // Default implementation does nothing.
    // Please migrate to using __toString() with proper @dev blocks.
  }

}
