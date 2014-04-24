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

use \MovLib\Exception\RedirectException\SeeOtherException;
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
   * The submitted (raw) password.
   *
   * @var string
   */
  protected $rawPassword;

  /**
   * The route to which we should redirect after successful sign in.
   *
   * @var string
   */
  protected $redirectTo;


  // ------------------------------------------------------------------------------------------------------------------- Setup


  /**
   * {@inheritdoc}
   */
  public function init() {
    // We need to know the translated version of the sign in route for comparison.
    $query         = null;
    $routeKey      = "/profile/sign-in";
    $redirectToKey = $this->intl->r("redirect_to");
    $route         = $this->intl->r($routeKey);

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    //
    // We won't append the redirect to query string to the language links in the footer because we have no chance to
    // find out what the translated version of that route would be.
    if ($this->request->path != $route) {
      $this->request->uri = "{$route}?{$redirectToKey}={$this->request->path}";
    }
    // If the client is signed in, but didn't request to be signed out or is currently submitting this form, redirect to
    // the personal dashboard.
    elseif ($this->request->methodGET && $this->session->isAuthenticated) {
      throw new SeeOtherException($this->intl->r("/dashboard"));
    }
    // Append the URL to the action attribute of our form.
    elseif (($this->redirectTo = $this->request->filterInputString(INPUT_GET, $redirectToKey)) && $this->redirectTo != $route) {
      $query              = [ $redirectToKey => $this->redirectTo ];
      $this->request->uri = "{$route}?{$redirectToKey}={$this->redirectTo}";
    }

    // Start rendering the page.
    $this->initPage($this->intl->t("Sign In"));
    $this->breadcrumb->addCrumb($this->intl->r("/users"), $this->intl->t("Users"));
    $this->initLanguageLinks($routeKey, null, false, $query);
    $this->breadcrumb->ignoreQuery = true;

    return $this;
  }


  // ------------------------------------------------------------------------------------------------------------------- Layout


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore =
      "<a class='btn btn-large btn-info fr' href='{$this->intl->r("/profile/join")}'>{$this->intl->t(
        "Join {sitename}",
        [ "sitename" => $this->config->sitename ]
      )}</a>"
    ;

    $form = (new Form($this->diContainerHTTP, [ "class" => "s s6 o3" ]))
      ->addElement(new InputEmail($this->diContainerHTTP, "email", $this->intl->t("Email Address"), $this->email, [
        "#help-text"  => "<a href='{$this->intl->r("/profile/reset-password")}'>{$this->intl->t("Forgot your password?")}</a>",
        "autofocus"   => true,
        "placeholder" => $this->intl->t("Enter your email address"),
        "required"    => true,
      ]))
      ->addElement(new InputPassword($this->diContainerHTTP, "password", $this->intl->t("Password"), $this->rawPassword, [
        "placeholder" => $this->intl->t("Enter your password"),
        "required"    => true,
      ]))
      ->addAction($this->intl->t("Sign In"), [ "class" => "btn btn-large btn-success" ])
      ->init([ $this, "valid" ])
    ;

    return "<div class='c'><div class='r'>{$form}</div></div>";
  }


  // ------------------------------------------------------------------------------------------------------------------- Validation


  /**
   * {@inheritdoc}
   */
  public function valid() {
    // @devStart
    // @codeCoverageIgnoreStart
    $this->log->debug("Authenticating user", [ "email" => $this->email, "password" => $this->rawPassword ]);
    // @codeCoverageIgnoreEnd
    // @devEnd

    if ($this->session->authenticate($this->email, $this->rawPassword)) {
      $this->alertSuccess(
        $this->intl->t("Sign in successful"),
        $this->intl->t("Welcome back {username}!", [ "username" => $this->placeholder($this->session->userName) ])
      );

      throw new SeeOtherException($this->redirectTo ?: $this->intl->r("/dashboard"));
    }

    $this->alertError(
      $this->intl->t("Sign in failed"),
      $this->intl->t("We either don’t know the email address, or the password was wrong.")
    );

    return $this;
  }

}
