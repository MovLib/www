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
 * @submodule InputHTML
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
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
   * WYSIWYG editor for the InputHTML form elemement partial plus other handy features.
   *
   * @class InputHTML
   * @constructor
   * @param {HTMLElement} element
   *   The <b>.inputhtml</b> element to work with.
   */
  function InputHTML(element) {

    /**
     * The form's textarea, contains the HTML source.
     *
     * @property textarea
     * @type HTMLElement
     */
    this.textarea = element.children[1].children[0];

    /**
     * The <code><div></code> wrapping the WYSIWYG buttons.
     *
     * @property editor
     * @type HTMLElement
     */
    this.editor = element.children[2];

    /**
     * The content editable <code><div></code>.
     *
     * @property content
     * @type HTMLElement
     */
    this.content = this.editor.children[this.editor.children.length - 1].children[0];

    // Enhance the current element.
    this.init(element);
  }

  InputHTML.prototype = {

    /**
     * Handle alignment button clicks.
     *
     * @method align
     * @chainable
     * @param {Event} event
     *   The click event on an align button.
     * @returns {InputHTML}
     */
    align: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // @todo Read direction from data attribute and add the respective class.
      console.dir(event);
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle bold formatting button clicks.
     *
     * @method bold
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    bold: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Clicked on bold button.");
      return this;
    },

    /**
     * Copy the content of the content editable element back into the textarea.
     *
     * @method copyToTextarea
     * @chainable
     * @return {InputHTML}
     */
    copyToTextarea: function () {
      this.textarea.value = this.content.innerHTML;
      return this;
    },

    /**
     * Handle format selector clicks.
     *
     * @method formats
     * @chainable
     * @param {Event} event
     *   The click event on the formats selector.
     * @returns {InputHTML}
     */
    formats: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle format clicks for headings.
     *
     * @method heading
     * @chainable
     * @param {Event} event
     *   The click event on a heading format.
     * @returns {InputHTML}
     */
    heading: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // @todo Read heading level from data attribute and format the whole block.
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle image button clicks.
     *
     * @method image
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    image: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle indent button clicks.
     *
     * @method indent
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    indent: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // @todo Read direction from data attribute.
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Initialize <b>.inputhtml</b> element.
     *
     * @method init
     * @chainable
     * @param {HTMLElement} element
     *   The element to initialize.
     * @return {InputHTML}
     */
    init: function (element) {
      // Copy textarea's content into our content area.
      // @todo Does this really trigger a single reflow?
      this.content.innerHTML = this.textarea.value;

      // We have to copy the divs content back into the textarea directly before the form is submitted to ensure that
      // the content is automatically passed to our webserver via the browser. We only do this once instead of updating
      // the textarea on every key event.
      element.form.addEventListener("submit", this.copyToTextarea.bind(this), false);

      this.editor.addEventListener("focus", function () {
        // Set the cursor to the end of the content but within the last HTML tag.
        if (this.content.lastChild) {
          var range     = document.createRange();
          var selection = window.getSelection();
          range.setStart(this.content.lastChild, 1);
          range.collapse(true);
          selection.removeAllRanges();
          selection.addRange(range);
        }

        // Give the complete editor focus.
        this.editor.classList.add("focus");
      }.bind(this), true);

      this.editor.addEventListener("blur", function () {
        this.editor.classList.remove("focus");
      }.bind(this), true);

      // Bind event handlers to the editor controls.
      var c = this.editor.children.length - 1;
      for (var i = 0; i < c; ++i) {
        // Handle the children of the formats selector
        if (this.editor.children[i].getAttribute("data-handler") === "formats") {

        }
        this.editor.children[i].addEventListener("click", this[this.editor.children[i].getAttribute("data-handler")].bind(this), false);
      }

      // React on various content events.
      this.content.addEventListener("keydown", this.keydown.bind(this), false);
      this.content.addEventListener("keyup", this.keyup.bind(this), false);

      // Use this to print an HTMLElement to the console!
      //console.dir(element);
      //
      // How to document: http://yui.github.io/yuidoc/syntax/index.html
      //
      // Helpful code: https://github.com/jakiestfu/Medium.js/blob/master/medium.js
      //
      // Remember IE9+ support!
      // Remember if something results in more than a single reflow hide and show element.

      return this;
    },

    /**
     * Handle italic formatting button clicks.
     *
     * @method italic
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    italic: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Not implemented yet!");
      return this;
    },

    /**
     * React on keydown changes.
     *
     * @method keydown
     * @chainable
     * @param {Event} event
     *   The focus event.
     * @return {InputHTML}
     */
    keydown: function (event) {

      // Disable keydown totally!
      //event.preventDefault();
      //event.returnValue = false;
      return this;
    },

    /**
     * React on keyup changes.
     *
     * @method keyup
     * @chainable
     * @param {Event} event
     *   The focus event.
     * @return {InputHTML}
     */
    keyup: function (event) {
      // Disable keyup totally!
      //event.preventDefault();
      //event.returnValue = false;
      if (this.content.innerHTML === "") {
        this.editor.classList.remove("not-empty");
      }
      else {
        this.editor.classList.add("not-empty");
      }
      return this;
    },

    /**
     * Handle link button clicks.
     *
     * @method link
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    link: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // @todo Handle external link restriction.
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle list button clicks.
     *
     * @method list
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    list: function (event) {
      event.preventDefault();
      event.returnValue = false;
      // @todo Read type from event target.
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle format clicks for paragraphs.
     *
     * @method paragraph
     * @chainable
     * @param {Event} event
     *   The click event on the paragraph format.
     * @returns {InputHTML}
     */
    paragraph: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Not implemented yet!");
      return this;
    },

    /**
     * Handle quotation button clicks.
     *
     * @method quotation
     * @chainable
     * @param {Event} event
     *   The click event on the editor button.
     * @returns {InputHTML}
     */
    quotation: function (event) {
      event.preventDefault();
      event.returnValue = false;
      alert("Not implemented yet!");
      return this;
    }

  };

  /**
   * Attach InputHTML to the MovLib modules namespace.
   *
   * @method
   * @chainable
   * @param {HTMLCollection} context
   *   The context we are currently working with.
   * @return {MovLib}
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
