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

use \MovLib\Data\Help\CategorySet;
use \MovLib\Partial\Alert;

/**
 * Defines the help category index presentation.
 *
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/help
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/help
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/help
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/help
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Index extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\PaginationTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->set = new CategorySet($this->diContainerHTTP);
    $this->initPage("Help");
    $this->initBreadcrumb();
    $this->initLanguageLinks("/help");
    $this->stylesheets[] = "help";
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $items = null;
    foreach ($this->set->loadOrdered("`created` DESC", $this->paginationOffset, $this->paginationLimit) as $id => $entity) {
      $items .= $this->formatListingItem($entity, $id);
    }
    return
      "<div class='c'>" .
        "<div class='r mb40'>" .
          $items .
        "</div>" .
        "<div class='big mb40 well tac'>{$this->intl->t(
          "Still can’t find what you’re looking for? {0}Visit the Forum{1}",
          [ "<a href='{$this->intl->r("/forum")}'>", "</a>" ]
        )}</div>" .
        "<div class='r'>" .
          "<div class='s s6 tac well'>" .
            "<p class='big'>{$this->intl->t("Building something cool? Check out our API!")}</p>" .
            "<a class='ico ico-api btn btn-primary btn-medium' href='{$this->intl->r("/api")}'> " .
              $this->intl->t("API Documentation") .
            "</a>" .
          "</div>" .
          "<div class='s s6 tac well'>" .
            "<p class='big'>{$this->intl->t("Got a really tough question? Our staff can help!")}</p>" .
            "<a class='ico ico-email btn btn-success btn-medium' href='{$this->intl->r("/contact")}'> " .
              $this->intl->t("New Support Request") .
            "</a>" .
          "</div>" .
        "</div>" .
      "</div>"
    ;
  }

  /**
   * {@inheritdoc}
   * @param \MovLib\Data\Help\Category\Category $category {@inheritdoc}
   */
  protected function formatListingItem(\MovLib\Data\AbstractEntity $category, $delta) {
    return
      "<div class='s s4'>" .
        "<h2 class='ico {$category->icon} tac'> {$category->title}</h2>" .
        "<p>{$this->htmlDecode($category->description)}</p>" .
        "<p class='tac'>" .
          "<a class='btn btn-info btn-large' href='{$category->route}'>" .
            $this->intl->t("{0} Help", [ $category->title ]) .
          "</a>" .
        "</p>" .
      "</div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getNoItemsContent() {
    return new Alert(
      "<p>{$this->intl->t("We couldn’t find any help categories matching your filter criteria, or there simply aren’t any help categories available.")}</p>",
      $this->intl->t("No Help Categories")
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getMainContent($content) {
    $noscript = new Alert(
      $this->intl->t("Please activate JavaScript in your browser to experience our website with all its features."),
      $this->intl->t("JavaScript Disabled")
    );

    return
      "<main id='m' role='main'>" .
        "<div id='banner'>" .
          "<h1 class='ico ico-help c mb20'> {$this->intl->t("Help, Please!")}</h1>" .
          "<p class='c mb20'>{$this->intl->t("Do you like movies? Great, so do we! All the info you need {0} for finding, buying, selling, and cataloging movies is right here.", [ "<br>" ])}</p>" .
          "<form action='{$this->intl->r("/help/search")}' class='c' method='get'>" .
            "<div class='s6' id='help-search'>" .
              "<input name='q' class='s big' required tabindex='3' title='{$this->intl->t(
                "Enter the search term you wish to search for in our help and hit enter."
              )}' type='search' placeholder='{$this->intl->t("I need help with..")}'>" .
              "<button class='ico ico-search s' tabindex='4' type='submit'><span class='vh'>{$this->intl->t(
                "Start searching in our help for the entered keyword."
              )}</span></button>" .
            "</div>" .
          "</form>" .
        "</div>" .
        "<noscript>{$noscript}</noscript>{$this->alerts}{$content}" .
      "</main>"
    ;
  }
}
