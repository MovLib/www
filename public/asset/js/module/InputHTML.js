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
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 * @param {window} window
 * @param {document} document
 * @param {MovLib} MovLib
 * @returns {undefined}
 */
(function (window, document, MovLib) {
  "use strict";

  /**
   * WYSIWYG editor for the InputHTML form elemement partial plus other handy features.
   *
   * @param {HTMLElement} element
   *   The <b>.inputhtml</b> element to work with.
   * @returns {InputHTML}
   */
  function InputHTML(element) {
    this.textarea = element.children[1].children[1];
    this.editor   = element.children[2];
    this.content  = this.editor.children[0].children[0];
    this.init(element);
  }

  InputHTML.prototype = {

    /**
     * Copy the content of the content editable element back into the textarea.
     *
     * @returns {InputHTML}
     */
    copyToTextarea: function () {
      this.textarea.value = this.content.innerHTML;
      return this;
    },

    /**
     * React on focus changes.
     *
     * @param {Event} event
     *   The focus event.
     * @returns {InputHTML}
     */
    focus: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // Focus event is now totally disabled for this HTMLElement! (Doesn't account for mouse clicks ;)
      return this;
    },

    /**
     * Initialize <b>.inputhtml</b> element.
     *
     * @param {HTMLElement} element
     *   The element to initialize.
     * @returns {InputHTML}
     */
    init: function (element) {
      // Copy textarea's content into our content area.
      // @todo Does this really trigger a single reflow?
      this.content.innerHTML = this.textarea.value;

      // We have to copy the divs content back into the textarea directly before the form is submitted to ensure that
      // the content is automatically passed to our webserver via the browser. We only do this once instead of updating
      // the textarea on every key event.
      MovLib.bind(element.form, { submit: this.copyToTextarea.bind(this) });

      // React on various content events.
      MovLib.bind(this.content, {
        focus   : this.focus.bind(this),
        keydown : this.keydown.bind(this),
        keyup   : this.keyup.bind(this)
      });

      // Use this to print an HTMLElement to the console!
      //console.dir(element);
      //
      // Helpful code: https://github.com/jakiestfu/Medium.js/blob/master/medium.js
      //
      // Remember IE9+ support!
      // Remember if something results in more than a single reflow hide and show element.

      return this;
    },

    /**
     * React on keydown changes.
     *
     * @param {Event} event
     *   The focus event.
     * @returns {InputHTML}
     */
    keydown: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // Keydown event is now totally disabled for this HTMLElement!
      return this;
    },

    /**
     * React on keyup changes.
     *
     * @param {Event} event
     *   The focus event.
     * @returns {InputHTML}
     */
    keyup: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // Keyup event is now totally disabled for this HTMLElement!
      return this;
    }

  };

  /**
   * Attach InputHTML to the MovLib modules.
   *
   * @param {HTMLCollection} context
   *   The context we are currently working with.
   * @returns {MovLib}
   */
  MovLib.modules.InputHTML = function (context) {
    var elements = context.getElementsByClassName("inputhtml");

    // Go through all matching HTMLElements and enhance them if they aren't already enhanced.
    for (var i = 0; i < elements.length; ++i) {
      if (!elements[i].inputhtml) {
        elements[i].inputhtml = new InputHTML(elements[i]);
      }
    }

    return MovLib;
  };

})(window, window.document, window.MovLib);
