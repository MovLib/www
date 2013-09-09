<?php

/* !
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
namespace MovLib\View\HTML\Gallery;

/**
 * Gallery upload view for anonymous users (who are not allowed to upload images).
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryUploadAnonymousView extends AbstractPageView {

  /**
   * Initialize new image upload view for anonymous users.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @param \MovLib\Presenter\GalleryUploadPresenter $presenter
   *   The upload presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    parent::__construct($presenter, "{$presenter->title} {$presenter->galleryTitle} {$i18n->t("gallery")} {$i18n->t("upload")}");
    $this->stylesheets[] = "modules/gallery.css";
  }

  /**
   * {@inheritdoc}
   * @global \MovLib\Model\I18nModel $i18n
   *   The global I18n Model instance for translations.
   */
  public function getContent() {
    global $i18n;
    $severity = self::ALERT_SEVERITY_ERROR;
    return
    "<div class='container'>" .
      "<div class='row'>" .
        "<aside class='span span--3'>" .
          $this->getSecondaryNavigation(
            $i18n->t("{0} gallery upload navigation", [ mb_convert_case($this->presenter->getAction(), MB_CASE_TITLE) ]),
            $this->presenter->getSecondaryNavigation()
          ) .
        "</aside>" .
        "<div class='span span--9'>" .
          "<div class='alert alert--{$severity}' role='alert'>" .
            "<p><b>" . $i18n->t("The image upload feature is only available for registered users.") . "</b></p>" .
            $i18n->t("Please {0} or {1} a new account to use all the features of MovLib. It's completely free and anonymous (if you want).",
              [ $this->a($i18n->r("/user/login"), "log in"), $this->a("/user/register", "register") ]) .
          "</div>" .
        "</div>" .
      "</div>" .
    "</div>";
  }

}
