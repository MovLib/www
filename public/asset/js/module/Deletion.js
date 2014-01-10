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

/* jshint browser:true */

/**
 * @module MovLib
 * @namespace modules
 * @submodule InputImage
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 * @param {window} window
 * @param {document} document
 * @param {MovLib} MovLib
 * @return {undefined}
 */
(function (window, document, MovLib) {
  "use strict";

  /**
   * The name of this JavaScript module.
   *
   * @type String
   */
  var moduleName = "Deletion";

  /**
   * Attach InputImage to the MovLib modules.
   *
   * @param {HTMLCollection} context
   *   The context we are currently working with.
   * @returns {MovLib}
   */
  MovLib.modules[moduleName] = function (context) {
    // The select element containing the deletion types.
    var reason = context.getElementById("reason");

    // The additional input elements for some deletion types.
    var infos = reason.form.getElementsByClassName("info");

    // Listen for the change event on the deletion type select element.
    reason.addEventListener("change", function () {
      // Try to get the info element for the selected deletion type.
      var info = context.getElementById("info-" + this.value);

      // Hide all other info elements and remove the required attribute.
      var c = infos.length;
      for (var i = 0; i < c; ++i) {
        infos[i].removeAttribute("required");
        infos[i].classList.add("hidden");
      }

      // Show the info element associated with this deletion type and make it required.
      if (info) {
        info.setAttribute("required", "required");
        info.classList.remove("hidden");
      }
    }, false);

    return MovLib;
  };

})(window, window.document, window.MovLib);
