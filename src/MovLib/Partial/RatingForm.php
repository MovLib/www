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
namespace MovLib\Partial;

use \MovLib\Data\User\User;
use \MovLib\Partial\Form;

/**
 * Defines the rating form.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class RatingForm extends \MovLib\Core\Presentation\DependencyInjectionBase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The form's entity to rate.
   *
   * @var \MovLib\Data\AbstractEntity
   */
  protected $entity;

  /**
   * The form for the rating.
   *
   * @var \MovLib\Partial\Form
   */
  protected $form;

  /**
   * The star buttons for the rating form.
   *
   * @var string
   */
  protected $starButtons;

  /**
   * The summary of the current ratingVotes.
   *
   * @var string
   */
  protected $summary;

  /**
   * The currently signed in user's rating for this entity.
   *
   * @var integer|null
   */
  protected $userRating;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new rating form.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param \MovLib\Data\AbstractEntity $entity
   *   The entity for which the rating form should be created.
   * @param array $attributes [optional]
   *   Additional attributes that should be applied to the <code><form></code> tag.
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, \MovLib\Data\RatingInterface $entity, array $attributes = []) {
    parent::__construct($diContainerHTTP);
    // @devStart
    // @codeCoverageIgnoreStart
    // Yes, we could create an interface with getters, but you know, we don't like those slow getters...
    assert(property_exists($entity, "ratingMean"), "Your entity needs to contain a \$ratingMean property (use the RatingTrait)!");
    assert(property_exists($entity, "ratingVotes"), "Your entity needs to contain a \$ratingVotes property (use the RatingTrait)!");
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Get the currently signed in user's rating for this entity.
    if ($this->session->isAuthenticated) {
      $this->userRating = (new User($this->diContainerHTTP))->getRating($entity, $this->session->userId);
    }

    // Build the buttons for the form.
    $this->starButtons = null;
    for ($i = 1; $i < 6; ++$i) {
      $rated  = $i <= $this->userRating ? " class='rated'" : null;
      $title  = $this->intl->t("{0,plural,=1{Awful}=2{Bad}=3{Okay}=4{Fine}=5{Awesome}other{Unknown}}", $i);
      $this->starButtons .=
        "<button{$rated} name='rating' type='submit' value='{$i}' title='{$title}'>" .
          "<span class='vh'>{$this->intl->t("Rate with {0,plural,one{one star} other{# stars}}.", $i)}</span>" .
        "</button>"
      ;
    }

    // Build an explanation based on available rating data. We can't use Intl ICU plural forms here because we have to
    // enclose the various numeric values in structured data and want the correctly formatted too.
    if ($entity->ratingVotes === 0) {
      $this->summary = $this->intl->t("No one has rated so far, be the first.");
    }
    elseif ($entity->ratingVotes === 1 && $this->userRating !== null) {
      $this->summary = $this->intl->t("You’re the only one who rated yet.");
    }
    else {
      $this->summary = $this->intl->t(
        "Rated by {0,plural,=1{{ratingVotes} user} other{{ratingVotes} users}} with {0,plural,=1{{rating}} other{a {1}mean rating{2} of {rating}}}.",
        [
          $entity->ratingVotes,
          "<a href='{$entity->rp("/ratings")}' title='{$this->intl->t("View the rating demographics.")}'>",
          "</a>",
          "ratingVotes"  => "<span property='ratingCount'>{$this->intl->format("{0,number}", $entity->ratingVotes)}</span>",
          "rating" => "<span property='ratingValue'>{$this->intl->format("{0,number}", $entity->ratingMean)}</span>",
        ]
      );
    }

    $this->entity = $entity;
    $this->diContainerHTTP->presenter->addClass("star-rating", $attributes);
    $this->form   = new Form($diContainerHTTP, $attributes, "stars-rating-{$entity->id}");
    $this->form->init(null, [ $this, "validate" ]);
  }

  /**
   * Get the rating form's string representation.
   *
   * @return string
   *   The rating form's string representation.
   */
  public function __toString() {
    return
      "{$this->form->open()}<fieldset>" .
        "<legend class='vh'>{$this->intl->t("Rate this show")}</legend>" .
        "<div aria-hidden='true' class='back'><span></span><span></span><span></span><span></span><span></span></div>" .
        "<div class='front'>{$this->starButtons}</div>" .
      "</fieldset>{$this->form->close()}" .
      "<small property='aggregateRating' typeof='AggregateRating'>{$this->summary}</small>";
    ;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the submitted form.
   *
   * @param array $errors
   *   Possibly found errors, we have no form elements therefor it's always empty.
   * @return array
   *   Possibly found errors.
   */
  public function validate($errors) {
    if ($this->session->isAuthenticated === false) {
      $errors[] = $this->intl->t(
        "Please {1}sign in{0} or {2}join{0} to rate.",
        [ "</a>", "<a href='{$this->intl->r("/profile/sign-in")}'>", "<a href='{$this->intl->r("/profile/join")}'>" ]
      );
    }
    elseif (($rating = $this->request->filterInput(INPUT_POST, "rating", FILTER_VALIDATE_INT, [ "options" => [ "min_range" => 1, "max_range" => 5 ]]))) {
      $this->entity->rate($rating, $this->session->userId);
    }
    else {
      $errors[] = $this->intl->t(
        "The submitted rating isn’t valid. Valid ratings range from {min} to {max}.",
        [ "min" => 1, "max" => 5 ]
      );
    }
    return $errors;
  }

}
