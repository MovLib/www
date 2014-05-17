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

/**
 * Base class for HTML lists.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The dependency injection container that has to be passed to each data class.
   *
   * @var \MovLib\Core\HTTP\DIContainerHTTP
   */
  protected $diContainerHTTP;

  /**
   * The listing's items.
   *
   * @var array
   */
  protected $items;

  /**
   * The listing's no items callback.
   *
   * @var callable
   */
  protected $noItemsCallback;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new generic array listing, the listing uses a <code>foreach</code> to iterate over the items and can
   * handle any kind of (native) array.
   *
   * @param \MovLib\Core\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param array $items
   *   The items to list.
   * @param callable $noItemsCallback
   *   The callback to call if there were no items.
   */
  public function __construct(\MovLib\Core\DIContainerHTTP $diContainerHTTP, array $items, callable $noItemsCallback) {
    $this->diContainerHTTP = $diContainerHTTP;
    $this->items           = $items;
    $this->noItemsCallback = $noItemsCallback;
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd

      if (empty($this->items)) {
        return $this->noItemsCallback;
      }

      $items = null;
      foreach ($this->items as $delta => $item) {
        $items .= $this->formatItem($item, $delta);
      }
      return $this->getListing($items);

    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
