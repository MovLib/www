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
  var moduleName = "InputImage";

  /**
   * Image upload advanced features.
   *
   * @class InputImage
   * @constructor
   * @param {HTMLElement} element The `.inputimage`-element to work with.
   * @return {Module}
   */
  function Module(element) {
    /**
     * The `.inputimage`-element to work with.
     *
     * @property element
     * @type HTMLElement
     */
    this.element = element;

    /**
     * The maximum filesize that is allowed.
     *
     * @property maxFilesize
     * @type Number
     */
    this.maxFilesize = parseInt(element.getAttribute("data-max-filesize"), 10);

    /**
     * Height of the existing image.
     *
     * @property height
     * @type Number
     */
    this.height = parseInt(element.getAttribute("data-height"), 10);

    /**
     * Width of the existing image.
     *
     * @property width
     * @type Number
     */
    this.width = parseInt(element.getAttribute("data-width"), 10);

    /**
     * The minimum height that an image must have to be valid.
     *
     * @property minHeight
     * @type Number
     */
    this.minHeight = parseInt(element.getAttribute("data-min-height"), 10);

    /**
     * The minimum width that an image must have to be valid.
     *
     * @property minWidth
     * @type Number
     */
    this.minWidth = parseInt(element.getAttribute("data-min-width"), 10);

    /**
     * Translated error messages for the various validation errors.
     *
     * @property alerts
     * @type Object
     */
    this.alerts = JSON.parse(element.children[0].innerHTML);

    /**
     * `FileReader` instance or `false` if browser doesn't have support.
     *
     * @property fileReader
     * @type FileReader
     */
    this.fileReader = (typeof FileReader !== "undefined");

    /**
     * The preview area.
     *
     * @property preview
     * @type HTMLElement
     */
    this.preview = element.children[1];

    /**
     * The preview area's `<img>`-element.
     *
     * @property img
     * @type HTMLElement
     */
    this.img = this.preview.children[0];

    /**
     * Empty `<img>`-element.
     *
     * @property newImg
     * @type HTMLElement
     */
    this.newImg = document.createElement("img");

    /**
     * The button surrounding the input file form element.
     *
     * @property button
     * @type HTMLElement
     */
    this.button = element.children[2].getElementsByClassName("input-file")[0];

    /**
     * The input file form element.
     *
     * @property input
     * @type HTMLElement
     */
    this.input = this.button.children[1];

    /**
     * List of allowed MIME types.
     *
     * @property accept
     * @type Array
     */
    this.accept = this.input.getAttribute("accept").split(",");

    // Initialize the input image form element.
    this.init();
  }

  Module.prototype = {

    /**
     * {@see FileReader.onload} callback.
     *
     * @method fileReaderOnload
     * @chainable
     * @param {Event} event The fired event.
     * @returns {MovLib}
     */
    fileReaderOnload: function (event) {
      this.newImg.src    = event.target.result;
      this.newImg.setAttribute("width", this.img.width);
      this.newImg.onload = this.previewImageOnload;
      return this;
    },

    /**
     * Initialize the <b>.inputimage</b> element.
     *
     * @method init
     * @chainable
     * @returns {InputImage}
     */
    init: function () {
      MovLib.toggleFocusClass(this.button);

      if (this.fileReader === true) {
        // Bind once and keep forever.
        this.fileReaderOnload   = this.fileReaderOnload.bind(this);
        this.previewImage       = this.previewImage.bind(this);
        this.previewImageOnload = this.previewImageOnload.bind(this);

        // Instantiate FileReader and bind onload/event listeners.
        this.fileReader        = new FileReader();
        this.fileReader.onload = this.fileReaderOnload;
        this.input.addEventListener("change", this.previewImage, false);
      }

      return this;
    },

    /**
     * Remove existing preview `<img>`-element and insert new preview `<img>`-element.
     *
     * @method insertNewImage
     * @chainable
     * @returns {InputImage}
     */
    insertNewImage: function () {
      this.preview.removeChild(this.img);
      this.preview.appendChild(this.newImg);
      this.img    = this.newImg;
      this.newImg = document.createElement("img");
      return this;
    },

    /**
     * Display a preview of the image the user is going to upload, also display the alert message that tells the user
     * that the shown image is only a preview and submission of the form is still needed for upload.
     *
     * @method previewImage
     * @chainable
     * @returns {InputImage}
     */
    previewImage: function () {
      // Check if the user selected an actual file.
      if (this.input.files && this.input.files[0]) {
        // Validate the file's size.
        if (this.input.files[0].size > this.maxFilesize) {
          MovLib.invalidate(this.input, this.alerts.large);
        }
        // Validate the file's MIME type as reported by the browser.
        else if (this.accept.indexOf(this.input.files[0].type) === -1) {
          MovLib.invalidate(this.input, this.alerts.type);
        }
        // Instantiate file reader to load the file the user is trying to upload. Not supported in IE9!
        else {
          this.fileReader.readAsDataURL(this.input.files[0]);
        }
      }

      return this;
    },

    /**
     * Continue image validation after successful load and display preview image if valid.
     *
     * @method previewImageOnload
     * @chainable
     * @returns {InputImage}
     */
    previewImageOnload: function () {
      // Validate the image's dimensions.
      if (this.newImg.naturalHeight < this.minHeight || this.newImg.naturalWidth < this.minWidth) {
        MovLib.invalidate(this.input, this.alerts.small);
        this.reset();
      }
      else {
        // The new image should have better quality if we're updating an existing image.
        if (this.height && this.width && (this.newImg.naturalHeight < this.height || this.newImg.naturalWidth < this.width)) {
          if (confirm(this.alerts.quality.split("{height_new}").join(this.newImg.naturalHeight).split("{width_new}").join(this.newImg.naturalWidth)) === false) {
            this.reset();
            return this;
          }
        }
        // Make sure the user knows that this is only a preview.
        else if (!this.element.classList.contains("preview-alert")) {
          var previewAlert = MovLib.getAlert(this.alerts.preview, "", "info", { "aria-live": "polite" });
          this.button.parentNode.appendChild(previewAlert);
          previewAlert.classList.add("show");
          this.element.classList.add("preview-alert");
        }
        this.insertNewImage();
      }

      return this;
    },

    /**
     * Reset the input file form element.
     *
     * @link http://stackoverflow.com/a/16222877/1251219
     * @method reset
     * @chainable
     * @returns {InputImage}
     */
    reset: function () {
      try {
        this.input.value = "";
        if (this.input.value) {
          this.input.type = "text";
          this.input.type = "file";
        }
      }
      catch (e) {}

      return this;
    }

  };

  /**
   * Attach InputImage to the MovLib modules.
   *
   * @param {HTMLCollection} context The context we are currently working with.
   * @returns {MovLib}
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
