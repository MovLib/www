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

use \MovLib\View\HTML\AbstractFormView;

/**
 * Description of GalleryUploadView
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class GalleryUploadView extends AbstractFormView {

  /**
   * Initialize new image upload form view.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @param \MovLib\Presenter\GalleryUploadPresenter $presenter
   *   The upload presenter controlling this view.
   */
  public function __construct($presenter) {
    global $i18n;
    parent::__construct($presenter, $i18n->t("{0} {$presenter->galleryTitle} upload", [ $presenter->title ]));
    $this->stylesheets[] = "modules/gallery.css";
    $this->attributes["enctype"] = self::ENCTYPE_BINARY;
  }

  /**
   * Get the content of the image upload page.
   *
   * @global \MovLib\Model\I18nModel $i18n
   *   The global i18n model instance for translations.
   * @return string
   *   The page's contents as HTML.
   */
  public function getFormContent() {
    global $i18n;
    return
    "<div class='row'>" .
      "<aside class='span span--3'>" .
        $this->getSecondaryNavigation(
          $i18n->t("{0} gallery upload navigation", [ mb_convert_case($this->presenter->getAction(), MB_CASE_TITLE) ]),
          $this->presenter->getSecondaryNavigation()
        ) .
      "</aside>" .
      "<div class='span span--9'>" .
        "<p><label for='image'>{$i18n->t("{$this->presenter->galleryTitle}")}{$this->help($i18n->t(
          "Allowed image extensions: {0}<br>Maximum file size: {1,number}&thinsp;MB",
          [ implode(", ", array_values($this->presenter->model->imageSupported)), ini_get("upload_max_filesize") ]
        ))}</label>{$this->input("image", [
          "accept" => implode(",", array_keys($this->presenter->model->imageSupported)),
          "type"   => "file",
        ])}</p>" .
        "<p>{$this->submit($i18n->t("Upload"))}</p>" .
        "<pre>" . print_r($_POST, true) . "</pre>" .
      "</div>" .
    "</div>";
  }

}
