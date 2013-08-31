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

use \Exception;
use \ReflectionClass;

/**
 * Base class for any presenter.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 */
abstract class AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The current view.
   *
   * @var \MovLib\View\HTML\AbstractPageView
   */
  public $view;


  // ------------------------------------------------------------------------------------------------------------------- Abstract Methods


  /**
   * Instantiate new presenter instance.
   */
  abstract public function __construct();

  /**
   * Associative array containing the breadcrumb trail for this presenter.
   *
   * The array will be passed to <code>AbstractView::getBreadcrumb()</code> which calls
   * <code>AbstractView::getNavigation()</code>, the returned array must be in the correct format for the last method.
   * Do not include the home nor the current page link, they are included automatically. Only specify the intermediate
   * points.
   *
   * <b>Example Usage:</b>
   * The following example is for the route <tt>/movie/1234/release-1234/discussion-1234</tt>:
   * <pre>return [
   *   // Home link is included automatically!
   *   [
   *     "href" => $i18n->r("movies"),
   *     "text" => $i18n->t("Movies"),
   *     "title" => $i18n->t("Go to movies overview page."),
   *   ],
   *   [
   *     "href" => $i18n->r("/movie/{0,number,integer}", [ $movieId ]),
   *     "text" => $movieTitle,
   *     "title" => $i18n->t("Go to “{0}” movie page.", [ $movieTitle ]),
   *   ],
   *   [
   *     "href" => $i18n->r("/movie/{0,number,integer}/release-{1,number,integer}", [ $movidId, $releaseId ]),
   *     "text" => $releaseTitle,
   *     "title" => $i18n->t("Got to “{0}” release page.", [ $releaseTitle ]),
   *   ],
   *   // Link to current page is included automatically!
   * ]</pre>
   *
   * @return array
   *   Array containing the breadcrumb trail for this presenter.
   */
  abstract public function getBreadcrumb();


  // ------------------------------------------------------------------------------------------------------------------- Public Methods


  /**
   * Get the presenter's short class name (e.g. <em>Abstract</em> for <em>AbstractPresenter</em>).
   *
   * The short name is the name of the current instance of this class without the namespace and the presenter suffix.
   *
   * @staticvar boolean|string $shortName
   * @return string
   *   The short name of the class (lowercased).
   */
  public function getShortName() {
    static $shortName = false;
    if ($shortName === false) {
      // Always remove the "Presenter" suffix from the name.
      $shortName = substr((new ReflectionClass($this))->getShortName(), 0, -9);
    }
    return $shortName;
  }

  /**
   * Get the presentation of this presenter.
   *
   * @return string
   */
  public function __toString() {
    // A __toString() method is not allowed to throw any kind of exception, therefor we catch everything and hope that
    // our exception view is working.
    try {
      return $this->view->getRenderedView();
    } catch (Exception $e) {
      return (new ExceptionPresenter($e))->__toString();
    }
  }

}
