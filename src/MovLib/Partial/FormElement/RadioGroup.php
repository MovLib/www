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
namespace MovLib\Partial\FormElement;

/**
 * Fieldset with multiple input radio form elements.
 *
 * Use a radio group if your options will <b>never exceed 9 choices</b> and only a single choice is valid.
 *
 * @link http://www.w3.org/TR/wai-aria/roles#radiogroup
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/fieldset
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class RadioGroup extends \MovLib\Partial\FormElement\Select {

  /**
   * Get the radio group form element.
   *
   * @return string
   *   The radio group form element.
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd

      $options = null;
      foreach ($this->options as $value => $option) {
        $checked  = $this->value == $value ? " checked" : null;
        $options .=
          "<label class='radio inline'>" .
            "<input{$checked} name='{$this->id}' required type='radio' value='{$this->htmlEncode($value)}'>{$option}" .
          "</label>"
        ;
      }
      return
        "{$this->required}{$this->helpPopup}{$this->helpText}" .
        "<fieldset aria-expanded='true'{$this->expandTagAttributes($this->attributes)}>" .
          "<legend>{$this->label}</legend>{$options}" .
        "</fieldset>"
      ;

    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return (string) new \MovLib\Presentation\Partial\Alert("<pre>{$e}</pre>", "Error Rendering Element", \MovLib\Presentation\Partial\Alert::SEVERITY_ERROR);
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }

}
