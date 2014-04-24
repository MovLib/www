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
namespace MovLib\Partial\Navigation;

/**
 * Defines the breadcrumb navigation object.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Breadcrumb extends \MovLib\Core\Presentation\DependencyInjectionBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The title for the current page's crumb.
   *
   * @var string
   */
  protected $title;

  /**
   * The breadcrumb's trail.
   *
   * @var array
   */
  protected $trail;

  /**
   * Support navigation interface for old code.
   *
   * @deprecated
   * @var array
   */
  public $menuitems = [];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new breadcrumb navigation object.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   {@inheritdoc}
   * @param string $title
   *   The title for the current page's crumb.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $title) {
    parent::__construct($diContainerHTTP);
    $this->addCrumb("/", $this->intl->t("Home"), [ "title" => $this->intl->t("Go back to the home page.") ]);
    $this->title = $title;
  }

  /**
   * Get the breadcrumb navigation's string representation.
   *
   * @return string
   *   The breadcrumb navigation's string representation.
   */
  public function __toString() {
    $crumbs = "";

    // Hidden feature for our most special presenter's, if the title for the crumb of the current presentation is set
    // to FALSE we won't include the current page. Only from interest for index presenter's.
    if ($this->title) {
      $this->addCrumb($this->request->path, $this->title);
    }

    $c = count($this->trail);
    for ($i = 0; $i < $c; ++$i) {
      $crumbs .= "<li typeof='Breadcrumb'>{$this->a(
        $this->trail[$i][0],
        "<span class='small' property='title'>{$this->trail[$i][1]}</span>",
        $this->trail[$i][2]
      )}</li>";
    }

    return
      "<nav id='breadcrumb' role='navigation' vocab='http://data-vocabulary.org/'>" .
        "<h2 class='vh'>{$this->intl->t("Breadcrumb")}</h2>" .
        "<ol class='no-list'>{$crumbs}</ol>" .
      "</nav>"
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Add a crumb to the breadcrumb's trail.
   *
   * @param string $route
   *   The crumb's route in the current locale.
   * @param string $text
   *   The crumb's text in the current locale.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the crumb.
   * @return this
   */
  public function addCrumb($route, $text, array $attributes = []) {
    // @devStart
    // @codeCoverageIgnoreStart
    assert(!empty($route), "The route of a breadcrumb trail's crumb cannot be empty!");
    assert(!empty($route), "The text of a breadcrumb trail's crumb cannot be empty!");
    // @codeCoverageIgnoreEnd
    // @devEnd
    if (mb_strlen($text) > 25) {
      if (empty($attributes["title"])) {
        $attributes["title"] = $text;
      }
      $text = mb_strimwidth($text, 0, 25, $this->intl->t("…"));
    }
    $attributes["property"] = "url";
    $this->trail[] = [ $route, $text, $attributes ];
    return $this;
  }

  /**
   * Add multiple crumbs to the breadcrumb's trail.
   *
   * @param array $crumbs
   *   The crumbs to add to the breadcrumb's trail.
   * @return this
   * @throws \ErrorException
   */
  public function addCrumbs(array $crumbs) {
    foreach ($crumbs as $crumb) {
      call_user_func_array([ $this, "addCrumb" ], $crumb);
    }
    return $this;
  }

}
