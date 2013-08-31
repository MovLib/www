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
   * String buffer used to concatenate all the output and send the whole shebang at once.
   *
   * @var string
   */
  public $presentation = "";

  /**
   * The current view.
   *
   * @var \MovLib\View\HTML\AbstractPageView
   */
  public $view;


  // ------------------------------------------------------------------------------------------------------------------- Abstract methods


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


  // ------------------------------------------------------------------------------------------------------------------- Public methods


  /**
   * Get the <var>$_SERVER["ACTION"]</var> as string to initialize object.
   *
   * This can be used to retrieve the value of <var>$_SERVER["ACTION"]</var> in CamelCase and therefore use it to
   * instanciate a class. The value itself is extracted via nginx from the requested URL and passed along as FastCGI
   * parameter. So for instead you might have a requested URL like <tt>/user/sign_up</tt>, <tt>sign_up</tt> will be the
   * action and this method will return <tt>SignUp</tt> so one can simply concatenate it.
   *
   * <b>Usage example:</b>
   * <pre>$class = "\\MovLib\\View\\HTML\\User\\User{$this->getAction()}View";
   * $this->output = (new $class())->getRenderedView();</pre>
   *
   * @staticvar string $action
   *   Used to cache the construction.
   * @param string $defaultAction
   *   [Optional] The default action value to use if <var>$_SERVER["ACTION"]</var> is empty. Defaults to <tt>Show</tt>.
   * @return string
   *   CamelCase representation of the action if set, otherwise <tt>Show</tt> is returned.
   */
  public function getAction($defaultAction = "Show") {
    static $action = null;
    if ($action === null) {
      $action = isset($_SERVER["ACTION"]) ? $_SERVER["ACTION"] : $defaultAction;
    }
    return $action;
  }

  /**
   * Get the current server request method as CamelCase string.
   *
   * @staticvar string $method
   *   Used to cache the construction.
   * @return string
   *   The request method as CamelCase string.
   */
  public function getMethod() {
    static $method = null;
    if ($method === null) {
      $method = ucfirst(strtolower($_SERVER["REQUEST_METHOD"]));
    }
    return $method;
  }

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


  // ------------------------------------------------------------------------------------------------------------------- Protected Methods


  /**
   * Set output for this presenter.
   *
   * @param string $viewName [optional]
   *   The n ame of the view without the <code>"View"</code> suffix. You have to include names of folders if your view
   *   is in a subdirectory within the view directory (e.g. <code>"User\\UserShow"</code>). If no value is passed, the
   *   object in property <var>$this->view</var> is used to generate the output. You must call
   *   <code>$this->getView()</code> before calling this method in this case.
   * @param string $viewType [optional]
   *   The foldername within the view directory, defaults to <code>"HTML"</code>.
   * @param string $method [optional]
   *   The name of the method that should be called to set the output, defaults to <code>"getRenderedView"</code>.
   * @return this
   */
  protected function setPresentation($viewName = null, $viewType = "HTML", $method = "getRenderedView") {
    if ($viewName !== null) {
      $view = "\\MovLib\\View\\{$viewType}\\{$viewName}View";
      $this->view = new $view($this);
    }
    $this->presentation = $this->view->{$method}();
    return $this;
  }

}
