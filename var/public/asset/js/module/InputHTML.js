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
   * The name of this JavaScript module.
   *
   * @type String
   */
  var moduleName = "InputHTML";

  /**
   * WYSIWYG editor for the InputHTML form elemement partial plus other handy features.
   *
   * @class InputHTML
   * @constructor
   * @param {HTMLElement} element
   *   The <b>.inputhtml</b> element to work with.
   * @return {InputHTML}
   */
  function Module(element) {

//    /**
//     * The form's textarea, contains the HTML source.
//     *
//     * @property textarea
//     * @type HTMLElement
//     */
//    this.textarea = element.children[1].children[0];
//
//    /**
//     * The <code><div></code> wrapping the WYSIWYG buttons.
//     *
//     * @property editor
//     * @type HTMLElement
//     */
//    this.editor = element.children[2];
//
//    /**
//     * The content editable <code><iframe></code> element.
//     *
//     * @property content
//     * @type HTMLIFrameElement
//     */
//    this.content = this.editor.children[this.editor.children.length - 1];
//
//    // Enhance the current element.
//    this.init(element);

    // CKEditor configuration according to our mode.
    var config = {
      customConfig: "/asset/js/ckeditor/config.js",
      format_tags: "p",
      language: MovLib.settings.ckeditor.language,
      removeButtons: "Underline,Subscript,Superscript,HorizontalRule,Anchor,Strike,JustifyBlock,Styles"
    };

    var paragraphGroups = [ "align" ];
    var insertGroups    = [ "links", "insert" ];

    // We need extended controls and tags.
    if (MovLib.settings.ckeditor.mode > 0) {
      paragraphGroups.push("list", "indent");
      insertGroups.splice(1, 0, "blocks");
      for (var i = MovLib.settings.ckeditor.headingLevel; i <= 6; i++) {
        config.format_tags += ";h" + i;
      }
    }

    // Remove the image button if no images are permitted.
    if (MovLib.settings.ckeditor.mode < 2) {
      config.removeButtons += ",Image";
    }

    config.toolbarGroups = [
      { name: "styles" },
      { name: "basicstyles", groups: [ "basicstyles", "cleanup" ] },
      { name: "paragraph",   groups: paragraphGroups },
      { name: "insert" , groups : insertGroups },
      { name: "undo",   groups: [ "undo" ] },
      { name: "document",	   groups: [ "mode" ] }
    ];

    var textarea = element.children[element.children.length - 1];

    // Add the "invalid" CSS class to the CKEditor container if the element is invalid.
    if (textarea.classList.contains("invalid")) {
      config.on = {
        instanceReady: function () {
          this.container.addClass("invalid");
        }
      };
    }

    // Escape special HTML characters to prevent the content-editable div of CKEditor to render them wrong.
    textarea.innerHTML = MovLib.htmlspecialchars(textarea.innerHTML, 'ENT_QUOTES', null, false);

    // Enhance the currenct element with the CKEditor for now.
    window.CKEDITOR.dom.element.prototype.disableContextMenu = function(){};
    var editor = window.CKEDITOR.replace(textarea.id, config);

  }

  Module.prototype = {

    /**
     * Copy the content of the content editable element back into the textarea.
     *
     * @method copyToTextarea
     * @chainable
     * @return {InputHTML}
     */
    copyToTextarea: function () {
      this.textarea.value = this.content.contentDocument.body.innerHTML;
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
      var eventFunctions = {

        /**
         * Handle alignment button clicks.
         *
         * @function align
         * @chainable
         * @param {Event} event
         *   The click event on an align button.
         * @returns {InputHTML}
         */
        align: function (event) {
          event.preventDefault();
          event.returnValue = false;
          // @todo Read direction from data attribute and add the respective class.
          alert("Not implemented yet!");
          return this;
        }.bind(this),

        /**
         * Handle blur events on the editor.
         *
         * @function blurEditor
         * @chainable
         * @returns {InputHTML}
         */
        blurEditor: function () {
          // If we have an active element in our editor iframe or our wrapper div, do not blur.
          if (!document.activeElement || !this.editor.contains(document.activeElement)) {
            this.editor.classList.remove("focus");
            // Remove all event handlers for the editor controls.
            // @todo Remove all event handlers for the editor controls.
          }
          return this;
        }.bind(this),

        /**
         * Handle format block clicks.
         *
         * @function
         * @param {Event} event
         *   The click event on a block format.
         * @returns {InputHTML}
         */
        formatBlock: function (event) {
          this.content.contentDocument.execCommand("formatBlock", false, event.target.getAttribute("data-tag"));
          this.content.focus();
          return this;
        }.bind(this),

        /**
         * Handle format inline clicks.
         *
         * @function
         * @param {Event} event
         *   The click event on an inline format.
         * @returns {InputHTML}
         */
        formatInline: function (event) {
          console.log("formatInline: " + event.target.getAttribute("data-tag"));
          this.content.contentDocument.execCommand(event.target.getAttribute("data-tag"), false, null);
          this.content.focus();
          return this;
        }.bind(this),

        /**
         * Handle image button clicks.
         *
         * @function image
         * @chainable
         * @param {Event} event
         *   The click event on the image button.
         * @returns {InputHTML}
         */
        image: function (event) {
          event.preventDefault();
          event.returnValue = false;
          alert("Not implemented yet!");
          return this;
        }.bind(this),

        /**
         * Handle indent button clicks.
         *
         * @function indent
         * @chainable
         * @param {Event} event
         *   The click event on an indent button.
         * @returns {InputHTML}
         */
        indent: function (event) {
          event.preventDefault();
          event.returnValue = false;
          // @todo Read direction from data attribute.
          alert("Not implemented yet!");
          return this;
        }.bind(this),

        /**
         * React on keyup changes.
         *
         * @method keyup
         * @chainable
         * @param {Event} event
         *   The keydown event.
         * @return {InputHTML}
         */
        keyup: function (event) {
          this.keyupBlocked = {};

          // Check for the ALT + F10 key combination and activate toolbar tabbing.
          if (!this.keyupBlocked.toolbar && event.altKey && ((event.which || event.keyCode) === 121)) {
            this.keyupBlocked.toolbar = true;

            var c = this.editor.children.length - 1;
            for (var i = 0; i < c; ++i) {
              this.editor.children[i].setAttribute("tabindex", 0);
            }

            // Caution, focus lost = selection lost!
            this.editor.firstChild.focus();

            window.setTimeout(function () {
              this.keyupBlocked.toolbar = false;
            }.bind(this), 100);

            return this;
          }

          // @markus you have to implement the following code, blocking and so on!!!!
          //         the following is only a scaffold and doesn't do anything
          if (!this.keyupBlocked.actions) { // Paste + Delete + Backspace
            this.keyupBlocked.actions = true;

            window.setTimeout(function () {
              this.keyupBlocked.actions = false;
            }.bind(this), 100);

            return this;
          }

          // @markus the following code should only be executed if any interesting keystroke was pressed, not on every
          //         possible keystroke in this world.
          //
          // Delete the contents of the placeholder when something is typed.
          if (this.content.classList.contains("placeholder")) {
            this.content.classList.remove("placeholder");
            this.content.contentDocument.body.firstChild.innerHTML = "";
          }
          else if (this.content.contentDocument.body.children.length > 0 && this.content.contentDocument.body.firstChild.nodeType === 3) {
            this.content.contentDocument.body.innerHTML = "<p>" + this.content.contentDocument.body.firstChild.textContent.replace(/\n\n+/, "</p><p>").split("\n").join("<br>") + "</p>";
          }
          // Check if the content is empty. If so, put the placeholder back into place and delete all unnecessary contents
          // like <br> or <div> some browsers insert.
          else if (this.content.contentDocument.body.innerHTML === "" || this.content.contentDocument.body.children[0].childNodes.length === 0) {
            this.content.contentDocument.body.innerHTML = "";
            var placeholder = this.content.contentDocument.createElement("p");
            this.content.contentDocument.body.appendChild(placeholder);
            this.content.focus();
            this.content.classList.add("placeholder");
            placeholder.innerHTML = this.textarea.getAttribute("placeholder");
          }

          return this;
        }.bind(this),

        /**
         * Handle link button clicks.
         *
         * @function link
         * @chainable
         * @param {Event} event
         *   The click event on the link button.
         * @returns {InputHTML}
         */
        link: function (event) {
          event.preventDefault();
          event.returnValue = false;
          // @todo Handle external link restriction.
          alert("Not implemented yet!");
          return this;
        }.bind(this),

        /**
         * Handle list button clicks.
         *
         * @function list
         * @chainable
         * @param {Event} event
         *   The click event on a list button.
         * @returns {InputHTML}
         */
        list: function (event) {
          event.preventDefault();
          event.returnValue = false;
          // @todo Read type from event target.
          alert("Not implemented yet!");
          return this;
        }.bind(this),

        /**
         * Handle quotation button clicks.
         *
         * @function quotation
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
        }.bind(this)
      };

      this.content.contentDocument.designMode = "on";
      if (this.textarea.value === "") {
        var placeholder       = document.createElement("p");
        placeholder.innerHTML = this.textarea.getAttribute("placeholder");
        this.content.contentDocument.body.appendChild(placeholder);
        this.content.classList.add("placeholder");
      }
      else {
        this.content.contentDocument.body.innerHTML = this.textarea.value;
      }

      // We have to copy the divs content back into the textarea directly before the form is submitted to ensure that
      // the content is automatically passed to our webserver via the browser. We only do this once instead of updating
      // the textarea on every key event.
      element.form.addEventListener("submit", this.copyToTextarea.bind(this), false);

      this.editor.addEventListener("blur", function () {
        window.setTimeout(eventFunctions.blurEditor, 100);
      }.bind(this), true);

      this.content.contentDocument.addEventListener("focus", function () {
        // Attach the placeholder if the class is present.
        if (this.content.classList.contains("placeholder")) {
          this.content.contentDocument.body.firstChild.innerHTML = "";
          this.content.contentDocument.body.firstChild.innerHTML = this.textarea.getAttribute("placeholder");
        }


        // Set the cursor to the end of the content but within the last HTML tag.
        this.content.contentDocument.body.lastChild.focus();

        // Bind the event handlers and add the focus class, if the editor was not in focus already.
        if (!this.editor.classList.contains("focus")) {
          this.editor.classList.add("focus");

          // Bind event handlers to the editor controls.
          var c = this.editor.children.length - 1;
          for (var i = 0; i < c; ++i) {
            // Handle the children of the formats selector
            if (this.editor.children[i].classList.contains("formats")) {
              for (var j = 0; j < this.editor.children[i].children[1].children.length; ++j) {
                this.editor.children[i].children[1].children[j].addEventListener("click", eventFunctions.formatBlock, false);
              }
            }
            else {
              this.editor.children[i].addEventListener("click", eventFunctions[this.editor.children[i].getAttribute("data-handler")], false);
            }
          }
        }
      }.bind(this), true);

      this.content.contentDocument.addEventListener("blur", function () {
        window.setTimeout(eventFunctions.blurEditor, 100);
      });

      // React on various content events.
      // @see http://docs.cksource.com/ckeditor_api/symbols/src/plugins_keystrokes_plugin.js.html
      this.content.contentDocument.addEventListener("keyup", eventFunctions.keyup, false);

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
     * React on keydown changes.
     *
     * @method keydown
     * @chainable
     * @param {Event} event
     *   The keydown event.
     * @return {InputHTML}
     */
    keydown: function (event) {
      // Disable keyup totally!
      //event.preventDefault();
      //event.returnValue = false;

      // Check for the ALT + F10 key combination and activate toolbar tabbing.
      if (event.altKey && ((event.which || event.keyCode) === 121)) {
        var c = this.editor.children.length - 1;
        for (var i = 0; i < c; ++i) {
          this.editor.children[i].setAttribute("tabindex", 0);
        }
        // Caution, focus lost = selection lost!
        this.editor.firstChild.focus();
      }

      return this;
    },

    /**
     * React on keypress changes.
     *
     * @method keypress
     * @chainable
     * @param {Event} event
     *   The keypress event.
     * @return {InputHTML}
     */
    keypress: function (event) {
      // Disable keypress totally!
      //event.preventDefault();
      //event.returnValue = false;

      // Delete the contents of the placeholder when something is typed.
      if (this.content.classList.contains("placeholder")) {
        this.content.classList.remove("placeholder");
        this.content.contentDocument.body.firstChild.innerHTML = "";
      }
      else if (this.content.contentDocument.body.children.length > 0 && this.content.contentDocument.body.firstChild.nodeType === 3) {
        this.content.contentDocument.body.innerHTML = "<p>" + this.content.contentDocument.body.firstChild.textContent.replace(/\n\n+/, "</p><p>").split("\n").join("<br>") + "</p>";
      }
      // Check if the content is empty. If so, put the placeholder back into place and delete all unnecessary contents
      // like <br> or <div> some browsers insert.
      else if (this.content.contentDocument.body.innerHTML === "" || this.content.contentDocument.body.children[0].childNodes.length === 0) {
        this.content.contentDocument.body.innerHTML = "";
        var placeholder = this.content.contentDocument.createElement("p");
        this.content.contentDocument.body.appendChild(placeholder);
        this.content.focus();
        this.content.classList.add("placeholder");
        placeholder.innerHTML = this.textarea.getAttribute("placeholder");
      }

      return this;
    },

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
  MovLib.modules[moduleName] = function (context) {
    var elements = context.getElementsByClassName(moduleName.toLowerCase());
    var c        = elements.length;
    for (var i = 0; i < c; ++i) {
      elements[i][moduleName] = new Module(elements[i]);
    }
    return MovLib;
  };

})(window, window.document, window.MovLib);
