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

use \MovLib\Mail\Mailer;
use \MovLib\Mail\Webmaster;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;

/**
 * The coming soon page.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class ComingSoon extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The presenter's form.
   *
   * @var \MovLib\Partial\Form
   */
  protected $form;

  /**
   * The user submitted email address.
   *
   * @var string
   */
  protected $email;


  // ------------------------------------------------------------------------------------------------------------------- Setup


  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->initPage($this->config->sitename);
    $this->prefetch("//{$this->config->hostname}/");
    $this->next("//{$this->config->hostname}/");

    $this->form = new Form($this->diContainerHTTP);
    $this->form->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [
      "autofocus"   => true,
      "placeholder" => $this->intl->t("Sign up for the {sitename} beta!", [ "sitename" => $this->config->sitename ]),
      "required"    => true,
    ]));
    $this->form->addAction($this->intl->t("Sign Up"), [ "class" => "btn btn-large btn-success" ]);
    $this->form->init([ $this, "valid" ]);

    $this->stylesheets[] = "coming-soon";
  }


  // ------------------------------------------------------------------------------------------------------------------- Layout


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return
      "<p class='tac'>{$this->intl->t(
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
      "<div class='r'><div class='s s8 o2'>{$this->form}</div></div>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getFooter() {
    return
      "<footer id='f' role='contentinfo'>" .
        "<h1 class='vh'>{$this->intl->t("Infos all around {sitename}", [ "sitename" => $this->config->sitename ])}</h1>" .
        "<div class='c'><div class='r'>" .
          "<p class='s s12 tac'>{$this->intl->t("The open beta is scheduled to start in June 2014.")}</p>" .
          "<section id='f-logos' class='s s12 tac'>" .
            "<h3 class='vh'>{$this->intl->t("Sponsors and external resources")}</h3>" .
            "<a class='no-link' href='http://www.fh-salzburg.ac.at/' target='_blank'>" .
              "<img alt='Fachhochschule Salzburg' height='30' src='{$this->fs->getExternalURL("asset://img/footer/fachhochschule-salzburg.svg")}' width='48'>" .
            "</a>" .
            "<a class='no-link' href='https://github.com/MovLib' target='_blank'>" .
              "<img alt='GitHub' height='30' src='{$this->fs->getExternalURL("asset://img/footer/github.svg")}' width='48'>" .
            "</a>" .
          "</section>" .
          "<p class='last s s12 tac'>{$this->intl->t("Wanna see the current alpha version of {sitename}? Go to {alpha_url}", [
            "sitename"  => $this->config->sitename,
            "alpha_url" => "<a href='//{$this->config->hostname}/'>{$this->config->hostname}</a>",
          ])}</p>" .
        "</div>" .
      "</div></footer>"
    ;
  }

  /**
   * {@inheritdoc}
   */
  public function getHeader() {
    return "";
  }

  /**
   * {@inheritdoc}
   */
  protected function getHeadTitle() {
    return $this->intl->t("{0}, {1}.", [ $this->config->sitename, $this->intl->t("the free movie library") ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getMainContent($content) {
    return
      "<main class='{$this->id}-content' id='m' role='main'><div class='c'>" .
        "<h1 class='cf'>" .
          "<img alt='' height='192' src='{$this->fs->getExternalURL("asset://img/logo/vector.svg")}' width='192'>" .
          "<span>{$this->config->sitename}{$this->intl->t(
            "{0}The {1}free{2} movie library.{3}",
            [ "<small>", "<em>", "</em>", "</small>" ]
          )}</span>" .
        "</h1>{$this->alerts}{$content}" .
      "</div></main>"
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Validation


  /**
   * Submitted email address is valid, add the email address to our subscribers.
   *
   * @return this
   */
  public function valid() {
    // Send an email with the new subscriber to the webmaster.
    (new Mailer())->send($this->diContainerHTTP, new Webmaster(
      $this->diContainerHTTP,
      "New beta subscription",
      "<a href='mailto:{$this->email}'>{$this->email}</a> would like to be part of the MovLib beta."
    ));

    // Append new subscriber to subscription list (not save to use database while we're still developing).
    file_put_contents("{$_SERVER["HOME"]}/subscriptions.txt", "\n{$this->email}", FILE_APPEND);

    // Let the user know that the subscription was successful.
    $this->alerts .= new Alert(
      $this->intl->t("Thanks for signing up for the {sitename} beta {email}.", [
        "sitename" => $this->config->sitename,
        "email"    => $this->placeholder($this->email),
      ]),
      $this->intl->t("Successfully Signed Up"),
      Alert::SEVERITY_SUCCESS
    );

    return $this;
  }

}
