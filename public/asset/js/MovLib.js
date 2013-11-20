/*!
 * This file is part of {@link Expression prj is undefined on line 2, column 48 in Templates/Licenses/license-movlib.txt. Expression prj is undefined on line 2, column 66 in Templates/Licenses/license-movlib.txt.}.
 *
 * Copyright © 2013-present {@link Expression prj is undefined on line 4, column 52 in Templates/Licenses/license-movlib.txt. Expression prj is undefined on line 4, column 67 in Templates/Licenses/license-movlib.txt.}.
 *
 * Expression prj is undefined on line 6, column 20 in Templates/Licenses/license-movlib.txt. is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * Expression prj is undefined on line 10, column 20 in Templates/Licenses/license-movlib.txt. is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with Expression prj is undefined on line 13, column 104 in Templates/Licenses/license-movlib.txt..
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */

/* jshint browser:true, globalstrict:true */
/* global MovLib:true */

/**
 * The main file of the MovLib JavaScript framework that is loaded on every page load and takes care of loading any
 * additional modules.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */

// We enforce strict behaviour in all our modules and we aren't using any third-party scripts.
"use strict";

/**
 * The global MovLib object.
 *
 * @class
 * @type MovLib
 */
function MovLib() {
  // We only extract the body element once from the DOM, this isn't going to be removed at any point.
  this.bodyTag = document.getElementsByTagName("body")[0];

  // Object to keep reference of already loaded modules.
  this.modules = {};

  // Directly parse the document supplied settings on initialization.
  this.settings = JSON.parse(document.getElementById("js-settings").innerHTML);

  // Initialize all basic page features.
  this.init();

  // Execute all modules of this presentation if there are any.
  if (this.settings.modules) {
    this.loadAndExecuteModules(this.settings.modules);
  }
}

MovLib.prototype = {

  init: function () {

  },

  /**
   * Load and execute all given modules.
   *
   * @param {Object} modules
   *   Object containing all modules that should be loaded, where the key is the module's name and the value the
   *   absolute URL to the module's file.
   * @param {Object} context [optional]
   *   The context that should be passed to the module on execution. Defaults to the complete document.
   * @returns {MovLib}
   */
  loadAndExecuteModules: function (modules, context) {
    var moduleOnload = function () {
      self.modules[this.module](context || document);
    };
    var self = this;
    var script;

    for (var module in modules) {
      if (!this.modules[module]) {
        script        = document.createElement("script");
        script.module = module;
        script.src    = modules[module];
        script.onload = moduleOnload;
        this.body.appendChild(script);
      }
      else {
        this.modules[module](context || document);
      }
    }

    return this;
  }

};
