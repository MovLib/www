<?php

/*
 *  This file is part of {@link https://github.com/MovLib MovLib}.
 *
 *  Copyright Â© 2013-present {@link http://movlib.org/ MovLib}.
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

namespace MovLib\Presentation\Person\Photo;

use \MovLib\Data\Person\Person;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputImage;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;

/**
 * Allows upload/edit of a person's photo.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright Â© 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Edit extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitForm;

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
   */
  public function __construct() {
    $session->checkAuthorization($this->intl->t("Only registered users are allowed to upload images."));
    $this->person = new Person($_SERVER["PERSON_ID"]);
    $this->initLanguageLinks("/person{0}/photo/upload", [ $this->person->id ]);

    if ($this->person->displayPhoto->imageExists) {
      $title     = $this->intl->t("Edit photo of {0}", [ $this->person->name ]);
      $pageTitle = $this->intl->t("Edit photo of {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
      $this->sidebarInit([
        [ $this->intl->r("/person/{0}/photo", [ $this->person->id ]), $this->intl->t("View"), [ "class" => "ico ico-view" ] ],
        [ $this->intl->r("/person/{0}/photo/edit", [ $this->person->id ]), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ],
        [ $this->intl->r("/person/{0}/photo/history", [ $this->person->id ]), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
        [ $this->intl->r("/person/{0}/photo/delete", [ $this->person->id ]), $this->intl->t("Delete"), [ "class" => "ico ico-delete" ] ],
      ]);
    }
    else {
      $title     = $this->intl->t("Upload photo for {0}", [ $this->person->name ]);
      $pageTitle = $this->intl->t("Upload photo for {0}", [ "<a href='{$this->person->route}'>{$this->person->name}</a>" ]);
    }
    $this->initPage($title);

    $this->initBreadcrumb([
      [ $this->intl->rp("/persons"), $this->intl->t("Persons") ],
      [$this->person->route, $this->person->name ],
      [ $this->intl->r("/person/{0}/photo", [ $this->person->id ]), $this->intl->t("Photo") ]
    ]);

    $this->inputImage = new InputImage("photo", $this->intl->t("Photo"), $this->person->displayPhoto);

    $this->inputDescription = new InputHTML(
      "description",
      $this->intl->t("Description"),
      $this->person->displayPhoto->description,
      [ "placeholder" => $this->intl->t("Please enter a detailed description of this photo."), "required" => "required" ]
    );

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
    // @todo: Remove alert and insert concrete content.
    return new \MovLib\Presentation\Partial\Alert($this->intl->t("The {0} feature isn’t implemented yet.", [ $this->intl->t("edit person photo") ]), $this->intl->t("Check back later"), \MovLib\Presentation\Partial\Alert::SEVERITY_INFO);
//    return $this->form;
  }

  /**
   * @inheritdoc
   */
  protected function valid() {

  }

}
