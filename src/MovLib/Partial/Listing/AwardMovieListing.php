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
 * List for movie instances that have won or were nominatet for an award.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class AwardMovieListing extends \MovLib\Partial\Listing\MovieListing {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $list = null;
      foreach ($this->listItems as $movie) {
        $list .= $this->formatListItem($movie->movie);
      }

      if ($list) {
        return "<ol class='hover-list no-list'>{$list}</ol>";
      }

      if (!$this->noItemsText) {
        $this->noItemsText = (string) new Alert(
          $this->intl->t("No movies match your search criteria."),
          $this->intl->t("No Movies"),
          Alert::SEVERITY_INFO
        );
      }

      return (string) $this->noItemsText;
    // @devStart
    // @codeCoverageIgnoreStart
    } catch (\Exception $e) {
      return (string) new Alert("<pre>{$e}</pre>", "Error Rendering Movie List", Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getAdditionalContent($movie, $listItem) {
    // @devStart
    // @codeCoverageIgnoreStart
    if (!isset($movie->wonCount)) {
      throw new \LogicException($this->intl->t("\$movie->wonCount has to be set!"));
    }
    if (!isset($movie->nominationCount)) {
      throw new \LogicException($this->intl->t("\$movie->nominationCount has to be set!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    return
      "<span class='label small'>{$this->intl->t("{0}x won", [ $movie->wonCount ])}</span>" .
      "<span class='label small'>{$this->intl->t("{0}x nominated", [ $movie->nominationCount ])}</span>"
    ;
  }

}
