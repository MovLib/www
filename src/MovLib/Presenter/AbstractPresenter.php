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

use \MovLib\Entity\Language;
use \MovLib\Entity\User;
use \MovLib\Utility\HTTP;
use \MovLib\View\HTML\AlertView;
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
  protected $output = "";

  /**
   * The currently active language.
   *
   * @var \MovLib\Entity\Language
   */
  protected $language;

  /**
   * The current user.
   *
   * @var \MovLib\Entity\User
   */
  protected $user;

  /**
   * The current view.
   *
   * @var mixed
   */
  protected $view;


  // ------------------------------------------------------------------------------------------------------------------- Magic methods


  /**
   * Instantiate new presenter object.
   */
  public function __construct() {
    $this->language = new Language();
    $this->user = new User();
    try {
      $this->init();
    } catch (Exception $e) {
      $this->output = new ErrorView($this->language, $e);
    }
  }

  /**
   * Magic function that is automatically called if somebody tries to echo the object itself.
   *
   * @return string
   */
  public function __toString() {
    if (xdebug_is_enabled()) {
      ob_start();
      var_dump($this);
      return ob_get_clean();
    }
    else {
      return "<pre>" . print_r($this, true) . "</pre>";
    }
  }


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
   *     "href" => route("movies"),
   *     "text" => __("Movies"),
   *     "title" => __("Go to movies overview page."),
   *   ],
   *   [
   *     "href" => route("movie/%u", $movieId),
   *     "text" => $movieTitle,
   *     "title" => __("Go to “@movieTitle” movie page.", [ "@movieTitle" => $movieTitle ]),
   *   ],
   *   [
   *     "href" => route("movie/%u/release-%u", [ $movidId, $releaseId ]),
   *     "text" => $releaseTitle,
   *     "title" => __("Got to “@releaseTitle” release page.", [ "@releaseTitle" => $releaseTitle ]),
   *   ],
   *   // Link to current page is included automatically!
   * ]</pre>
   *
   * @return array
   *   Array containing the breadcrumb trail for this presenter.
   */
  abstract public function getBreadcrumb();

  /**
   * Initialize the presenter and set the output.
   */
  protected abstract function init();


  // ------------------------------------------------------------------------------------------------------------------- Public final methods


  /**
   * Get the <var>$_SERVER["ACTION"]</var> als string to initialize object.
   *
   * This can be used to retrieve the value of <var>$_SERVER["ACTION"]</var> in CamelCase and therefor use it to
   * instanciate a class. The value itself is extracted via nginx from the requested URL and passed along as FastCGI
   * parameter. So for instead you might have a requested URL like <tt>/user/sign_up</tt>, <tt>sign_up</tt> will be the
   * action and this method will return <tt>SignUp</tt> so one can simply concatenate it.
   *
   * <b>Usage example:</b>
   * <pre>$class = "\\MovLib\\View\\HTML\\User\\User{$this->getAction()}View";
   * $this->output = (new $class($this->language))->getRenderedView();</pre>
   *
   * @param string $defaultAction
   *   [Optional] The default action value to use if <var>$_SERVER["ACTION"]</var> is empty. Defaults to <tt>Show</tt>.
   * @return string
   *   CamelCase representation of the action if set, otherwise <tt>Show</tt> is returned.
   */
  public final function getAction($defaultAction = "Show") {
    if (isset($_SERVER["ACTION"])) {
      return $_SERVER["ACTION"];
    }
    return $defaultAction;
  }

  /**
   * Get the current language object.
   *
   * @return \MovLib\Entity\Language
   */
  public final function getLanguage() {
    return $this->language;
  }

  /**
   * Get the output.
   *
   * @return string
   */
  public final function getOutput() {
    return $this->output;
  }

  /**
   * Get value identified by key from global post array without triggering an error if array or offset (key) does not
   * exist.
   *
   * @param mixed $key
   *   The key (offset in the array) to identify the desired value within the global post array.
   * @param mixed $defaultValue
   *   [Optional] The value that should be returned if global post array or the key within the global post array is not
   *   present. Defaults to an empty string.
   * @return mixed
   *   The desired value if present or value of <var>$defaultValue</var>.
   */
  public final function getPostValue($key, $defaultValue = "") {
    if (isset($_POST[$key])) {
      return $_POST[$key];
    }
    return $defaultValue;
  }

  /**
   * Get the current user object.
   *
   * @return \MovLib\Entity\User
   */
  public final function getUser() {
    return $this->user;
  }


  // ------------------------------------------------------------------------------------------------------------------- Public methods


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
      // Always remove the "presenter" suffix from the name.
      $shortName = substr((new ReflectionClass($this))->getShortName(), 0, -9);
    }
    return $shortName;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected final methods


  /**
   * Get (and set) the current view.
   *
   * @param string $viewName
   *   The name of the view without the <tt>View</tt> suffix. You have to include names of folders if your view is in a
   *   subdirectory within the view folder (e.g. <tt>User\\UserShow</tt>).
   * @param string $viewType
   *   [Optional] The foldername within the view directory. Defaults to <tt>HTML</tt>.
   * @return $this
   */
  protected final function getView($viewName, $viewType = "HTML") {
    $this->setView($viewName, $viewType);
    return $this->view;
  }

  /**
   * Set output for this presenter.
   *
   * @param string $viewName
   *   [Optional] The name of the view without the <tt>View</tt> suffix. You have to include names of folders if your
   *   view is in a subdirectory within the view folder (e.g. <tt>User\\UserShow</tt>). If no value is passed along
   *   the object from the property <var>AbstractPresenter::$view</var> is used to generate the output. You must call
   *   <code>AbstractPresenter::getView()</code> before calling this method in this case.
   * @param string $viewType
   *   [Optional] The foldername within the view directory. Defaults to <tt>HTML</tt>.
   * @param string $method
   *   [Optional] The name of the method that should be called to set the output. Defaults to <tt>getRenderedView</tt>.
   * @return $this
   */
  protected final function setOutput($viewName = null, $viewType = "HTML", $method = "getRenderedView") {
    if ($viewName !== null) {
      $this->setView($viewName, $viewType);
    }
    $this->output = $this->view->{$method}();
    return $this;
  }

  /**
   * Set the current view.
   *
   * @param string $viewName
   *   The name of the view without the <tt>View</tt> suffix. You have to include names of folders if your view is in a
   *   subdirectory within the view folder (e.g. <tt>User\\UserShow</tt>).
   * @param string $viewType
   *   [Optional] The foldername within the view directory. Defaults to <tt>HTML</tt>.
   * @return $this
   */
  protected final function setView($viewName, $viewType = "HTML") {
    $view = "\\MovLib\\View\\{$viewType}\\{$viewName}View";
    $this->view = new $view($this);
    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Protected methods


  /**
   * Display the alert view to the user with a single alert message and no additional content.
   *
   * <b>IMPORTANT!</b> This will overwrite your current view but not your output! Call
   *                   <code>AbstractPresenter::setOutput()</code> without any arguments to set the output.
   *
   * @see \MovLib\View\HTML\AlertView
   * @see \MovLib\View\HTML\AbstractView::setAlert()
   * @param string $title
   *   Short descriptive title that summarizes the alert, also used as page title!
   * @param string $message
   *   The message that should be displayed to the user.
   * @param string $severity
   *   [optional] The severity level of this alert, defaults to warning. Available severity levels are:
   *   <ul>
   *     <li>info</li>
   *     <li>warning (default)</li>
   *     <li>success</li>
   *     <li>error</li>
   *   </ul>
   * @param boolean $block
   *   [optional] If your message is very long, or your alert is very important, increase the padding around the message
   *   and enclose the title in a level-4 heading instead of the bold tag.
   * @return $this
   */
  protected function showSingleAlertAlertView($title, $message, $severity = "warning", $block = false) {
    $this->view = (new AlertView($this, $title))->setAlert($message, $title, $severity, $block);
    return $this;
  }

}
