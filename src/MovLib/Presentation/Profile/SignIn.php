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

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\FormElement\InputEmail;
use \MovLib\Presentation\Partial\FormElement\InputPassword;
use \MovLib\Presentation\Redirect\SeeOther;

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
  use \MovLib\Presentation\TraitForm;


  // ------------------------------------------------------------------------------------------------------------------- Properties
  // The properties are public to allow classes that throw an UnauthorizedException to manipulate them.


  /**
   * The submitted email address.
   *
   * @var string
   */
  public $email;

  /**
   * The submitted (raw) password.
   *
   * @var string
   */
  protected $rawPassword;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Instantiate new sign in presentation.
   *
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  public function __construct() {
    // We need to know the translated version of the sign in route for comparison.
    $this->initLanguageLinks("/profile/sign-in");

    // Snatch the current requested URI if a redirect was requested and no redirect is already active. We have to build
    // the complete target URI to ensure that this presenter will receive the submitted form, but at the same time we
    // want to enable ourself to redirect the user after successful sign in to the page she or he requested.
    if ($kernel->requestURI != $this->languageLinks[$this->intl->languageCode]) {
      if (empty($_GET["redirect_to"])) {
        $_GET["redirect_to"] = $kernel->requestURI;
      }
    }
    // If the user is logged in, but didn't request to be signed out, redirect her or him to the personal dashboard.
    elseif ($session->isAuthenticated === true) {
      throw new SeeOther($this->intl->r("/my"));
    }

    // Ensure all views are using the correct path info to render themselves.
    $kernel->requestURI = $kernel->requestPath = $this->languageLinks[$this->intl->languageCode];

    // Append the URL to the action attribute of our form.
    $redirectToKey = $this->intl->r("redirect_to");
    if (!empty($_GET[$redirectToKey]) && $_GET[$redirectToKey] != $this->languageLinks[$this->intl->languageCode]) {
      $redirectTo          = rawurlencode(rawurldecode($_GET[$redirectToKey]));
      $kernel->requestURI .= "?{$redirectToKey}={$redirectTo}";
    }

    // Start rendering the page.
    $this->initPage($this->intl->t("Sign In"));
    $this->initBreadcrumb([[ $this->intl->rp("/users"), $this->intl->t("Users") ]]);
    $this->breadcrumb->ignoreQuery = true;

    $this->headingBefore = "<a class='btn btn-large btn-primary fr' href='{$this->intl->r("/profile/join")}'>{$this->intl->t(
      "Join {sitename}",
      [ "sitename" => $this->config->siteName ]
    )}</a>";

    $this->formAddElement(new InputEmail("email", $this->intl->t("Email Address"), $this->email, [
      "#help-text"  => "<a href='{$this->intl->r("/profile/reset-password")}'>{$this->intl->t("Forgot your password?")}</a>",
      "autofocus"   => true,
      "placeholder" => $this->intl->t("Enter your email address"),
      "required"    => true,
    ]));

    $this->formAddElement(new InputPassword("password", $this->intl->t("Password"), $this->rawPassword, [
      "placeholder" => $this->intl->t("Enter your password"),
      "required"    => true,
    ]));

    $this->formAddAction($this->intl->t("Sign In"), [ "class" => "btn btn-large btn-success" ]);

    $this->formInit([ "class" => "s s6 o3" ]);
  }

  /**
   * @inheritdoc
   */
  protected function getContent() {
    return "<div class='c'><div class='r'>{$this->formRender()}</div></div>";
  }

  /**
   * {@inheritdoc}
   *
   * @return this
   * @throws \MovLib\Presentation\Redirect\SeeOther
   */
  protected function formValid() {
    if ($session->authenticate($this->email, $this->rawPassword) === true) {
      $kernel->alerts .= new Alert(
        $this->intl->t("Successfully Signed In"),
        $this->intl->t("Welcome back {username}!", [ "username" => $session->userName ]),
        Alert::SEVERITY_SUCCESS
      );
      throw new SeeOther(!empty($_GET["redirect_to"]) ? $_GET["redirect_to"] : $this->intl->r("/my"));
    }

    $this->formInvalid($this->intl->t("We either don’t know the email address, or the password was wrong."));
    return $this;
  }

}
