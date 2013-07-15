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
 * @link https://github.com/harvesthq/chosen
 * @link https://github.com/silviomoreto/bootstrap-select
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
  var name = "MovLibSelect";

  /**
   * Default values for this module.
   *
   * @type Object
   */
  var defaults = {
    disableSearchThreshold: 10,
    maxSelectOptions: Infinity,
    allowSingleDeselect: true
  };

  function Plugin(element, options) {
    this.element = element;
    this.options = $.extend({}, defaults, options); // + element.data() ?
    this.init();
  };

  Plugin.prototype = {
    init: function() {

    }
  };

  // Register the jQuery plugin and protect against multiple instantiation.
  $.fn[name] = function (options) {
    return this.filter("select:not(.js-select)").each(function () {
      if (!$.data(this, name)) {
        $.data(this, name, new Plugin(this, options));
      }
    });
  };

  /**
   * Enhance all select elements within the current context.
   *
   * @param {Object} context
   *   The current context we are working with.
   * @returns {undefined}
   */
  MovLib.modules.select = function (context) {
    // Do not call the jQuery plugin, only adds overhead we do not need at this point.
    $("select:not(.js-select)", context).each(function () {
      $.data(this, name, new Plugin(this));
    });
  };

})(jQuery, document, MovLib, window);
