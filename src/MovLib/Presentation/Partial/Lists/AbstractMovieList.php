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
namespace MovLib\Presentation\Partial\Lists;

/**
 * Base class for all movie listings.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractMovieList extends \MovLib\Presentation\Partial\Lists\AbstractList {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The span size for a single person's description.
   *
   * @var integer
   */
  protected $descriptionSpan;

  /**
   * The attributes of the list's items.
   *
   * @var array
   */
  public $listItemsAttributes;



  /**
   * The entity to perform the listing for.
   *
   * @var stdClass
   */
  protected $entity;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new abstract movie listing.
   *
   * @global \MovLib\Kernel $kernel
   * @param \mysqli_result $listItems
   *   The mysqli result containing the movies and job information.
   * @param \MovLib\Data\Person\Person $entity
   *   The person to perform the listing for.
   * @param string $noItemsText [optional]
   *   {@inheritdoc}
   * @param array $listItemsAttributes [optional]
   *   {@inheritdoc}
   * @param array $attributes [optional]
   *   {@inheritdoc}
   * @param integer $spanSize [optional]
   *   The span size the list items should reserve, defaults to <code>10</code>
   */
  public function __construct($listItems, $entity, $noItemsText = "", array $listItemsAttributes = null, array $attributes = null, $spanSize = 10) {
    global $kernel;
    parent::__construct($listItems, $noItemsText, $attributes);
    $this->entity = $entity;
    $kernel->stylesheets[] = "movie";
    $this->addClass("hover-list no-list r", $this->attributes);
    $this->listItemsAttributes = $listItemsAttributes;
    $this->addClass("r s s{$spanSize}", $this->listItemsAttributes);
    $this->descriptionSpan                 = --$spanSize;
    $this->listItemsAttributes[]           = "itemscope";
    $this->listItemsAttributes["itemtype"] = "http://schema.org/Movie";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Construct movie title information for display.
   *
   * @global \MovLib\Data\I18n $i18n
   * @param \MovLib\Data\Movie\Movie $movie
   *   The movie to display the title information for. Can also be <code>\\MovLib\\Data\\Movie\\FullMovie</code>.
   * @param array $attributes [optional]
   *   Additional attributes to apply to the wrapper.
   * @param string $wrap
   *   The enclosing tag.
   * @return string
   *   The formatted title information.
   * @throws \LogicException
   */
  protected function getTitleInfo($movie, $attributes = null, $wrap = "p") {
    global $i18n;
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($movie) || !isset($movie->displayTitle)) {
      throw new \LogicException("You have to pass a valid movie object to get title information!");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // We have to use different micro-data if display and original title differ.
    if ($movie->displayTitle != $movie->originalTitle) {
      $displayTitleItemprop = "alternateName";
      $originalTitle = "<br><span class='small'>{$i18n->t("{0} ({1})", [
        "<span itemprop='name'{$this->lang($movie->originalTitleLanguageCode)}>{$movie->originalTitle}</span>",
        "<i>{$i18n->t("original title")}</i>",
      ])}</span>";
    }
    // Simply clear the original title if it's the same as the display title.
    else {
      $displayTitleItemprop = "name";
      $originalTitle = null;
    }
    $displayTitle = "<span class='link-color' itemprop='{$displayTitleItemprop}'{$this->lang($movie->displayTitleLanguageCode)}>{$movie->displayTitle}</span>";

    // Append year enclosed in micro-data to display title if available.
    if (isset($movie->year)) {
      $displayTitle = $i18n->t("{title} ({year})", [ "title" => $displayTitle, "year" => "<span itemprop='datePublished'>{$movie->year}</span>" ]);
    }

    return "<{$wrap}{$this->expandTagAttributes($attributes)}>{$displayTitle}{$originalTitle}</{$wrap}>";
  }

  /**
   * Format a list item.
   *
   * @param \MovLib\Data\Movie\Full $movie
   *   The movie to display.
   * @param stdClass $entity
   *   The entity to display this listing for.
   */
  abstract protected function formatItem($movie, $entity);

  /**
   * @inheritdoc
   */
  protected function render() {

    $list   = null;
    /* @var $movie \MovLib\Data\Movie\FullMovie */
    while ($movie = $this->listItems->fetch_object("\\MovLib\\Data\\Movie\\FullMovie")) {
      $list .= $this->formatItem($movie, $this->entity);
    }

    if (!$list) {
      return $this->noItemsText;
    }

    // Put it all together and we're done.
    return "<ol{$this->expandTagAttributes($this->attributes)}>{$list}</ol>";
  }

}
