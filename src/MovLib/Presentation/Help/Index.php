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
 * The main help page.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Index extends \MovLib\Presentation\Page {


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new help presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;

    $this->initPage($i18n->t("Help"));
    $this->initLanguageLinks("/help");
    $this->initBreadcrumb();

    $kernel->stylesheets[] = "help";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getContent() {
    global $i18n;

    $content =

      "<div class='c'>" .
      "<div class='r'>" .
        "<article class='s s4 taj'>" .
          "<h2 class='ico ico-movie tac'> {$i18n->t("Database")}</h2>" .
          "<p>{$i18n->t(
            "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore."
          )}</p>" .
          "<p class='tac'><a class='btn btn-success btn-large' href=''>{$i18n->t("Database Help")}</a></p>" .
        "</article>" .
        "<article class='s s4 taj'>" .
          "<h2 class='ico ico-company tac'> {$i18n->t("Marketplace")}</h2>" .
          "<p>{$i18n->t(
            "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore."
          )}</p>" .
          "<p class='tac'><a class='btn btn-success btn-large' href=''>{$i18n->t("Marketplace Help")}</a></p>" .
        "</article>" .
        "<article class='s s4 taj'>" .
          "<h2 class='ico ico-person tac'> {$i18n->t("Community")}</h2>" .
          "<p>{$i18n->t(
            "Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore."
          )}</p>" .
          "<p class='tac'><a class='btn btn-success btn-large' href=''>{$i18n->t("Community Help")}</a></p>" .
        "</article>" .
      "</div>" .
      "</div>"

    ;
    return $content;
  }

}
