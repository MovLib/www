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
namespace MovLib\Presentation\Partial\Listing;

use \MovLib\Presentation\Partial\Alert;

/**
 * List for movie instances with jobs.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class CompanyMovieListing extends \MovLib\Presentation\Partial\Listing\MovieListing {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * @inheritdoc
   */
  public function __toString() {
    global $i18n;

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
          $i18n->t("No movies match your search criteria."),
          $i18n->t("No Movies"),
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
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getAdditionalContent($movie, $listItem) {
    global $i18n;

    // @devStart
    // @codeCoverageIgnoreStart
    if (empty($movie->jobIds)) {
      throw new \LogicException($i18n->t("\$movie->jobIds can not be empty!"));
    }

    if (empty($movie->jobTitles)) {
      throw new \LogicException($i18n->t("\$movie->jobTitles can not be empty!"));
    }
    if (count($movie->jobTitles) != count($movie->jobIds)) {
      throw new \LogicException($i18n->t("The count of \$movie->jobTitles and \$movie->jobIds has to be equal!"));
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    $jobsDone = null;
    $c = count($movie->jobTitles);
    for ($i = 0; $i < $c; ++$i) {
      $jobsDone .=
        "<a class='label' href='{$i18n->r("/job/{0}", [ $movie->jobIds[$i] ])}'>{$movie->jobTitles[$i]}</a>"
      ;
    }

    return "<span class='small'>{$jobsDone}</span>";
  }

}
