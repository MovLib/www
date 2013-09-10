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
namespace MovLib\Presenter\Gallery;

use \IntlDateFormatter;
use \MovLib\Model\AbstractImageModel;
use \MovLib\View\HTML\Alert;
use \MovLib\View\HTML\Gallery\GalleryImageView;

/**
 * Description of TraitImagePresenter
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
trait TraitGalleryImagePresenter {

  /**
   * The model of the currently displayed movie image (e.g. poster, lobby card, ...).
   *
   * @var \MovLib\Model\AbstractImageModel
   */
  public $imageModel;

  /**
   * Title of this image (e.g. <code>"Photo X of Y"</code>).
   *
   * @var string
   */
  public $imageTitle;

  /**
   * @inheritdoc
   */
  public function setView() {
    new GalleryImageView($this);
    return $this;
  }

  /**
   * Get the image description list points.
   *
   * @return array
   */
  public function getImageDescription() {
    global $i18n, $user;
    $details = $this->imageModel->getImageDetails();
    if (empty($details["description"])) {
      $details["description"] = new Alert("{$i18n->t("No {0} available, could you provide one?", [ $i18n->t("Description") ])} {$this->view->a(
        $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $this->model->id, $this->imageModel->id ]),
        $i18n->t("Click here to do so.")
      )}");
    }
    $desc = [[ $i18n->t("Description"), $details["description"] ]];
    if (isset($details["country"])) {
      if (empty($details["country"])) {
        $details["country"] = new Alert("{$i18n->t("No {0} available, could you provide one?", [ $i18n->t("Country") ])} {$this->view->a(
          $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}/edit", [ $this->model->id, $this->imageModel->id ]),
          $i18n->t("Click here to do so.")
        )}");
      }
      else {
        $details["country"] = $this->view->a($i18n->r("/country/{0}", [ $details["country"]["code"] ]), $details["country"]["name"]);
      }
      $desc[] = [ $i18n->t("Country"), $details["country"] ];
    }
    else {
      $desc[] = [ $i18n->t("Country"), $this->a($i18n->r("/country/{0}", [ $details["country"]["code"] ]), $details["country"]["name"]) ];
    }
    $desc[] = [ $i18n->t("Dimensions"), $i18n->t("{0} × {1} pixels", [ $details["imageWidth"], $details["imageHeight"] ]) ];
    $desc[] = [ $i18n->t("Size"), msgfmt_format_message($i18n->locale, "{0,number,integer}", [ $details["imageSize"] ]) ];
    $desc[] = [ $i18n->t("User"), $this->view->a($i18n->r("/user/{0}", [ $details["#user"]->name ]), $details["#user"]->name) ];
    $desc[] = [ $i18n->t("Creation Date"), $i18n->formatDate($details["created"], $user->timezone, IntlDateFormatter::MEDIUM) ];
    $desc[] = [ $i18n->t("Last Update"), $i18n->formatDate($details["changed"], $user->timezone, IntlDateFormatter::MEDIUM) ];
    $desc[] = [ $i18n->t("Source"), $details["source"] ];
    return $desc;
  }

  /**
   * Get the gallery's image stream.
   *
   * @return string
   *   The gallery's image stream.
   */
  public function getImageStream() {
    global $i18n;
    $stream = "";
    $c = count($this->images);
    for ($i = 0; $i < $c; ++$i) {
      $stream .= $this->view->a(
        $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}/{1}", [ $this->model->id, $this->images[$i]->sectionId ]),
        $this->view->getImage($this->images[$i], AbstractImageModel::IMAGESTYLE_DETAILS_STREAM),
        [ "class" => "span span--1" ]
      );
    }
    return $stream;
  }

  /**
   * @inheritdoc
   */
  public function getBreadcrumb() {
    global $i18n;
    $breadcrumb = parent::getBreadcrumb();
    $breadcrumb[] = [ $i18n->r("/{$_SERVER["ACTION"]}/{0}/{$_SERVER["TAB"]}s", [ $this->model->id ]), $this->galleryTitle ];
    return $breadcrumb;
  }

}
