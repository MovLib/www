<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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
namespace MovLib\Presentation;

use \MovLib\Data\Image\Movie as MovieImage;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Lists;

/**
 * The listing for the latest movie additions.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class Movies extends \MovLib\Presentation\AbstractSecondaryNavigationPage {
  use \MovLib\Presentation\LatestAdditionsTrait;

  /**
   * Numeric array containing the <code>\MovLib\Data\Movie</code> objects to display.
   *
   * @var array
   */
  private $movies;

  /**
   * Instantiate new latest movies presentation
   *
   * @global \MovLib\Data\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->init($i18n->t("Movies"));
    $this->movies = (new \MovLib\Data\Movies())->getMoviesByCreated();
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    global $i18n;
    $alert = new Alert($i18n->t("No movies match your criteria."));
    $alert->severity = Alert::SEVERITY_INFO;
    $c = count($this->movies);
    $items = [];
    for ($i = 0; $i < $c; ++$i) {
      $title = $this->movies[$i]->getDisplayTitle();
      $titleSuffix = array_column($this->movies[$i]->getCountries(), "code");
      if (isset($this->movies[$i]->year)) {
        $titleSuffix[] = $this->movies[$i]->year;
      }
      $titleSuffix = (new Lists($titleSuffix, ""))->toCommaSeparatedList();
      if (!empty($titleSuffix)) {
        $titleSuffix = " ({$titleSuffix})";
      }
      $displayPoster = $this->movies[$i]->getDisplayPoster();
      $items[] = $this->a(
          $i18n->r("/movie/{0}", [ $this->movies[$i]->id ]),
          "<article>" .
            "<div class='latest-additions__image'>{$this->getImage($displayPoster, MovieImage::IMAGESTYLE_SPAN_1, [
              "alt" => $i18n->t("{0} movie poster{1}.", [ $title, isset($displayPoster->country)
                ? $i18n->t(" for {0}", [ $displayPoster->country["name"] ])
                : ""
              ]),
            ])}</div>" .
            "<div class='latest-additions__info clear-fix'>" .
              "<h2>{$title}{$titleSuffix}</h2>" .
              "<p>{$i18n->t("“{0}” (<em>original title</em>)", [ $this->movies[$i]->originalTitle ])}</p>" .
            "</div>" .
          "</article>",
          [ "tabindex" => $this->getTabindex() ]
        )
      ;
    }
    return (new Lists($items, $alert, [ "id" => "latest-additions" ]))->toHtmlList("ol");
  }

}