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

/**
 * Provides properties and methods that are used by several system page presenters.
 *
 * @author Franz Torghele <ftorghele.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
trait SystemPageTrait {

  /**
   * Get sidebar items for a system page.
   *
   * @return array
   *   Array containing the sidebar items.
   */
  protected function getSidebarItems() {
    $sidebarItems = [
      [ $this->intl->r("/about"), $this->intl->t("About {sitename}", [ "sitename" => $this->config->sitename ]) ],
      [ $this->intl->r("/team"), $this->intl->t("Team") ],
      [ $this->intl->r("/privacy-policy"), $this->intl->t("Privacy Policy") ],
      [ $this->intl->r("/terms-of-use"), $this->intl->t("Terms of Use") ],
      [ $this->intl->r("/impressum"), $this->intl->t("Impressum") ],
      [ $this->intl->r("/contact"), $this->intl->t("Contact") ],
      [ $this->intl->r("/articles-of-association"), $this->intl->t("Articles of Association"), [ "class" => "separator"] ],
      [ $this->entity->r("/history", [ $this->entity->route->args ]), $this->intl->t("History"), [ "class" => "ico ico-history" ] ],
    ];
    if ($this->session->isAuthenticated && $this->session->isAdmin()) {
      $sidebarItems[] = [ $this->entity->r("/edit", [ $this->entity->route->args ]), $this->intl->t("Edit"), [ "class" => "ico ico-edit" ] ];
    }

    return $sidebarItems;
  }

}
