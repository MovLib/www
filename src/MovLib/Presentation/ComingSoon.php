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
namespace MovLib\Presentation;

use \MovLib\Presentation\Email\Webmaster;
use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\InputEmail;

/**
 * The coming soon page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class ComingSoon extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The user submitted email address.
   *
   * @var string
   */
  protected $email;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new coming soon presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   */
  public function __construct() {
    global $i18n, $kernel;
    $this->initPage($kernel->siteName);

    // Configure and initialize the form.
    $this->formAddElement(new InputEmail("email", $i18n->t("Email Address"), [
      "autofocus"   => true,
      "placeholder" => $i18n->t("Sign up for the {sitename} beta!", [ "sitename" => $kernel->siteName ]),
      "required"    => true,
    ], $this->email));
    $this->formAddAction($i18n->t("Sign Up"), [ "class" => "btn btn-large btn-success" ]);
    $this->formInit();

    $kernel->stylesheets[] = "coming-soon";
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   */
  protected function getFooter() {
    global $i18n, $kernel;
    return
      "<footer id='f' role='contentinfo'>" .
        "<h1 class='vh'>{$i18n->t("Infos all around {sitename}", [ "sitename" => $kernel->siteName ])}</h1>" .
        "<div class='c'><div class='r'>" .
          "<p class='s s12 tac'>{$i18n->t("The open beta is scheduled to start in June 2014.")}</p>" .
          "<section id='f-logos' class='s s12 tac'>" .
            "<h3 class='vh'>{$i18n->t("Sponsors and external resources")}</h3>" .
            "<a class='img' href='http://www.fh-salzburg.ac.at/' target='_blank'>" .
              "<img alt='Fachhochschule Salzburg' height='30' src='{$kernel->getAssetURL("footer/fachhochschule-salzburg", "svg")}' width='48'>" .
            "</a>" .
            "<a class='img' href='https://github.com/MovLib' target='_blank'>" .
              "<img alt='GitHub' height='30' src='{$kernel->getAssetURL("footer/github", "svg")}' width='48'>" .
            "</a>" .
          "</section>" .
          "<p class='last s s12 tac'>{$i18n->t("Wanna see the current alpha version of {sitename}? Go to {alpha_url}", [
            "sitename"  => $kernel->siteName,
            "alpha_url" => "<a href='//{$kernel->domainDefault}/'>{$kernel->domainDefault}</a>",
          ])}</p>" .
        "</div>" .
      "</div></footer>"
    ;
  }

  /**
   * @inheritdoc
   */
  protected function getHeader() {
    return "";
  }

  /**
   * @inheritdoc
   */
  protected function getHeadTitle() {
    global $kernel;
    return $kernel->siteNameAndSlogan;
  }

  /**
   * @inheritdoc
   */
  protected function getMainContent() {
    global $i18n, $kernel;
    return
      "<main class='{$this->id}-content' id='m' role='main'><div class='c'>" .
        "<h1 class='cf'>" .
          "<img alt='' height='192' src='{$kernel->getAssetURL("logo/vector", "svg")}' width='192'>" .
          "<span>{$kernel->siteNameAndSloganHTML}</span>" .
        "</h1>" .
        $this->alerts .
        "<p class='tac'>{$i18n->t(
          "Imagine {1}Wikipedia{0}, {2}Discogs{0}, {3}Last.fm{0}, {4}IMDb{0}, and {5}TheMovieDB{0} combined in a " .
          "totally free and open project.",
          [
            "</a>",
            "<a href='//en.wikipedia.org/' target='_blank'>",
            "<a href='http://www.discogs.com/' target='_blank'>",
            "<a href='http://www.last.fm/' target='_blank'>",
            "<a href='http://www.imdb.com/' target='_blank'>",
            "<a href='http://www.themoviedb.org/' target='_blank'>",
          ]
        )}</p>" .
        "<div class='r'><div class='s s8 o2'>{$this->formRender()}</div></div>" .
      "</div></main>"
    ;
  }

  /**
   * The submitted form has no auto-validation errors, continue normal program flow.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @return this
   */
  protected function formValid() {
    global $i18n, $kernel;

    // Send an email with the new subscriber to the webmaster.
    $kernel->sendEmail(new Webmaster(
      "New beta subscription",
      "<a href='mailto:{$this->email}'>{$this->email}</a> would like to be part of the MovLib beta."
    ));

    // Append new subscriber to subscription list (not save to use database while we're still developing).
    file_put_contents("{$kernel->documentRoot}/private/subscriptions.txt", "\n{$this->email}", FILE_APPEND);

    // Let the user know that the subscription was successful.
    $this->alerts .= new Alert(
      $i18n->t("Thanks for signing up for the {sitename} beta {email}.", [
        "sitename" => $kernel->siteName,
        "email"    => $this->placeholder($this->email),
      ]),
      $i18n->t("Successfully Signed Up"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
