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
namespace MovLib\Presentation\Award;

use \MovLib\Data\Award\AwardSet;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Listing\AwardIndexListing;

/**
 * The latest Awards.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;
  use \MovLib\Partial\PaginationTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The award set.
   *
   * @var \MovLib\Data\AwardSet
   */
  protected $awardSet;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->awardSet = new AwardSet($this->diContainerHTTP);
    $this
      ->initPage($this->intl->t("Awards"))
      ->initBreadcrumb()
      ->initLanguageLinks("/awards", null, true)
      ->paginationInit($this->awardSet)
      ->sidebarInit([
        [ $this->request->path, $this->title, [ "class" => "ico ico-award" ] ],
        [ $this->intl->r("/award/random"), $this->intl->t("Random") ],
      ])
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore = "<a class='btn btn-large btn-success fr' href='{$this->intl->r("/award/create")}'>{$this->intl->t("Create New Award")}</a>";
    return new AwardIndexListing($this->diContainerHTTP, $this->awardSet, "`created` DESC", [ $this, "noItemsCallback" ]);
  }

  /**
   * Get the text to display if no awards matched the filter criteria for the listing.
   *
   * @return string
   *   The text to display if no awards matched the filter criteria for the listing.
   */
  public function noItemsCallback() {
    return new Alert(
      $this->intl->t("We couldn’t find any awards matching your filter criteria, or there simply aren’t any awards available."),
      $this->intl->t("No Awards"),
      Alert::SEVERITY_INFO
    ) .
    "<p>{$this->intl->t(
      "Would you like to {0}create an award{1}?",
      [ "<a href='{$this->intl->r("/award/create")}'>", "</a>" ]
    )}</p>";
  }

}
