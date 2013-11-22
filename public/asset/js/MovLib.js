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
 * The main file of the MovLib JavaScript framework that is loaded on every page load and takes care of loading any
 * additional modules.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
(function (window, document) {
  // The first thing we do is changing the id attribute of the html element, this is necessary for all our CSS styles.
  document.getElementsByTagName("html")[0].id = "js";

  /**
   * The global MovLib object.
   *
   * No need to wait for the DOM to be ready (and implement highly complicated code to do so) because our scripts are
   * always included directly above the closing body. This ensures that the DOM is actually already present on the client
   * side when this script is invoke. Makes life much easier and loading of scripts much more reliable.
   *
   * @returns {MovLib}
   */
  function MovLib() {

    /**
     * Object to keep reference of already loaded modules.
     *
     * This is kept public because our modules export themselves, unlike you're used to from other systems.
     *
     * @type Object
     */
    this.modules = {};

    /**
     * The global JavaScript settings object.
     *
     * @type Object
     */
    this.settings = JSON.parse(document.getElementById("js-settings").innerHTML);

    // Initialize all basic page features.
    this.init();

    // Load and execute all modules of this presentation if there are any.
    if (this.settings.modules) {
      this.executeModules(this.settings.modules, document);
    }
  }

  MovLib.prototype = {

    /**
     * Add an event listener to the given HTMLElement.
     *
     * @param {HTMLElement} element
     *   The element on which we should listen for events.
     * @param {Object} events
     *   Object where the key is the event to listen for and the value the desired callback function.
     * @returns {MovLib}
     */
    bind: function (element, events) {
      for (var event in events) {
        if (typeof event === "string") {
          element.addEventListener(event, events[event], false);
        }
      }
      return this;
    },

    init: function () {
      // Anon helper function to load polyfills.
      var load = function (name) {
        this.loadModule(name, "//" + this.settings.domainStatic + "/asset/js/polyfill/" + name + ".js");
      };

      // Load cross-browser sham for classList support.
      if (!("classList" in document.documentElement)) {
        load.call(this, "classList");
      }
    },

    /**
     * Execute all given modules with the given context.
     *
     * Note that any module that wasn't loaded yet will be automatically loaded and executed.
     *
     * @param {Object} modules
     *   The modules to execute.
     * @param {HTMLCollection} context
     *   The context we are currently working with.
     * @returns {MovLib}
     */
    executeModules: function (modules, context) {
      // The callback method if the module isn't loaded yet.
      var execute = function (module) {
        this.modules[module](context);
      };

      for (var module in modules) {
        if (!this.modules[module]) {
          this.loadModule(module, modules[module], execute.bind(this, module));
        }
        else {
          this.modules[module](context);
        }
      }

      return this;
    },

    /**
     * Asynchronously load the given module.
     *
     * @param {String} name
     *   The module's name.
     * @param {String} url
     *   The module's absolute URL (including scheme and hostname).
     * @param {Function} onloadCallback [optional]
     *   A function to call on the onload event of the inserted script tag.
     * @returns {MovLib}
     */
    loadModule: function (name, url, onloadCallback) {
      if (!this.modules[name]) {
        var script    = document.createElement("script");
        script.async  = true;
        script.src    = url;
        script.onload = onloadCallback;
        document.body.appendChild(script);
      }
      return this;
    }

  };

  // Instantiate and export the global MovLib object.
  window.MovLib = new MovLib();

})(window, window.document);
