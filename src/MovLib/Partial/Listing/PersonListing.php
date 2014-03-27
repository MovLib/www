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

use \MovLib\Data\Person\Person;
use \MovLib\Partial\Alert;

/**
 * Listing for person instances.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PersonListing {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The dependency injection container.
   *
   * @var \MovLib\Core\DIContainer
   */
  protected $diContainer;

  /**
   * The active intl instance.
   *
   * @var \MovLib\Core\Intl
   */
  protected $intl;

  /**
   * The list items to display.
   *
   * @var mixed
   */
  protected $listItems;

  /**
   * The RDFa property to apply on every list item as HTML attribute.
   *
   * @var string
   */
  protected $listItemProperty;

  /**
   * The text to display if there are no items.
   *
   * @var mixed
   */
  protected $noItemsText;

  /**
   * The presenting presenter.
   *
   * @var \MovLib\Presentation\AbstractPresenter
   */
  protected $presenter;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new person listing.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainer
   *   The dependency injection container.
   * @param mixed $listItems
   *   The items to build the person listing.
   * @param string $listItemProperty [optional]
   *   The RDFa property to apply to every list item.
   * @param mixed $noItemsText [optional]
   *   The text to display if there are no items, defaults to a generic {@see \MovLib\Presentation\Partial\Alert}.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainer, $listItems, $listItemProperty = null, $noItemsText = null) {
    $this->diContainer = $diContainer;
    $this->intl        = $this->diContainer->intl;
    $this->presenter   = $this->diContainer->presenter;
    // @devStart
    // @codeCoverageIgnoreStart
    if (isset($listItemProperty) && (empty($listItemProperty) || !is_string($listItemProperty))) {
      throw new \InvalidArgumentException("\$listItemProperty must be a non-empty string when given");
    }
    if (isset($noItemsText) && (empty($noItemsText) || !method_exists($noItemsText, "__toString"))) {
      throw new \InvalidArgumentException(
        "\$noItemsText must be a non-empty string or convertable to string when given"
      );
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
    $this->listItems   = $listItems;
    $this->noItemsText = $noItemsText;
    if ($listItemProperty) {
      $this->listItemProperty = " property='{$listItemProperty}'";
    }
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
      /* @var $person \MovLib\Data\Person\FullPerson */
      while ($person = $this->listItems->fetch_object("\\MovLib\\Data\\Person\\FullPerson", [ $this->diContainer ])) {
        $list .= $this->formatListItem($person->initFetchObject());
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = new Alert(
          $this->intl->t(
            "We couldn’t find any persons matching your filter criteria, or there simply aren’t any persons available." .
            " Would you like to {0}create a new entry{1}?",
            [ "<a href='{$this->intl->r("/person/create")}'>", "</a>" ]
          ),
          $this->intl->t("No Persons"),
          Alert::SEVERITY_INFO
        );
      }

      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Person List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Format a person list item.
   *
   * @param \MovLib\Data\Person\FullPerson $person
   *   The person to format.
   * @param mixed $listItem [optional]
   *   The current list item if different from $person.
   * @return string
   *   The formatted person list item.
   */
  final protected function formatListItem($person, $listItem = null) {
    $bornName = null;
    if ($person->bornName) {
      $bornName = "<small>{$this->intl->t("{0} ({1})", [
        "<span property='additionalName'>{$person->bornName}</span>",
        "<i>{$this->intl->t("born name")}</i>",
      ])}</small>";
    }

    // @todo implement new image retrieval!
    return
      "<li{$this->listItemProperty} class='hover-item r' typeof='Person'>" .
        "<a class='no-link s s1 tac'><img alt='' height='60' src='{$this->presenter->getExternalURL(
          "asset://img/logo/vector.svg"
        )}' width='60'></a>" .
        "<div class='s s9'>" .
          "<a href='{$person->route}' property='url'><span property='name'>{$person->name}</span></a>{$bornName}" .
        "{$this->getAdditionalContent($person, $listItem)}</div>" .
      "</li>"
    ;
  }

  /**
   * Get additional content to display on a person list item.
   *
   * @param \MovLib\Data\Person\FullPerson $person
   *   The person providing the information.
   * @param mixed $listItem [optional]
   *   The current list item if different from $person.
   * @return string
   *   The formatted additional content.
   */
  protected function getAdditionalContent($person, $listItem) {
    // The default implementation returns no additional content.
  }

}
