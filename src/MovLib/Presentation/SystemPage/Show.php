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

use \MovLib\Data\SystemPage;

/**
 * Single system page presentation.
 *
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class Show extends \MovLib\Presentation\AbstractPresenter {
  use \MovLib\Partial\SidebarTrait;


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * The system page to present.
   *
   * @var \MovLib\Data\SystemPage
   */
  protected $systemPage;


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * {@inheritdoc}
   */
  public function getContent() {
    return $this->htmlDecode($this->systemPage->text);
  }

  /**
   * {@inheritdoc}
   */
  public function init() {
    $this->systemPage = new SystemPage($this->diContainerHTTP, (integer) $_SERVER["SYSTEM_PAGE_ID"]);
    $this->initPage($this->systemPage->title);
    $this->initBreadcrumb();
    $this->initLanguageLinks($this->systemPage->route);
    $this->sidebarInit([
      [ $this->intl->r("/about-movlib"), $this->intl->t("About {sitename}", [ "sitename" => $this->config->sitename ]) ],
      [ $this->intl->r("/team"), $this->intl->t("Team") ],
      [ $this->intl->r("/privacy-policy"), $this->intl->t("Privacy Policy") ],
      [ $this->intl->r("/terms-of-use"), $this->intl->t("Terms of Use") ],
      [ $this->intl->r("/impressum"), $this->intl->t("Impressum") ],
      [ $this->intl->r("/contact"), $this->intl->t("Contact") ],
      [ $this->intl->r("/articles-of-association"), $this->intl->t("Articles of Association") ],
    ]);
  }

}
