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
namespace MovLib\Presentation\Help;

/**
 * Present search results to the user.
 *
 * @route /help/search
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Search extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Search";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user's submitted search query.
   *
   * @var null|string
   */
  protected $query;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new search results presentation.
   */
  public function init() {
    $this->initPage($this->intl->t("Help Search"));
    $this->initBreadcrumb([
      [ $this->intl->r("/help"), $this->intl->t("Help") ]
    ]);
    $this->breadcrumb->ignoreQuery = true;
    $this->query = filter_input(INPUT_GET, "q", FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_NULL_ON_FAILURE);

    $this->initLanguageLinks("/help/search", null, false, [ "q" => rawurlencode($this->query) ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Get the page's content.
   *
   * @return mixed
   *   The page's content.
   */
  public function getContent() {
    // We're done if we have no search query.
    if (empty($this->query)) {
      $this->alertError(
        $this->intl->t("No search query submitted."),
        $this->intl->t("Nothing to Search for…")
      );
      return;
    }

    return
      "<div class='c'>" .
        $this->checkBackLater("Search in Help") .
        $this->intl->t("You searched for: {0}", [ $this->query ]) .
      "</div>"
    ;
  }

}
