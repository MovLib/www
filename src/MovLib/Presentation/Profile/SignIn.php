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
namespace MovLib\Presentation\Profile;

use \MovLib\Exception\SeeOtherException;
use \MovLib\Partial\Alert;
use \MovLib\Partial\Form;
use \MovLib\Partial\FormElement\InputEmail;
use \MovLib\Partial\FormElement\InputPassword;

/**
 * User sign in presentation.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class SignIn extends \MovLib\Presentation\AbstractPresenter {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The submitted email address.
   *
   * @var string
   */
  protected $email;

  /**
   * The presentation's form.
   *
   * @var \MovLib\Partial\Form
   */
  protected $form;

  /**
   * The submitted (raw) password.
   *
   * @var string
   */
  protected $rawPassword;


  // ------------------------------------------------------------------------------------------------------------------- Setup


  /**
   * {@inheritdoc}
   */
  public function init() {
    // We need to know the translated version of the sign in route for comparison.
    $routeKey = "/profile/sign-in";
    $redirectToKey = $this->intl->r("redirect_to");
    $this->initLanguageLinks($routeKey);
    $route = $this->intl->r($routeKey);

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    //
    // We won't append the redirect to query string to the language links in the footer because we have no chance to
    // find out what the translated version of that route would be.
    if ($this->request->uri != $route) {
      if (empty($this->request->query[$redirectToKey])) {
        $this->request->query[$redirectToKey] = $this->request->uri;
      }
    }
    // If the user is logged in, but didn't request to be signed out, redirect her or him to the personal dashboard.
    elseif ($this->session->isAuthenticated) {
      throw new SeeOtherException($this->intl->r("/my"));
    }

    // Append the URL to the action attribute of our form.
    $redirectTo = $this->request->filterInput(INPUT_GET, $redirectToKey, FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR | FILTER_FLAG_STRIP_LOW);
    if ($redirectTo && $redirectTo != $route) {
      $redirectTo = rawurlencode(rawurldecode($redirectTo));
      $this->request->uri .= "?{$redirectToKey}={$redirectTo}";
    }

    // Start rendering the page.
    $this->initPage($this->intl->t("Sign In"));
    $this->initBreadcrumb([[ $this->intl->rp("/users"), $this->intl->t("Users") ]]);
    $this->breadcrumb->ignoreQuery = true;

    $this->headingBefore = "<a class='btn btn-large btn-primary fr' href='{$this->intl->r("/profile/join")}'>{$this->intl->t(
      "Join {sitename}",
      [ "sitename" => $this->config->sitename ]
    )}</a>";

    $this->form = new Form($this->diContainerHTTP);

    $this->form->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [
      "#help-text"  => "<a href='{$this->intl->r("/profile/reset-password")}'>{$this->intl->t("Forgot your password?")}</a>",
      "autofocus"   => true,
      "placeholder" => $this->intl->t("Enter your email address"),
      "required"    => true,
    ]));

    $this->form->addElement(new InputPassword($this->diContainerHTTP, "password", $this->intl->t("Password"), $this->rawPassword, [
      "placeholder" => $this->intl->t("Enter your password"),
      "required"    => true,
    ]));

    $this->form->addAction($this->intl->t("Sign In"), [ "class" => "btn btn-large btn-success" ]);

    $this->form->init([ $this, "valid" ], [ "class" => "s s6 o3" ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Layout


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return "<div class='c'><div class='r'>{$this->form}</div></div>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Validation


  /**
   * {@inheritdoc}
   */
  public function valid() {
    if ($this->session->authenticate($this->email, $this->rawPassword)) {
      $this->alerts .= new Alert(
        $this->intl->t("Successfully Signed In"),
        $this->intl->t("Welcome back {username}!", [ "username" => $this->session->userName ]),
        Alert::SEVERITY_SUCCESS
      );

      $redirectTo = $this->request->filterInput(INPUT_GET, $this->intl->r("redirect_to"), FILTER_SANITIZE_STRING);
      throw new SeeOtherException($redirectTo ?: $this->intl->r("/my"));
    }

    $this->alerts .= new Alert(
      $this->intl->t("We either don’t know the email address, or the password was wrong."),
      $this->intl->t("Sign In Failed"),
      Alert::SEVERITY_ERROR
    );

    return $this;
  }

}
