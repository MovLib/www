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
 * @module MovLib
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
   * @class MovLib
   * @constructor
   */
  function MovLib() {

    /**
     * Object to keep reference of already loaded modules.
     *
     * This is kept public because our modules export themselves, unlike you're used to from other systems.
     *
     * @property modules
     * @type Object
     */
    this.modules = {};

    /**
     * The global JavaScript settings object.
     *
     * @property settings
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
     * Initialize page features that are available on every page.
     *
     * The code within this method is only executed during the initial page load, not for any subsequent AJAX loads
     * (which aren't implemented yet but planned).
     *
     * @method init
     * @chainable
     * @return {MovLib}
     */
    init: function () {
      /**
       * A DOM element for testing.
       *
       * @type HTMLElement
       */
      var element = document.createElement("input");

      // Anonymous helper function to load polyfills.
      var load = function (name) {
        this.loadModule("//" + this.settings.domainStatic + "/asset/js/polyfill/" + name + ".js");
      };

      // Load cross-browser sham for classList support.
      if (!("classList" in document.documentElement)) {
        load.call(this, "classList");
      }

      // Extend our document with the most important sections.
      document.header = document.getElementById("header");
      document.main   = document.getElementById("main");
      document.footer = document.getElementById("footer");

      // Ensure focused elements don't hide themselve beneath our fixed header.
      document.body.addEventListener("focus", this.fixFocusScrollPosition, true);

      // Ensure that the viewport is properly scrolled to. This fixes a problem with the usage of autofocus in Gecko
      // (Firefox) browsers where it includes the hidden main navigation elements while computing the scroll top offset
      // for the current autofocus element. This is something I came up myself, no known workaround and a little known
      // problem. But there is a bug report at: https://bugzilla.mozilla.org/show_bug.cgi?id=712130
      //
      // @todo If this gets fixed in Gecko remove it (the fix for the reported bug might not fix our problem).
      window.onload = function () {
        var autofocusElement = document.querySelector("[autofocus]");
        if (autofocusElement) {
          // Enable autofocus support in older browsers while we're at it.
          autofocusElement.focus();

          // This is the actual Firefox hack. See called method for further details.
          this.fixFocusScrollPosition({ target: autofocusElement });
        }
      }.bind(this);

      // @todo Extend mega menu further for best accessability!
      //       - http://terrillthompson.com/blog/474
      //       - http://adobe-accessibility.github.io/Accessible-Mega-Menu/
      var expanders = document.getElementsByClassName("expander");
      var c         = expanders.length;
      for (var i = 0; i < c; ++i) {
        // Add focus class to expander if it is focused for CSS styling.
        expanders[i].addEventListener("focus", function () {
          this.classList.add("focus");
        }, false);

        // Remove focus class from expander.
        expanders[i].addEventListener("blur", function () {
          this.classList.remove("focus");
        }, false);

        // Capture blur events of children to determine if the complete expander lost focus.
        expanders[i].addEventListener("blur", function () {
          // Remove open class if no element is currently focused or if the currently focused element isn't one of our
          // children.
          var checkFocus = function () {
            if (!document.activeElement || !this.contains(document.activeElement)) {
              this.classList.remove("open");
            }
          };

          // Give the browser a millisecond grace time to change the focus state.
          // @todo Is a millisecond enough for all browsers?
          window.setTimeout(checkFocus.bind(this), 100);
        }, true);

        // React on certain keypress events as recommended by W3C's menu widget.
        // http://www.w3.org/TR/wai-aria-practices/#menu
        expanders[i].addEventListener("keypress", function (event) {
          switch (event.which || event.keyCode) {
            case 13: // Return / Enter
            case 32: // Space
            case 38: // Up Arrow
              if (event.target === this) {
                this.classList.add("open");
                this.getElementsByTagName("a")[0].focus();
              }
              break;

            case 27: // Escape
              this.classList.remove("open");
              this.focus();
              break;
          }
        }, false);

        // Allow mobile browsers to open the menu.
        expanders[i].getElementsByClassName("clicker").firstChild.addEventListener("click", function () {
          this.parentNode().classList.add("open");
        }, false);

        // Allow mobile browsers to close the menu.
        expanders[i].addEventListener("click", function (event) {
          if (event.target === this) {
            this.classList.remove("open");
          }
        }, true);
      }

      return this.execute(document);
    },

    /**
     * The MovLib module itself.
     *
     * The code within this method is executed on every page load, including subsequent AJAX loads.
     *
     * @method execute
     * @chainable
     * @param {HTMLCollection} context
     *   The context we are currently working with.
     * @returns {MovLib}
     */
    execute: function (context) {

      return this;
    },

    /**
     * Execute all given modules with the given context.
     *
     * Note that any module that wasn't loaded yet will be automatically loaded and executed.
     *
     * @method executeModules
     * @chainable
     * @param {Object} modules
     *   The modules to execute.
     * @param {HTMLCollection} context
     *   The context we are currently working with.
     * @return {MovLib}
     */
    executeModules: function (modules, context) {
      // The callback method if the module isn't loaded yet.
      var execute = function (module) {
        this.modules[module](context);
      };

      for (var module in modules) {
        if (!this.modules[module]) {
          this.loadModule(modules[module], execute.bind(this, module));
        }
        else {
          this.modules[module](context);
        }
      }

      return this;
    },

    /**
     * Fix scroll position on focus.
     *
     * Because we have a fixed header input elements might go beneath it if you tab through the page. Unfortunately
     * there is no way to fix this with a pure CSS solution for input elements. Input elements are empty an can't have
     * a :before of :after element and focus doesn't bubble in CSS. We fix this issue for all users who have JavaScript
     * enable and others have to scroll themselves.
     *
     * @method fixFocusScrollPosition
     * @returns {undefined}
     */
    fixFocusScrollPosition: function (event) {
      if (!document.header.contains(event.target)) {
        var boundingClientRect = event.target.getBoundingClientRect();
        if (boundingClientRect.top < boundingClientRect.height + 50) {
          window.scrollBy(0, -((boundingClientRect.top > 0 ? boundingClientRect.height : (boundingClientRect.top * -1 + boundingClientRect.height)) + 60));
        }
      }
    },

    /**
     * Asynchronously load the given module.
     *
     * @method loadModule
     * @chainable
     * @param {String} url
     *   The module's absolute URL (including scheme and hostname).
     * @param {Function} [onloadCallback]
     *   A function to call on the onload event of the inserted script tag.
     * @return {MovLib}
     */
    loadModule: function (url, onloadCallback) {
      var script    = document.createElement("script");
      script.async  = true;
      script.src    = url;
      script.onload = onloadCallback;
      document.body.appendChild(script);
      return this;
    }

  };

  // Instantiate and export the global MovLib object.
  window.MovLib = new MovLib();

})(window, window.document);
