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
namespace MovLib\Presentation\Award;

use \MovLib\Data\Award\Award;
use \MovLib\Partial\Date;
use \MovLib\Partial\QuickInfo;

/**
 * Defines the award show presentation.
 *
 * @link http://schema.org/Organization
 * @link http://www.google.com/webmasters/tools/richsnippets?q=https://en.movlib.org/award/{id}
 * @link http://www.w3.org/2012/pyRdfa/extract?validate=yes&uri=https://en.movlib.org/award/{id}
 * @link http://validator.w3.org/check?uri=https://en.movlib.org/award/{id}
 * @link http://gsnedders.html5.org/outliner/process.py?url=https://en.movlib.org/award/{id}
 *
 * @property \MovLib\Data\Award\Award $entity
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class Show extends \MovLib\Presentation\AbstractShowPresenter {
  use \MovLib\Presentation\Award\AwardTrait;
  use \MovLib\Partial\ContentSectionTrait;

  /**
   * {@inheritdoc}
   */
  public function init() {
    return $this->initShow(new Award($this->diContainerHTTP, $_SERVER["AWARD_ID"]), "Organization");
  }

  /**
   * {@inheritdoc}
   */
  public function getContent() {
    $this->headingBefore = "<div class='r'><div class='s s10'>";

    $infos = new QuickInfo($this->intl);
    $this->entity->links          && $infos->add($this->intl->t("Sites"), $this->formatWeblinks($this->entity->links));
    $this->entity->firstEventYear && $infos->add($this->intl->t("First Event"), (new Date($this->intl, $this))->format($this->entity->firstEventYear));
    $this->entity->lastEventYear  && $infos->add($this->intl->t("Last Event"), (new Date($this->intl, $this))->format($this->entity->lastEventYear));
    $this->entity->wikipedia      && $infos->addWikipedia($this->entity->wikipedia);

    $this->headingAfter .= "{$infos}</div><div class='s s2'><img alt='' src='{$this->fs->getExternalURL("asset://img/logo/vector.svg")}' width='140' height='140'></div></div>";

    $this->entity->description && $this->addContentSection($this->intl->t("Description"), $this->entity->description);
    $this->entity->aliases     && $this->addContentSection($this->intl->t("Also Known As"), $this->formatAliases($this->entity->aliases), false);
    if (($content = $this->getContentSections())) {
      return $content;
    }

    return new Alert(
      "<p>{$this->intl->t("{sitename} doesn’t have further details about this award.", [ "sitename" => $this->config->sitename ])}</p>" .
      "<p>{$this->intl->t("Would you like to {0}add additional information{1}?", [ "<a href='{$this->intl->r("/award/{0}/edit", $this->entity->id)}'>", "</a>" ])}</p>",
      $this->intl->t("No Info")
    );
  }

}
