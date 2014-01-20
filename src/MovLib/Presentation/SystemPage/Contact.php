<?php

/* !
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
namespace MovLib\Presentation\SystemPage;

use \MovLib\Presentation\Partial\Alert;
use \MovLib\Presentation\Partial\Form;
use \MovLib\Presentation\Partial\FormElement\InputHTML;
use \MovLib\Presentation\Partial\FormElement\InputSubmit;
use \MovLib\Presentation\Partial\FormElement\InputText;

/**
 * Contact page presentation.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Contact extends \MovLib\Presentation\Page {
  use \MovLib\Presentation\TraitSidebar;
  use \MovLib\Presentation\TraitFormPage;


  // ------------------------------------------------------------------------------------------------------------------- Properties
  
  
  /**
   * The email's subject.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputText
   */
  protected $subject;

  /**
   * The email's body.
   *
   * @var \MovLib\Presentation\Partial\FormElement\InputHTML
   */
  protected $message;
  

  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new system page presentation.
   * @global \MovLib\I18n $i18n
   */
  public function __construct() {
    global $i18n;
    $this->initPage($i18n->t("Contact"));
    $this->initBreadcrumb();
    $this->initLanguageLinks($i18n->t("/contact"));
    $this->initSidebar([
      [ $i18n->r("/team"), $i18n->t("Team") ],
      [ $i18n->r("/privacy-policy"), $i18n->t("Privacy Policy") ],
      [ $i18n->r("/terms-of-use"), $i18n->t("Terms of Use") ],
      [ $i18n->r("/association-statutes"), $i18n->t("Association Statutes") ],
      [ $i18n->r("/impressum"), $i18n->t("Impressum") ],
      [ $i18n->r("/contact"), $i18n->t("Contact") ]
    ]);
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function getPageContent(){
    global $i18n;

    $this->subject = new InputText("subject", $i18n->t("Subject"), [
      "placeholder" => $i18n->t("This will appear as subject of your message"),
      "required",
    ]);
    $this->message = new InputHTML("message", $i18n->t("Message"), null, []);
    $this->form = new Form($this, [ $this->subject, $this->message ]);
    $this->form->actionElements[] = new InputSubmit($i18n->t("Send"), [ "class" => "btn btn-success btn-large" ]);

    return 
      "<div class='c'><div class='r'><div class='s s10'>" .
      "<p>{$i18n->t("Thank you for your interest in contacting MovLib. Before proceeding, some important disclaimers:")}</p><ul>" .
      "<li>{$i18n->t("MovLib has no central editorial board; contributions are made by a large number of volunteers at their own discretion. Edits are not the responsibility of MovLib (the organisation that hosts the site) nor of its staff.")}</li>" .
      "<li>{$i18n->t("If you have questions about the concept of MovLib rather than a specific problem, the About MovLib page may help.")}</li>" .
      "</ul>{$this->form}</div></div></div>";
  }

  /**
   * @inheritdoc
   */
  protected function valid() {
    global $i18n;
    $this->alerts .= new Alert($i18n->t("Not implemented yet!"));
    return $this;
  }

}