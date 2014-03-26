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
 * Images list for entities.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class EntityListing extends \MovLib\Presentation\AbstractBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Absolute class name of the entitiy to fetch.
   *
   * @var string
   */
  protected $entity;

  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;

  /**
   * The text to display if there are no items.
   *
   * @var mixed
   */
  protected $noItemsText;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new entity listing.
   *
   * @param mixed $listItems
   *   The items to build the entity listing.
   * @param mixed $noItemsText [optional]
   *   The text to display if there are no items, defaults to a generic {@see \MovLib\Presentation\Partial\Alert}.
   * @param string $entityName
   *   The name of the Data object to fetch (e.g. <code>"Genre"</code> which will lead to instantiation of
   *   <code>"\\MovLib\\Data\\Genre"</code>).
   */
  public function __construct($listItems, $noItemsText, $entityName) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($entityName)) {
      throw new \InvalidArgumentException("{$entityName} cannot be empty");
    }
    if (class_exists("\\MovLib\\Data\\{$entityName}") === false) {
      throw new \InvalidArgumentException("\\MovLib\\Data\\{$entityName} must match an existing class");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->listItems   = $listItems;
    $this->noItemsText = $noItemsText;
    $this->entity      = "\\MovLib\\Data\\{$entityName}";
  }

  /**
   * Get the string representation of the listing.
   *
   * @return string
   *   The string representation of the listing.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      while ($entity = $this->listItems->fetch_object($this->entity)) {
        $list .= $this->formatListItem($entity);
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      return (string) $this->noItemsText;

    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Entity List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format list item.
   *
   * @param mixed $entity
   *   The entity to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $entity.
   * @return string
   *   The formatted entity list item.
   */
  final protected function formatListItem($entity, $listItem = null) {
    return
      "<li class='hover-item r' typeof='Corporation'>" .
        "<div class='s s10'>" .
          "<a href='{$entity->route}' property='url'><span property='name'>{$entity->name}</span></a>" .
          $this->getAdditionalContent($entity, $listItem) .
        "</div>" .
      "</li>"
    ;
  }

  /**
   * Get additional content to display on a list item.
   *
   * @param mixed $entity
   *   The entity providing the information.
   * @return string
   *   The formatted additional content.
   */
  protected function getAdditionalContent($entity, $listItem) {
    // The default implementation returns no additional content.
  }

}
