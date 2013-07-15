/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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

/* jshint browser:true, jquery:true */
/* global MovLib:true, use:true */

/**
 * Nice select input elements that fit our flat design and extend usability.
 *
 * @link http://twitter.github.com/bootstrap/javascript.html#buttons
 *   This MovLib module and jQuery plugin is based on the Bootstrap buttons code.
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 *
 * @param {jQuery} $
 *   The global jQuery object.
 * @param {document} document
 *   The global document object.
 * @param {MovLib} MovLib
 *   The global MovLib object.
 * @param {window} window
 *   The global window object.
 * @param {undefined} undefined
 *   In older engines (ES5-) undefined is mutable, we ensure that undefined is really undefined with this parameter.
 * @returns {undefined}
 */
(function ($, document, MovLib, window, undefined) {
  "use strict";

  /**
   * The name of the jQuery plugin.
   *
   * @type String
   */
  var name = "MovLibButton";

  /**
   * Instantiate new button.
   *
   * @param {Object} $element
   * @returns {Plugin}
   */
  function Plugin($element) {
    this.$element = $element;
  };

  Plugin.prototype = {

    /**
     * Set state of a button.
     *
     * @param {String} state
     *   The new state of the button. Possible values are: "disabled", "loading" and "reset"
     * @returns {undefined}
     */
    setState: function (state) {
      var $element = this.$element;
      var data = $element.data();
      var value = $element.is("input") ? "val" : "html";
      state = state + "Text";
      if (data.resetText && data[state]) {
        $element.data("resetText", $element[value]())[value](data[state]);
        // Push to event loop to allow forms to submit.
        setTimeout(function () {
          $element.prop("disabled", (state === "loadingText"));
        }, 0);
      }
    },

    /**
     * Toggle active state of button.
     *
     * @returns {undefined}
     */
    toggle: function () {
      this.$element.toggleClass("active");
    }

  };

  /**
   * Instantiate button on selected elements or execute button method on element.
   *
   * @param {String} action
   *   [Optional] The method to execute (e.g. "setState", "toggle").
   * @returns {jQuery}
   */
  $.fn[name] = function (action) {
    return this.filter(".button").each(function () {
      var data = $.data(this, name);
      if (!data) {
        $.data(this, name, (data = new Plugin(this, action)));
      }
      if (action === "toggle") {
        data.toggle();
      } else if (action) {
        data.setState(action);
      }
    });
  };

  // Keep track of any buttons which have the data-toggle attribute set.
  $(document).on("click." + name, ".button.js-toggle", function () {
    $(this).MovLibButton("toggle");
  });

  /**
   * Empty module, nothing to do here.
   */
  MovLib.modules.button = {};

})(jQuery, document, MovLib, window);
