<#assign licenseFirst = "/*!">
<#assign licensePrefix = " *">
<#assign licenseLast = " */">
<#import "../Licenses/${project.license}.ftl" as prj>
<#include "../Licenses/license-${project.license}.txt">

/* jshint browser:true, jquery:true */
/* global MovLib:true, use:true */

/**
 * Description of ${name}
 *
 * @author ${user}
 * @copyright © ${date?date?string("yyyy")} ${prj.name}
 * @license ${prj.licenseLink} ${prj.licenseName}
 * @link ${prj.website}
 * @since ${prj.version}
 *
 * @param {jQuery} $
 *   The global jQuery object.
 * @param {document} document
 *   The global document object.
 * @param {MovLib} MovLib
 *   The global MovLib object.
 * @param {window} window
 *   The global window object.
 * @param {undefined} undefined
 *   In older engines (ES5-) undefined is mutable, we ensure that undefined is really undefined with this parameter.
 * @returns {undefined}
 */
(function ($, document, MovLib, window, undefined) {
  "use strict";

  /**
   * Description of ${name}.
   *
   * @param {Object} context
   *   The current context we are working with.
   * @param {Object} settings
   *   The MovLib settings that were passed from PHP.
   * @returns {undefined}
   */
  MovLib.modules.${name} = function (context, settings) {

  };

  // Contains all modules that this module relys on.
  //MovLib.modules.${name}.dependencies = [];
  // Remove these lines if your module has no dependencies!

})(jQuery, document, MovLib, window);
