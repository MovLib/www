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

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement.
	config.toolbarGroups = [
//		{ name: "insert" },
//		{ name: "forms" },
//		{ name: "tools" },
//		{ name: "others" },
//		"/",
		{ name: "styles" },
		{ name: "basicstyles", groups: [ "basicstyles", "cleanup" ] },
		{ name: "paragraph",   groups: [ "align", "list", "indent" ] },
		{ name: "insert" , groups : [ "links", "blocks", "insert"]},
		{ name: "undo",   groups: [ "undo", "find" ] },
//		{ name: "editing",     groups: [ "find", "selection" ] },
//		{ name: "colors" },
		{ name: "document",	   groups: [ "mode" ] }
	];

  // Get rid of useless entities like &nbsp;.
  config.basicEntities = false;

	// Set the most common block elements.
	config.format_tags = "p;h2;h3;h4;pre";
  // Disable unwanted &nbsp; characters in empty block elements.
  config.fillEmptyBlocks = false;

  // Enable automatic growth of the editor.
  config.autoGrow_onStartup = true;

  // Override the default bold element, since we use <b> tags.
  config.coreStyles_bold = { element: "b", overrides: "strong" };

  // Override the default italic element, since we use <i> tags.
  config.coreStyles_italic = { element: "i", overrides: "em" };

  // Use the native spell checker of the browser, since SCAYT doesn't seem to work.
  config.disableNativeSpellChecker = false;

  // Disable all HTML entities and conversions, since we encode the input ourselves and use UTF-8.
  config.entities = false;
  config.entities_additional = "";
  config.entities_greek = false;
  config.entities_latin = false;

  // Enable automatic growth and content-editable instead of <iframe> editing.
  config.extraAllowedContent = "cite";
  config.extraPlugins = "autogrow,divarea,justify,image2";

  // Define our custom alignment classes.
  config.image2_alignClasses = [ "user-left", "user-center", "user-right" ];
  config.image2_captionedClass = "";
  config.justifyClasses = [ "user-left", "user-center", "user-right", null ];

  // Disable advanced link dialog settings.
  config.linkShowAdvancedTab = false;
  config.linkShowTargetTab = false;

	// Simplify the dialog windows.
	config.removeDialogTabs = "image:advanced;link:advanced";

  // Remove unnecessary buttons and plugins.
  config.removePlugins = "contextmenu,maximize,table,tableresize,tabletools,scayt";

  // Set the number of TAB spaces to zero.
  config.tabSpaces = 0;

};

// Modify dialog definitions to remove unnecessary items.
CKEDITOR.on( "dialogDefinition", function(ev){
  // Take the dialog name and its definition from the event data.
  var dialogName = ev.data.name;
  var dialogDefinition = ev.data.definition;

  // Remove unnecessary items from the select boxes of the link dialog.
  if (dialogName === "link") {
    dialogDefinition.getContents("info").get("protocol")["items"] = [["http://‎","http://"],["https://‎","https://"]];
    dialogDefinition.getContents("info").get("linkType")["items"] = [ dialogDefinition.getContents("info").get("linkType")["items"][0] ];
  }

  // Remove unnecessary "captioned" checkbox from the image dialog.
  if (dialogName === "image2") {
    // Hide the alternative text field from the user, since we always use the caption for that purpose.
    dialogDefinition.getContents("info").get("alt").className = "dn";

    // Check the checkbox by default, since we always want a caption.
    dialogDefinition.getContents("info").get("hasCaption").default = true;

    // Seems like some kind of event has to be triggered in order to update the state of the checkbox.
    // This works fine, even if the callback has no body (WTH CKEditor?). It contains a body for sanity's sake.
    dialogDefinition.getContents("info").get("hasCaption").setup = function () {
      this.checked = true;
    };

    // Hide the checkbox from the user.
    dialogDefinition.getContents("info").get("hasCaption").className = "dn";
  }
});

