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
 * Internet Explorer 9+ <code>Element.classList</code> implementation.
 *
 * Why I wrote my own? No offense but the code is a mess. I decided to write my own script, properly documented and
 * only supporting IE9. It's time to move away from all those old browsers. This implementation should be ultra fast
 * and help IE9 users with their slow user agent.
 *
 * @link https://developer.mozilla.org/en-US/docs/Web/API/Element.classList
 * @link http://purl.eligrey.com/github/classList.js/blob/master/classList.js
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
(function () {

  /**
   * Used to keep things short and to avoid magic values.
   *
   * @type String
   * @default "class"
   */
  var c = "class";

  /**
   * Instantiate new Element.classList user land instance.
   *
   * @class ClassList
   * @constructor
   * @param {Element} element The element to enhance with a classList.
   */
  function ClassList(element) {

    /**
     * The class list's element.
     *
     * @property element
     * @type Element
     */
    this.element = element;

    /**
     * The classes list.
     *
     * @property classes
     * @type Array
     */
    this.classes = [];

    // Check if this element already has a class attribute, if so, export the content to class scope.
    var c = element.getAttribute(c);
    if (c) {
      this.classes = (c.trim()).split(/\s+/);
    }
  }

  ClassList.prototype = {

    /**
     * Add one or more CSS classes to the element's class list.
     *
     * @method add
     * @param {String} className* One or more class names to add.
     */
    add: function (className) {
      var classNames = arguments;
      var i          = classNames.length;
      var index;
      var updated    = false;

      while (i--) {
        className = classNames[i] + "";
        index     = this.classes.indexOf(className);
        if (index === -1) {
          this.classes.push(className);
          updated = true;
        }
      }

      if (updated === true) {
        this.element.setAttribute(c, this.classes.join(" "));
      }
    },

    /**
     * Check if an element's list of classes contains a specific class.
     *
     * @method contains
     * @param {String} className The specific class to check for.
     * @return {Boolean} <code>TRUE</code> if the element's class list contains the class, otherwise <code>FALSE</code>.
     */
    contains: function (className) {
      className += "";
      return this.classes.indexOf(className) !== -1;
    },

    /**
     * Remove one or more classes from an element's list of classes
     *
     * @method remove
     * @param {String} className* One or more class names.
     */
    remove: function (className) {
      var classNames = arguments;
      var i          = classNames.length;
      var index;
      var updated    = false;

      while (i--) {
        className = classNames[i] + "";
        index     = this.classes.indexOf(className);
        if (index !== -1) {
          this.classes.splice(index, 1);
          updated = true;
        }
      }

      if (updated === true) {
        this.element.setAttribute(c, this.classes.join(" "));
      }
    },

    /**
     * Toggle the existence of a class in an element's list of classes.
     *
     * @method toggle
     * @param {String} className The class name to toggle.
     * @param {Boolean} [force=undefined] <code>FALSE</code> will remove the class and <code>TRUE</code> will add the class, no
     *   matter if it's present right now or not.
     * @return {Boolean} <code>TRUE</code> if the class was added to the element, <code>FALSE</code> if it was removed.
     */
    toggle: function (className, force) {
      className += "";
      var contains = this.contains(className);
      if (contains === true && force !== true) {
        this.remove(className);
      }
      else if (force !== false) {
        this.add(className);
      }
      return !contains;
    }

  };

  // Finally add the missing classList property to the Element object and allow IE9+ users to browse our site.
  Object.defineProperty(Element.prototype, "classList", {
    get          : function () {
      return new ClassList(this);
    },
    enumerable   : true,
    configurable : true
  });

})();
