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
namespace MovLib\Presenter;

use \MovLib\Model\MoviesModel;
use \MovLib\View\HTML\MoviesView;

/**
 * Description of MoviesPresenter
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
class MoviesPresenter extends AbstractPresenter {

  /**
   * The MoviesModel containing all the movies needed for the listing.
   *
   * @var \MovLib\Model\MoviesModel
   */
  public $moviesModel;

  /**
   * Initialize the MoviesPresenter.
   *
   * @global \MovLib\Model\I18nModel $i18n
   */
  public function __construct() {
    global $i18n;
    $this->moviesModel = new MoviesModel();
    $this->view = new MoviesView($this, $i18n->t("Movies"));
    return $this->setPresentation();
  }

  public function getBreadcrumb() {}

}