/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

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

  // Use the native spell checker of the browser, since SCAYT doesn"t seem to work.
  config.disableNativeSpellChecker = false;

  // Disable all HTML entities and conversions, since we encode the input ourselves and use UTF-8.
  config.entities = false;
  config.entities_additional = "";
  config.entities_greek = false;
  config.entities_latin = false;

  // Enable automatic growth and content-editable instead of <iframe> editing.
  config.extraAllowedContent = "cite";
  config.extraPlugins = "autogrow,divarea,justify";

  // Define our custom alignment classes.
  config.image2_alignClasses = [ "user-left", "user-center", "user-right" ];
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
CKEDITOR.on( "dialogDefinition", function( ev ){
    // Take the dialog name and its definition from the event data.
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;

    // Check if the definition is from the dialog we"re only interested in the link dialog.
    if ( dialogName === "link" ) {
      // Remove unnecessary items from the select boxes.
      dialogDefinition.getContents("info").get("protocol")["items"] = [["http://‎","http://"],["https://‎","https://"]];
      dialogDefinition.getContents("info").get("linkType")["items"] = [ dialogDefinition.getContents("info").get("linkType")["items"][0] ];
    }
});

