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
  document.documentElement.id = "js";

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
     * <code>TRUE</code> if current browser is Mozilla Firefox (any version).
     *
     * @link http://stackoverflow.com/a/9851769/1251219
     * @property browserFirefox
     * @type Boolean
     */
    this.browserFirefox = typeof InstallTrigger !== "undefined";

    /**
     * <code>TRUE</code> if current browser is Opera (8.0 - 15.0).
     *
     * @link http://stackoverflow.com/a/9851769/1251219
     * @property browserOpera
     * @type Boolean
     */
    this.browserOpera = !!window.opera || navigator.userAgent.indexOf(" OPR/") >= 0;

    /**
     * <code>TRUE</code> if current browser is Google Chrome (any version).
     *
     * @link http://stackoverflow.com/a/9851769/1251219
     * @property browserChrome
     * @type Boolean
     */
    this.browserChrome = !!window.chrome && !this.browserOpera;

    /**
     * <code>TRUE</code> if current browser is Apple Safari (any version).
     *
     * @link http://stackoverflow.com/a/9851769/1251219
     * @property browserSafari
     * @type Boolean
     */
    this.browserSafari = Object.prototype.toString.call(window.HTMLElement).indexOf("Constructor") > 0;

    /**
     * <code>TRUE</code> if current browser is Microsoft Internet Explorer (any version).
     *
     * @link http://stackoverflow.com/a/9851769/1251219
     * @property browserIE
     * @type Boolean
     */
    this.browserIE = /*@cc_on!@*/false || !!document.documentMode;

    /**
     * <code>TRUE</code> if current browser is Microsoft Internet Explorer 9.
     *
     * @property browserIE9
     * @type Boolean
     */
    this.browserIE9 = document.documentElement.className === "ie9";

    /**
     * Object to keep reference of already loaded modules.
     *
     * This is kept public because our modules export themselves, unlike you're used to from other systems.
     *
     * @property modules
     * @type Object
     * @default {}
     */
    this.modules = {};

    /**
     * The global JavaScript settings object.
     *
     * @property settings
     * @type Object
     * @default { domainStatic: "movlib.org" }
     */
    this.settings = JSON.parse(document.getElementById("jss").innerHTML);

    // Initialize all basic page features.
    this.init();

    // Load and execute all modules of this presentation if there are any.
    if (this.settings.modules) {
      this.executeModules(this.settings.modules, document);
    }
  }

  MovLib.prototype = {

    /**
     * The MovLib module itself.
     *
     * The code within this method is executed on every page load, including subsequent AJAX loads.
     *
     * @method execute
     * @chainable
     * @param {HTMLCollection} context The context we are currently working with.
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
     * @todo We shouldn't call <code>.bind()</code> within a loop!
     * @method executeModules
     * @chainable
     * @param {Object} modules The modules to execute.
     * @param {HTMLCollection} context The context we are currently working with.
     * @return {MovLib}
     */
    executeModules: function (modules, context) {
      // The callback method if the module isn't loaded yet.
      this.executeModules.execute = this.executeModules.execute || function (module) {
        this.modules[module](context);
      };

      for (var module in modules) {
        if (this.modules[module]) {
          this.modules[module](context);
        }
        else {
          this.loadModule(modules[module], this.executeModules.execute.bind(this, module));
        }
      }

      return this;
    },

    /**
     * Get callout.
     *
     * Please note that the callout has the CSS class `"hide"` and you have to add the class `"show"` after you inserted
     * the element into the DOM.
     *
     * @method getCallout
     * @param {String} message The already translated callout's message.
     * @param {String} [title] The already translated callout's title.
     * @param {String} [severity] The callout's severity level, one of `"error"`, `"info"`, or `"success"`.
     * @param {Object} [attributes={}] Additional attributes that should be applied to the callout.
     * @returns {HTMLElement} The callout.
     */
    getCallout: function (message, title, severity, attributes) {
      var callout = document.createElement("div");
      if (attributes) {
        for (var attribute in attributes) {
          callout.setAttribute(attribute, attributes[attribute]);
        }
      }
      callout.classList.add("callout", "hide");
      if (severity) {
        callout.classList.add("callout-" + severity);
      }
      callout.innerHTML = "<div class='c'>" + (title || "") + message + "</div>";
      return callout;
    },


    /**
     * Escape special HTML characters like PHP's equivalent function.
     *
     * @link https://github.com/kvz/phpjs/blob/master/functions/strings/htmlspecialchars.js
     *
     * @param {String} string
     *   The string to escape.
     * @param {String|Array} quote_style
     *   The quote style to use.
     * @param {null} charset
     *   Not supported
     * @param {boolean} double_encode
     *   Whether to do double encoding or not.
     * @returns {String}
     *   The escaped HTML string.
     */
    htmlspecialchars: function (string, quote_style, charset, double_encode) {
      // discuss at: http://phpjs.org/functions/htmlspecialchars/
      // original by: Mirek Slugen
      // improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // bugfixed by: Nathan
      // bugfixed by: Arno
      // bugfixed by: Brett Zamir (http://brett-zamir.me)
      // bugfixed by: Brett Zamir (http://brett-zamir.me)
      // revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
      // input by: Ratheous
      // input by: Mailfaker (http://www.weedem.fr/)
      // input by: felix
      // reimplemented by: Brett Zamir (http://brett-zamir.me)
      // note: charset argument not supported
      // example 1: htmlspecialchars("<a href='test'>Test</a>", 'ENT_QUOTES');
      // returns 1: '&lt;a href=&#039;test&#039;&gt;Test&lt;/a&gt;'
      // example 2: htmlspecialchars("ab\"c'd", ['ENT_NOQUOTES', 'ENT_QUOTES']);
      // returns 2: 'ab"c&#039;d'
      // example 3: htmlspecialchars('my "&entity;" is still here', null, null, false);
      // returns 3: 'my &quot;&entity;&quot; is still here'

      var optTemp = 0,
        i = 0,
        noquotes = false;
      if (typeof quote_style === 'undefined' || quote_style === null) {
        quote_style = 2;
      }
      string = string.toString();
      if (double_encode !== false) {
        // Put this first to avoid double-encoding
        string = string.replace(/&/g, '&amp;');
      }
      string = string.replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');

      var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
      };
      if (quote_style === 0) {
        noquotes = true;
      }
      if (typeof quote_style !== 'number') {
        // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
          // Resolve string input to bitwise e.g. 'ENT_IGNORE' becomes 4
          if (OPTS[quote_style[i]] === 0) {
            noquotes = true;
          } else if (OPTS[quote_style[i]]) {
            optTemp = optTemp | OPTS[quote_style[i]];
          }
        }
        quote_style = optTemp;
      }
      if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/'/g, '&#039;');
      }
      if (!noquotes) {
        string = string.replace(/"/g, '&quot;');
      }

      return string;
    },

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
      // Load cross-browser sham for classList support.
      if (!("classList" in document.documentElement)) {
        this.loadModule("//" + this.settings.domainStatic + "/asset/js/poly/classList.js");
      }

      // Extend our document with the most important sections.
      document.header = document.getElementById("h");
      document.main   = document.getElementById("m");
      document.footer = document.getElementById("f");

      // Fix scroll position on focus.
      //
      // Because we have a fixed header input elements might go beneath it if you tab through the page. Unfortunately
      // there is no way to fix this with a pure CSS solution for input elements. Input elements are empty an can't have
      // a :before of :after element and focus doesn't bubble in CSS. We fix this issue for all users who have JavaScript
      // enable and others have to scroll themselves.
      document.body.addEventListener("focus", function (event) {
        if (document.header && !document.header.contains(event.target)) {
          var boundingClientRect = event.target.getBoundingClientRect();
          if (boundingClientRect.bottom < 110) {
            window.scrollBy(0, -((boundingClientRect.top > 0 ? boundingClientRect.height : (boundingClientRect.top * -1 + boundingClientRect.height)) + 60));
          }
        }
      }, true);

      // Fix Firefox autofocus bug: https://bugzilla.mozilla.org/show_bug.cgi?id=712130
      if (this.browserFirefox) {
        var autofocus = document.querySelector("[autofocus]");
        if (autofocus) {
          autofocus.blur();
          window.onload = function () {
            this.scroll(0, 0);
            autofocus.focus();
          };
        }
      }

      // Only bind header events if we actually have a header.
      if (document.header) {
        // Check if there is any active element and if there is check if it's a child of the currently opened navigation.
        // If none of both is true remove the open class and close the navigation.
        var checkFocus = function () {
          if (!document.activeElement || !this.contains(document.activeElement)) {
            this.classList.remove("open");
          }
        };

        // Wait 100 milliseconds and allow the browser to change the active element, afterwards check focus.
        var expanderCapturingBlur = function () {
          window.setTimeout(checkFocus.bind(this), 100);
        };

        // Extend our mega menu with the W3C recommended keyboard shortcuts for accessibility.
        // @see http://www.w3.org/TR/wai-aria-practices/#menu
        var expanderKeypress = function (event) {
          switch (event.which || event.keyCode) {
            case 13: // Return / Enter
            case 32: // Space
            case 38: // Up Arrow
              if (event.target === this) {
                event.preventDefault();
                event.returnValue = false;
                this.classList.add("open");

                // We have to ensure that the first anchor is actually visible before the transition finished in order to
                // give it focus right away.
                var a = this.getElementsByTagName("a")[0];
                if (a) {
                  a.focus();
                }
              }
              break;

            case 27: // Escape
              event.preventDefault();
              event.returnValue = false;
              this.classList.remove("open");
              this.focus();
              break;
          }
        };

        // Enable mobile users to close the menu via click.
        var expanderClose = function () {
          this.classList.remove("open");
        };

        // Enable mobile users to open the menu via click.
        var clickerClick = function () {
          this.parentNode.classList.add("open");
        };

        // Bind all events
        // @todo Extend mega menu further for best accessibility!
        //       - http://terrillthompson.com/blog/474
        //       - http://adobe-accessibility.github.io/Accessible-Mega-Menu/
        var expanders = document.header.getElementsByClassName("expander");
        var c         = expanders.length;
        for (var i = 0; i < c; ++i) {
          this.toggleFocusClass(expanders[i], false);
          expanders[i].addEventListener("blur", expanderCapturingBlur, true);
          expanders[i].addEventListener("keypress", expanderKeypress, false);
          expanders[i].getElementsByClassName("clicker")[0].addEventListener("click", clickerClick, false);
          expanders[i].addEventListener("click", expanderClose, true);
          expanders[i].addEventListener("mouseout", expanderClose, false);
        }

        var languageSelector = document.getElementById("f-language");
        if (languageSelector) {
          languageSelector.addEventListener("keypress", expanderKeypress.bind(languageSelector.parentNode.children[0]), false);
        }

        var search = document.getElementById("s");
        var searchQuery = search.children.q;

        function searchQueryChange() {
          if (searchQuery.value === "") {
            search.classList.remove("focus");
          }
          else {
            search.classList.add("focus");
          }
        }

        searchQuery.addEventListener("change", searchQueryChange);
        searchQueryChange();
      }

      return this.execute(document);
    },

    /**
     * Invalidate a form element and display the browser generated error message to the user.
     *
     * @param {HTMLElement} element The HTML element to invalidate.
     * @param {String} message The translated custom error message to display.
     * @returns {MovLib}
     */
    invalidate: function (element, message) {
      // Callback for the change event on the element.
      this.invalidate.reset = this.invalidate.reset || function () {
        element.setCustomValidity("");
        element.removeEventListener("change", this.invalidate.reset, false);
      }.bind(this);

      // Remove possible HTML from message and set the custom validity error message on the element.
      var stripTags       = document.createElement("div");
      stripTags.innerHTML = message;
      element.setCustomValidity((stripTags.textContent || stripTags.innerText));

      // Only submit if the submit function of the form is masked by a submit input element, otherwise the onsubmit
      // event isn't fired and the form is really submitted (and we only want to show the validity error message).
      if (element.form.submit instanceof HTMLElement) {
        element.form.submit.click();
      }

      // Observe any changes to this input field and reset the validity error message.
      element.addEventListener("change", this.invalidate.reset, false);

      return this;
    },

    /**
     * Asynchronously load the given module.
     *
     * @method loadModule
     * @param {String} url The module's absolute URL (including scheme and hostname).
     * @param {Function} [onloadCallback] A function to call on the onload event of the inserted script tag.
     * @return {MovLib}
     */
    loadModule: function (url, onloadCallback) {
      var script    = document.createElement("script");
      script.async  = true;
      script.src    = url;
      script.onload = onloadCallback;
      document.body.appendChild(script);
      return this;
    },

    /**
     * Toggle <code>.focus</code> CSS class on <code>"focus"</code> and <code>"blur"</code> events.
     *
     * @method toggleFocusClass
     * @param {HTMLElement} element The HTML element on which the class should be toggled.
     * @param {Boolean} [useCapture=true] Capturing phase or bubbling phase, default to <code>true</code> (capture).
     * @returns {MovLib}
     */
    toggleFocusClass: function (element, useCapture) {
      if (element) {
        this.add = this.add || function () {
          this.classList.add("focus");
        };
        element.addEventListener("focus", this.add, (useCapture || true));

        this.remove = this.remove || function () {
          this.classList.remove("focus");
        };
        element.addEventListener("blur", this.remove, (useCapture || true));
      }
      return this;
    }

  };

  // Instantiate and export the global MovLib object.
  window.MovLib = new MovLib();

})(window, window.document);
