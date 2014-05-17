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
 * Defines the base class for listings that consume MySQLi results.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMySQLiResultListing implements \MovLib\Partial\Listing\ListingInterface {


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
   * @var \mysqli_result
   */
  protected $items;

  /**
   * The <code>ORDER BY</code> clause for the query.
   *
   * @var string
   */
  protected $orderBy;

  /**
   * The set of which the items should be listed.
   *
   * @var \MovLib\Data\SetInterface
   */
  protected $set;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new listing that consumes MySQLi results.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param \MovLib\Data\SetInterface $set
   *   The set of which the items should be listed.
   * @param string $orderBy
   *   The <code>ORDER BY</code> clause for the query.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, \MovLib\Data\SetInterface $set, $orderBy) {
    // @devStart
    // @codeCoverageIgnoreStart
    // No way we can check for a trait and traits can't implement interfaces ... :(
    assert(
      method_exists($diContainerHTTP->presenter, "getNoItemsContent"),
      "You need to use the PaginationTrait in your class to use a listing."
    );
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->diContainerHTTP = $diContainerHTTP;
    $this->orderBy         = $orderBy;
    $this->set             = $set;
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

      $items = null;
      $result = $this->set->loadOrdered($this->orderBy, $this->diContainerHTTP->presenter->paginationOffset, $this->diContainerHTTP->presenter->paginationLimit);
      while ($item = $result->fetch_object($this->set->getEntityClassName(), [ $this->diContainerHTTP ])) {
        $items .= $this->formatItem($item);
      }
      $result->free();
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
