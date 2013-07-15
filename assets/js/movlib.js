/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link http://movlib.org/ MovLib}.
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

/* jshint browser:true, jquery:true */
/* global MovLib:true, use:true */

/**
 * Bootstrap directly after script is loaded.
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013–present, MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link http://movlib.org/
 * @since 0.0.1-dev
 *
 * @param {jQuery} $
 *   The global jQuery object.
 * @param {window} window
 *   The global window object.
 * @param {document} document
 *   The global document object.
 * @returns {undefined}
 */
(function ($, window, document) {
  "use strict";

  /**
   * The global MovLib oject.
   *
   * @type MovLib
   */
  window.MovLib = { settings: {}, modules: {} };

  /**
   * Load a single MovLib JavaScript module including all its dependencies.
   *
   * @param {String} module
   *   The name of the module.
   * @returns {Deferred.promise}
   *   Returns a Deferred promise which will be resolved as soon as the module is loaded.
   */
  $.getModule = function (module) {
    return $.Deferred(function (dfd) {
      if (!MovLib.modules[module]) {
        $.ajax({
          cache: true,
          dataType: "script",
          // @todo The version should be part of the files name, create rewrite rules within nginx.
          url: "https://" + MovLib.settings.serverNameStatic + "/js/modules/" + module + ".js?v=" + MovLib.settings.version
        }).done(function () {
          if (MovLib.modules[module].dependencies) {
            $.when($.getModules(MovLib.modules[module].dependencies)).done(dfd.resolve);
          } else {
            dfd.resolve();
          }
        });
      } else {
        dfd.resolve();
      }
    }).promise();
  };

  /**
   * Load a bunch of MovLib modules including all their dependencies.
   *
   * @param {Array} modules
   *   Numeric array containing the names of the modules that should be loaded.
   * @returns {Deferred.promise}
   *   Returns a Deferred promise which will be resolved as soon as all modules have been loaded.
   */
  $.getModules = function (modules) {
    return $.Deferred(function (dfd) {
      var i = modules.length;
      while (modules.length > 0) {
        $.when($.getModule(modules.shift())).done(function () {
          if (--i === 0) {
            dfd.resolve();
          }
        });
      }
    }).promise();
  };

  /**
   * Execute the given module.
   *
   * @param {String} module
   *   The name of the module that should be executed.
   * @param {Object} context
   *   [Optional] The context with which we are currently working. Defaults to <code>document</code>.
   * @param {Object} settings
   *   [Optional] The settings for this module. Will be extended (overwritten) with <code>MovLib.settings</code>.
   * @returns {jQuery}
   */
  $.executeModule = function (module, context, settings) {
    context = context || document;
    settings = $.extend(settings, MovLib.settings);
    if (!MovLib.modules[module]) {
      $.when($.getModule(module)).done(function () {
        MovLib.modules[module](context, settings);
      });
    } else {
      MovLib.modules[module](context, settings);
    }
    return this;
  };

  /**
   * Execute all given modules.
   *
   * @param {Array} modules
   *   Numeric array containing all modules that should be executed.
   * @returns {jQuery}
   */
  $.executeModules = function (modules) {
    while (modules.length > 0) {
      $.executeModule(modules.shift());
    }
    return this;
  };

  // Wait for the DOM to be ready.
  $(function () {
    // Merge default settings with settings passed in from PHP.
    MovLib.settings = $.parseJSON($("#js-settings").text());

    // Load all modules of this page.
    if (MovLib.settings.modules.length > 0) {
      $.executeModules(MovLib.settings.modules);
    }
  });

})(jQuery, window, document);
