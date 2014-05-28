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

use \MovLib\Exception\RedirectException\SeeOtherException;

/**
 * Defines the dashboard presenter object.
 *
 * @routeCache false
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Dashboard extends \MovLib\Presentation\AbstractPresenter {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "Dashboard";
  // @codingStandardsIgnoreEnd

  /**
   * {@inheritdoc}
   */
  public function init() {
    if ($this->session->isAuthenticated === false) {
      throw new SeeOtherException("/");
    }
    $this->initPage($this->intl->t("Dashboard"));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return str_replace(
      "{sitename}",
      $this->config->sitename,
      "<div class='c'><div class='r'><div class='s s12'><p>{$this->intl->t(
        "{sitename} is now open for beta testers and we are very grateful that you’ve taken this opportunity and " .
        "decided to join us. Our goal is pretty simple, create the best open and free online movie database in the " .
        "world. But we need your help to reach this goal."
      )}</p><p>{$this->intl->t(
        "If you’d like to find out more about {sitename} head over to our {0}about page{1}.",
        [ "<a href='{$this->intl->r("/about")}'>", "</a>" ]
      )}</p><h2>{$this->intl->t("{sitename} is {0}beta software{1}", [ "<strong>", "</strong>" ])}</h2><p>{$this->intl->t(
        "Expect errors and missing features all over the place. {sitename} is still in a very early development " .
        "stage. You’re one of the first persons to ever fiddle around with it. But that’s a good thing, because…"
      )}</p><h2>{$this->intl->t("{sitename} is {0}built by you{1}", [ "<strong>", "</strong>" ])}</h2><p>{$this->intl->t(
        "We (the {0}core developers{1}) worked on the {sitename} software for almost two years now (and invested an " .
        "estimated {2}10 person-years of effort{1}), we started from scratch, without any frameworks, without " .
        "anything. We wanted to create a unique highly customized and specialized software that has only one purpose: " .
        "being an awesome foundation for a sophisticated online movie database software. Although we think that we’re " .
        "on the right track with that, we need your help to verify that. We need your opinions as movie and series " .
        "experts, professionals and collectors. Many websites are built without asking the audience, the owners and " .
        "developers simply build what they think is right for their users. We don’t want to make that mistake, we " .
        "want to build {sitename} together with you, so that you end up with exactly the database you need for your " .
        "daily dose of movie related structured data, or your own website.",
        [ "<a href='{$this->intl->r("/team")}'>", "</a>", "<a target='_blank' href='https://www.ohloh.net/p/movlib/estimated_cost'>" ]
      )}</p><h2>{$this->intl->t("{sitename} is {0}open source{1}", [ "<strong>", "</strong>" ])}</h2><p>{$this->intl->t(
        "And like almost all open source projects developed by people in their spare time. We don’t have a 24/7 " .
        "support team, we aren’t baked by a multi-million dollar company. This means development will be slow compared " .
        "to big commercial projects. We have to go to work each day and take care of other things. If you have any " .
        "talent that might be useful for {sitename} and you’d like to support us, simply {0}get in touch{1}. It " .
        "doesn’t matter what you might be good at. Maybe you’re a passioned designer, marketer, or a developer " .
        "yourself. Doesn’t matter, {sitename} needs the help of every person who is interested in the topic to reach " .
        "our goal.",
        [ "<a href='{$this->intl->r("/contact")}'>", "</a>" ]
      )}</p></div></div></div>"
    );
  }

}
