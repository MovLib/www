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
namespace MovLib\Presentation\Movie\Poster;

use \MovLib\Data\Movie\PosterSet;

/**
 * Defines the movie poster index presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractIndexPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Index";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initIndex(
      new PosterSet($this->container, $_SERVER["MOVIE_ID"]),
      $this->intl->t("Posters"),
      $this->intl->t("Upload New Poster")
    );
    $this->breadcrumb->addCrumb($this->set->route, $this->intl->t("Movies"));
    $this->breadcrumb->addCrumb($this->intl->r("/movie/{0}", $_SERVER["MOVIE_ID"]), $this->intl->t("Movie"));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $poster, $delta) {
    /* @var $poster \MovLib\Data\Movie\Poster */
    $return =
      "<li class='mb20 s s2 tac'>" .
        "<a class='no-link' href='{$poster->route}' typeof='ImageObject'>" .
          $this->img($poster->imageGetStyle(), [ "property" => "thumbnail" ], false) .
          $this->intl->t("{width} × {height}", [
            "width"  => "<span property='width'>{$poster->imageWidth}<span class='vh'> px</span></span>",
            "height" => "<span property='height'>{$poster->imageHeight}<span class='vh'> px</span></span>",
          ]) .
        "</a>" .
      "</li>"
    ;
    $styles = new \ReflectionProperty($poster, "imageStyles");
    $styles->setAccessible(true);
    $styles = $styles->getValue($poster);
    if (!is_array($styles)) {
      $styles = unserialize($styles);
    }
    ob_start();
    print_r($styles);
    $styles = ob_get_clean();
    return "{$return}<li class='mb20 s s8 tal'><pre>{$styles}</pre></li>";
  }

  /**
   * Get the listing.
   *
   * @param string $items
   *   The formatted listing's items.
   * @return string
   *   The listing.
   */
  protected function getListing($items) {
    return "<ol class='grid-list no-list r'>{$items}</ol>";
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return $this->calloutInfo("<p>{$this->intl->t("We couldn’t find any posters for this movie.")}</p>", $this->intl->t("No Posters"));
  }

}
