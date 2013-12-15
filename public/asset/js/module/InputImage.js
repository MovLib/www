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
    this.element = element;
    this.img     = element.children[0].children[0].children[0];
    this.button  = element.children[1].getElementsByClassName("input-file")[0];
    this.input   = this.button.children[1];
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
      if (typeof FileReader !== undefined) {
        this.input.addEventListener("change", this.previewImage.bind(this), false);
      }
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
      if (this.input.files && this.input.files[0]) {
        this.previewAlert = this.previewAlert || this.element.getElementsByClassName("alert")[0];
        var reader        = new FileReader();
        reader.onload     = function (event) {
          this.img.removeAttribute("height"); // Important if the user updates an existing image!
          this.img.src = event.target.result;
          this.previewAlert.classList.add("fade-in");
        }.bind(this);
        reader.readAsDataURL(this.input.files[0]);
      }
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
