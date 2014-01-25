<?php

/*
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright © 2013-present {@link http://movlib.org/ MovLib}.
 *
 *  MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 *  License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 *  version.
 *
 *  MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 *  of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License along with MovLib.
 *  If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

namespace MovLib\Presentation\Person;

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * Allows upload/edit of a person's photo.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class PhotoEdit extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitFormPage;

  /**
   * The image input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputImage
   */
  protected $inputImage;

  /**
   * The description input.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $inputDescription;

  /**
   * The person to upload/edit the photo for.
   *
   * @var \MovLib\Data\Person\Person
   */
  protected $person;

  /**
   * Instantiate new Photo Edit presentation.
   *
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Data\User\Session $session
   */
  public function __construct() {
    global $i18n, $session;
    $session->checkAuthorization($i18n->t("Only registered users are allowed to upload images."));
    $this->person = new Person($_SERVER["PERSON_ID"]);
    $this->initLanguageLinks("/person{0}/photo/upload", [ $this->person->id ]);

    if ($this->person->displayPhoto->imageExists) {
      $title     = $i18n->t("Edit photo of {0}", [ $this->person->name ]);
      $pageTitle = $i18n->t("Edit photo of {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
      $this->initSidebar([
        [ $this->person->route, $i18n->t("Back to Person"), [ "class" => "ico ico-person" ] ],
        [ $i18n->r("/person/{0}/photo", [ $this->person->id ]), $i18n->t("View"), [ "class" => "ico ico-view" ] ],
        [ $i18n->r("/person/{0}/photo/edit", [ $this->person->id ]), $i18n->t("Edit"), [ "class" => "ico ico-edit" ] ],
        [ $i18n->r("/person/{0}/photo/history", [ $this->person->id ]), $i18n->t("History"), [ "class" => "ico ico-history" ] ],
        [ $i18n->r("/person/{0}/photo/delete", [ $this->person->id ]), $i18n->t("Delete"), [ "class" => "ico ico-delete" ] ],
      ]);
    }
    else {
      $title     = $i18n->t("Upload photo for {0}", [ $this->person->name ]);
      $pageTitle = $i18n->t("Upload photo for {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
    }
    $this->initPage($title);

    $this->initBreadcrumb([
      [ $i18n->rp("/persons"), $i18n->t("Persons") ],
      [$this->person->route, $this->person->name ],
      [ $i18n->r("/person/{0}/photo", [ $this->person->id ]), $i18n->t("Photo") ]
    ]);

    $this->inputImage = new InputImage("photo", $i18n->t("Photo"), $this->person->displayPhoto);

    $this->inputDescription = new InputHTML("description", $i18n->t("Description"), $this->person->displayPhoto->description, [ "required" => "required" ]);

    $this->form = new Form($this, [
      $this->inputImage,
      $this->inputDescription,
    ]);
    $this->form->actionElements[] = new InputSubmit("Submit", [ "class" => "btn btn-large btn-success"]);
  }

  /**
   * @inheritdoc
   */
  protected function getPageContent() {
    return $this->form;
  }

  /**
   * @inheritdoc
   */
  protected function valid() {

  }

}
