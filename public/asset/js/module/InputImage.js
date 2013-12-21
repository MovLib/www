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
   * Image upload advanced features.
   *
   * @class InputImage
   * @constructor
   * @param {HTMLElement} element
   *   The <b>.inputimage</b> element to work with.
   * @return {InputImage}
   */
  function InputImage(element) {
    this.element     = element;
    this.maxFilesize = parseInt(element.getAttribute("data-max-filesize"), 10);
    this.height      = parseInt(element.getAttribute("data-height"), 10);
    this.width       = parseInt(element.getAttribute("data-width"), 10);
    this.minHeight   = parseInt(element.getAttribute("data-min-height"), 10);
    this.minWidth    = parseInt(element.getAttribute("data-min-width"), 10);
    this.alerts      = JSON.parse(element.children[0].innerHTML);
    this.fileReader  = (typeof FileReader !== undefined);
    this.preview     = element.children[1];
    this.img         = this.preview.children[0];
    this.newImg      = document.createElement("img");
    this.button      = element.children[2].getElementsByClassName("input-file")[0];
    this.input       = this.button.children[1];
    this.accept      = this.input.getAttribute("accept").split(",");
    this.init();
  }

  InputImage.prototype = {

    /**
     * Initialize the <b>.inputimage</b> element.
     *
     * @method init
     * @chainable
     * @returns {InputImage}
     */
    init: function () {
      this.button.addEventListener("focus", MovLib.classFocusAdd, true);
      this.button.addEventListener("blur", MovLib.classFocusRemove, true);
      if (this.fileReader === true) {
        this.fileReader        = new FileReader();
        this.fileReader.onload = this.fileReaderOnload.bind(this);
        this.input.addEventListener("change", this.previewImage.bind(this), false);
      }
    },

    insertNewImage: function () {
      this.preview.removeChild(this.img);
      this.preview.appendChild(this.newImg);
      return this;
    },

    fileReaderOnload: function (event) {
      this.newImg.src    = event.target.result;
      this.newImg.setAttribute("width", this.img.width);
      this.newImg.onload = this.previewImageOnload.bind(this);

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

    previewImageOnload: function () {
      // Validate the image's dimensions.
      if (this.newImg.naturalHeight < this.minHeight || this.newImg.naturalWidth < this.minWidth) {
        MovLib.invalidate(this.input, this.alerts.small);
        this.reset();
      }
      else {
        // The new image should have better quality if we're updating an existing image.
        if (this.height && this.width && (this.newImg.naturalHeight < this.height || this.newImg.naturalWidth < this.width)) {
          if (confirm(this.alerts.quality.split("{height_new}").join(this.newImg.naturalHeight).split("{width_new}").join(this.newImg.naturalWidth)) === true) {
            this.insertNewImage();
          }
          else {
            this.reset();
          }
        }
        // Make sure the user knows that this is only a preview.
        else if (!this.element.classList.contains("preview-alert")) {
          var previewAlert = MovLib.getAlert(this.alerts.preview, "", "info", { "aria-live": "polite" });
          this.button.parentNode.appendChild(previewAlert);
          previewAlert.classList.add("show");
          this.element.classList.add("preview-alert");
          this.insertNewImage();
        }
      }

      return this;
    },

    /**
     * Reset the input file form element.
     *
     * @link http://stackoverflow.com/a/16222877/1251219
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
   * @param {HTMLCollection} context
   *   The context we are currently working with.
   * @returns {MovLib}
   */
  MovLib.modules.InputImage = function (context) {
    var elements = context.getElementsByClassName("inputimage");
    var c        = elements.length;
    for (var i = 0; i < c; ++i) {
      elements[i].inputimage = new InputImage(elements[i]);
    }
    return MovLib;
  };

})(window, window.document, window.MovLib);
