<?php

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
namespace MovLib\Partial;

use \MovLib\Core\HTTP\Request;

/**
 * Defines the HTML attributes object.
 *
 * Properties for this object are set via annotations to ensure that the instance only contains the properties that were
 * actually added to it and still enable developers to enjoy proper auto-completion of available attributes. The list
 * of properties was auto-generated based on {@link https://developer.mozilla.org/en-US/docs/Web/HTML/Attributes} and
 * {@link https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques}.
 *
 * <b>NOTE</b><br>
 * Legacy and <code><applet></code> related attributes have been removed.
 *
 * @todo Write descriptions for all properties.
 * @todo Add types for properties.
 * @todo Verify that the defined properties are valid HTML5 properties.
 *
 * @property string $ariaAutocomplete
 * @property boolean $ariaChecked
 * @property boolean $ariaDisabled
 * @property boolean $ariaExpanded
 * @property boolean $ariaHaspopup
 * @property boolean $ariaHidden
 * @property boolean $ariaInvalid
 * @property string $ariaLabel
 * @property integer $ariaLevel
 *   Indicates the level of an element within a structure and must be 1 (one) or greater.
 *
 *   <b>External Links</b>
 *   <ul>
 *     <li>{@link http://www.w3.org/TR/wai-aria/states_and_properties#aria-level}</li>
 *     <li>{@link http://msdn.microsoft.com/en-us/library/ie/cc304085%28v=vs.85%29.aspx}</li>
 *   </ul>
 *
 *   <b>Global attribute</b>
 * @property boolean $ariaMultiline
 * @property boolean $ariaMultiselectable
 * @property string $ariaOrientation
 * @property boolean $ariaPressed
 * @property boolean $ariaReadonly
 * @property boolean $ariaRequired
 * @property boolean $ariaSelected
 * @property string $ariaSort
 * @property mixed $ariaValuemax
 * @property mixed $ariaValuemin
 * @property mixed $ariaValuenow
 * @property string $ariaValuetext
 *
 * @property {type} $ariaLive
 * @property {type} $ariaRelevant
 * @property {type} $ariaAtomic
 * @property {type} $ariaBusy
 *
 * @property {type} $ariaDropeffect
 * @property {type} $ariaDragged
 *
 * @property {type} $ariaActivedescendant
 *   The ID value of the descendent element.
 *
 *   <b>External Links</b>
 *   <ul>
 *     <li>{@link http://www.w3.org/TR/wai-aria/states_and_properties#aria-activedescendant}</li>
 *     <li>{@link https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques/Using_the_aria-activedescendant_attribute}</li>
 *     <li>{@link http://msdn.microsoft.com/en-us/library/ie/dd347027%28v=vs.85%29.aspx}</li>
 *   </ul>
 *
 *   <b>Global attribute</b>
 * @property {type} $ariaControls
 * @property array $ariaDescribedby
 *
 *
 *   <b>External Links</b>
 *   <ul>
 *     <li>{@link http://www.w3.org/TR/wai-aria/states_and_properties#aria-describedby}</li>
 *     <li>{@link https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques/Using_the_aria-describedby_attribute}</li>
 *   </ul>
 *
 *   <b>Global attribute</b>
 * @property {type} $ariaFlowto
 * @property array $ariaLabelledby
 *   The IDs of the elements that label the current element.
 *
 *   <b>External Links</b>
 *   <ul>
 *     <li>{@link http://www.w3.org/TR/wai-aria/states_and_properties#aria-labelledby}</li>
 *     <li>{@link https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques/Using_the_aria-labelledby_attribute}</li>
 *   </ul>
 *
 *   <b>Global attribute</b>
 * @property {type} $ariaOwns
 * @property {type} $ariaPosinset
 * @property {type} $ariaSetsize
 *
 * @property string $about
 *   RDFa Light about, {@link http://manu.sporny.org/2011/rdfa-lite/}.
 *
 *   <b>Global attribute</b>
 * @property string $accept
 *   List of types the server accepts, typically a file type.
 *
 *   <pre><form>, <input></pre>
 * @property string $acceptCharset
 *   List of supported charsets.
 *
 *   <pre><form></pre>
 * @property string $accesskey
 *   Defines a keyboard shortcut to activate or add focus to the element.
 *
 *   <b>Global attribute</b>
 * @property string $action
 *   The URI of a program that processes the information submitted via the form.
 *
 *   <pre><form></pre>
 * @property string $align
 *   Specifies the horizontal alignment of the element.
 *
 *   <pre><applet>, <caption>, <col>, <colgroup>, <hr>, <iframe>, <img>, <table>, <tbody>, <td>, <tfoot> , <th>, <thead>, <tr></pre>
 * @property string $alt
 *   Alternative text in case an image can’t be displayed.
 *
 *   <pre><applet>, <area>, <img>, <input></pre>
 * @property boolean $async
 *   Indicates that the script should be executed asynchronously.
 *
 *   <pre><script></pre>
 * @property boolean $autocomplete
 *   Indicates whether controls in this form can by default have their values automatically completed by the browser.
 *
 *   <pre><form>, <input></pre>
 * @property boolean $autofocus
 *   The element should be automatically focused after the page loaded.
 *
 *   <pre><button>, <input>, <keygen>, <select>, <textarea></pre>
 * @property boolean $autoplay
 *   The audio or video should play as soon as possible.
 *
 *   <pre><audio>, <video></pre>
 * @property boolean $autosave
 *   Previous values should persist dropdowns of selectable values across page loads.
 *
 *   <pre><input></pre>
 * @property string $buffered
 *   Contains the time range of already buffered media.
 *
 *   <pre><audio>, <video></pre>
 * @property string $challenge
 *   A challenge string that is submitted along with the public key.
 *
 *   <pre><keygen></pre>
 * @property string $charset
 *   Declares the character encoding of the page or script.
 *
 *   <pre><meta>, <script></pre>
 * @property boolean $checked
 *   Indicates whether the element should be checked on page load.
 *
 *   <pre><command>, <input></pre>
 * @property string $cite
 *   Contains a URI which points to the source of the quote or change.
 *
 *   <pre><blockquote>, <del>, <ins>, <q></pre>
 * @property array $class
 *   Often used with CSS to style elements with common properties.
 *
 *   <b>Global attribute</b>
 * @property {type} $cols
 *   Defines the number of columns in a textarea.
 *
 *   <pre><textarea></pre>
 * @property {type} $colspan
 *   The colspan attribute defines the number of columns a cell should span.
 *
 *   <pre><td>, <th></pre>
 * @property {type} $content
 *   A value associated with http-equiv or name depending on the context.
 *
 *   <pre><meta></pre>
 * @property {type} $contenteditable
 *   Indicates whether the element’s content is editable.
 *
 *   <b>Global attribute</b>
 * @property {type} $contextmenu
 *   Defines the ID of a <menu> element which will serve as the element’s context menu.
 *
 *   <b>Global attribute</b>
 * @property {type} $controls
 *   Indicates whether the browser should show playback controls to the user.
 *
 *   <pre><audio>, <video></pre>
 * @property {type} $coords
 *   A set of values specifying the coordinates of the hot-spot region.
 *
 *   <pre><area></pre>
 * @property {type} $data
 *   Specifies the URL of the resource.
 *
 *   <pre><object></pre>
 * @property array $dataAttributes
 *   Lets you attach custom attributes to an HTML element.
 *
 *   <b>Global attribute</b>
 * @property {type} $datetime
 *   Indicates the date and time associated with the element.
 *
 *   <pre><del>, <ins>, <time></pre>
 * @property {type} $default
 *   Indicates that the track should be enabled unless the user’s preferences indicate something different.
 *
 *   <pre><track></pre>
 * @property {type} $defer
 *   Indicates that the script should be executed after the page has been parsed.
 *
 *   <pre><script></pre>
 * @property string $dir
 *   Defines the text direction. Allowed values are <code>"ltr"</code> (Left-To-Right) or <code>"rtl"</code>
 *   (Right-To-Left).
 *
 *   <b>Global attribute</b>
 * @property string $dirname
 *   Instructs the form element to send the direction information along it's value to the server. Possible values are:
 *   <code>"auto"</code>, <code>"ltr"</code> (Left-To-Right), or <code>"rtl"</code> (Right-To-Left).
 *
 *   <pre><input>, <textarea></pre>
 * @property {type} $disabled
 *   Indicates whether the user can interact with the element.
 *
 *   <pre><button>, <command>, <fieldset>, <input>, <keygen>, <optgroup>, <option>, <select>, <textarea></pre>
 * @property {type} $download
 *   Indicates that the hyperlink is to be used for downloading a resource.
 *
 *   <pre><a>, <area></pre>
 * @property {type} $draggable
 *   Defines whether the element can be dragged.
 *
 *   <b>Global attribute</b>
 * @property {type} $dropzone
 *   Indicates that the element accept the dropping of content on it.
 *
 *   <b>Global attribute</b>
 * @property {type} $enctype
 *   Defines the content type of the form date when the method is POST.
 *
 *   <pre><form></pre>
 * @property {type} $for
 *   Describes elements which belongs to this one.
 *
 *   <pre><label>, <output></pre>
 * @property {type} $form
 *   Indicates the form that is the owner of the element.
 *
 *   <pre><button>, <fieldset>, <input>, <keygen>, <label>, <meter>, <object>, <output>, <progress>, <select>, <textarea></pre>

 * @property {type} $formaction
 *   Indicates the action of the element, overriding the action defined in the <form>.
 *
 *   <pre><input>, <button></pre>
 * @property {type} $headers
 *   IDs of the <th> elements which applies to this element.
 *
 *   <pre><td>, <th></pre>
 * @property {type} $height
 *   Note: In some instances, such as <div>, this is a legacy attribute, in which case the CSS height property should
 *   be used instead. In other cases, such as <canvas>, the height must be specified with this attribute.
 *
 *   <pre><canvas>, <embed>, <iframe>, <img>, <input>, <object>, <video></pre>
 * @property {type} $hidden
 *   Indicates the relevance of an element.
 *
 *   <b>Global attribute</b>
 * @property {type} $high
 *   Indicates the lower bound of the upper range.
 *
 *   <pre><meter></pre>
 * @property {type} $href
 *    The URL of a linked resource.
 *
 *   <pre><a>, <area>, <base>, <link></pre>
 * @property {type} $hreflang
 *   Specifies the language of the linked resource.
 *
 *   <pre><a>, <area>, <link></pre>
 * @property {type} $httpEquiv
 *
 *
 *   <pre><meta></pre>
 * @property {type} $icon
 *   Specifies a picture which represents the command.
 *
 *   <pre><command></pre>
 * @property {type} $id
 *   Often used with CSS to style a specific element. The value of this attribute must be unique.
 *
 *   <b>Global attribute</b>
 * @property {type} $ismap
 *   Indicatesthat the image is part of a server-side image map.
 *
 *   <pre><img></pre>
 * @property {type} $itemprop
 *
 *
 *   <b>Global attribute</b>
 * @property {type} $keytype
 *   Specifies the type of key generated.
 *
 *   <pre><keygen></pre>
 * @property {type} $kind
 *   Specifies the kind of text track.
 *
 *   <pre><track></pre>
 * @property {type} $label
 *   Specifies a user-readable title of the text track.
 *
 *   <pre><track></pre>
 * @property {type} $lang
 *   Defines the language used in the element.
 *
 *   <b>Global attribute</b>
 * @property {type} $language
 *   Defines the script language used in the element.
 *
 *   <pre><script></pre>
 * @property {type} $list
 *   Identifies a list of pre-defined options to suggest to the user.
 *
 *   <pre><input></pre>
 * @property {type} $loop
 *   Indicates whether the media should start playing from the start when it’s finished.
 *
 *   <pre><audio>, <bgsound>, <marquee>, <video></pre>
 * @property {type} $low
 *   Indicates the upper bound of the lower range.
 *
 *   <pre><meter></pre>
 * @property {type} $manifest
 *   Specifies the URL of the document’s cache manifest.
 *
 *   <pre><html></pre>
 * @property {type} $max
 *   Indicates the maximum value allowed.
 *
 *   <pre><input>, <meter>, <progress></pre>
 * @property {type} $maxlength
 *   Defines the maximum number of characters allowed in the element.
 *
 *   <pre><input>, <textarea></pre>
 * @property {type} $media
 *   Specifies a hint of the media for which the linked resource was designed.
 *
 *   <pre><a>, <area>, <link>, <source>, <style></pre>
 * @property {type} $method
 *   Defines which HTTP method to use when submitting the form. Can be GET (default) or POST.
 *
 *   <pre><form></pre>
 * @property {type} $min
 *   Indicates the minimum value allowed.
 *
 *   <pre><input>, <meter></pre>
 * @property {type} $multiple
 *   Indicates whether multiple values can be entered in an input of the type email or file.
 *
 *   <pre><input>, <select></pre>
 * @property {type} $name
 *   Name of the element. For example used by the server to identify the fields in form submits.
 *
 *   <pre><button>, <form>, <fieldset>, <iframe>, <input>, <keygen>, <object>, <output>, <select>, <textarea>, <map>, <meta>, <param></pre>
 * @property {type} $novalidate
 *   This attribute indicates that the form shouldn’t be validated when submitted.
 *
 *   <pre><form></pre>
 * @property {type} $open
 *   Indicates whether the details will be shown on page load.
 *
 *   <pre><details></pre>
 * @property {type} $optimum
 *   Indicates the optimal numeric value.
 *
 *   <pre><meter></pre>
 * @property {type} $pattern
 *   Defines a regular expression which the element’s value will be validated against.
 *
 *   <pre><input></pre>
 * @property {type} $ping
 *
 *
 *   <pre><a>, <area></pre>
 * @property {type} $placeholder
 *   Provides a hint to the user of what can be entered in the field.
 *
 *   <pre><input>, <textarea></pre>
 * @property {type} $poster
 *   A URL indicating a poster frame to show until the user plays or seeks.
 *
 *   <pre><video></pre>
 * @property string $prefix
 *   RDFa Light prefix, {@link http://manu.sporny.org/2011/rdfa-lite/}.
 *
 *   Many prefixes are pre-defined, {@link http://www.w3.org/2011/rdfa-context/rdfa-1.1.html}.
 *
 *   <b>Global attribute</b>
 * @property {type} $preload
 *   Indicates whether the whole resource, parts of it or nothing should be preloaded.
 *
 *   <pre><audio>, <video></pre>
 * @property string $property
 *   RDFa Light property, {@link http://manu.sporny.org/2011/rdfa-lite/}.
 *
 *   <b>Global attribute</b>
 * @property {type} $pubdate
 *   Indicates whether this date and time is the date of the nearest <article> ancestor element.
 *
 *   <pre><time></pre>
 * @property {type} $radiogroup
 *
 *
 *   <pre><command></pre>
 * @property {type} $readonly
 *   Indicates whether the element can be edited.
 *
 *   <pre><input>, <textarea></pre>
 * @property {type} $rel
 *   Specifies the relationship of the target object to the link object.
 *
 *   <pre><a>, <area>, <link></pre>
 * @property {type} $required
 *   Indicates whether this element is required to fill out or not.
 *
 *   <pre><input>, <select>, <textarea></pre>
 * @property {type} $reversed
 *   Indicates whether the list should be displayed in a descending order instead of a ascending.
 *
 *   <pre><ol></pre>
 * @property string $role
 *   ARIA role of an element: {@link https://developer.mozilla.org/en-US/docs/Web/Accessibility/ARIA/ARIA_Techniques}
 *
 *   <b>Global attribute</b>
 * @property {type} $rows
 *   Defines the number of rows in a textarea.
 *
 *   <pre><textarea></pre>
 * @property {type} $rowspan
 *   Defines the number of rows a table cell should span over.
 *
 *   <pre><td>, <th></pre>
 * @property {type} $sandbox
 *
 *
 *   <pre><iframe></pre>
 * @property {type} $spellcheck
 *   Indicates whether spell checking is allowed for the element.
 *
 *   <b>Global attribute</b>
 * @property {type} $scope
 *
 *
 *   <pre><th></pre>
 * @property {type} $scoped
 *
 *
 *   <pre><style></pre>
 * @property {type} $seamless
 *
 *
 *   <pre><iframe></pre>
 * @property {type} $selected
 *   Defines a value which will be selected on page load.
 *
 *   <pre><option></pre>
 * @property {type} $shape
 *
 *
 *   <pre><a>, <area></pre>
 * @property {type} $size
 *   Defines the width of the element (in pixels). If the element’s type attribute is text or password then it’s the
 *   number of characters.
 *
 *   <pre><input>, <select></pre>
 * @property {type} $sizes
 *
 *
 *   <pre><link></pre>
 * @property {type} $span
 *
 *
 *   <pre><col>, <colgroup></pre>
 * @property {type} $src
 *   The URL of the embeddable content.
 *
 *   <pre><audio>, <embed>, <iframe>, <img>, <input>, <script>, <source>, <track>, <video></pre>
 * @property {type} $srcdoc
 *
 *
 *   <pre><iframe></pre>
 * @property {type} $srclang
 *
 *
 *   <pre><track></pre>
 * @property {type} $start
 *   Defines the first number if other than 1.
 *
 *   <pre><ol></pre>
 * @property {type} $step
 *
 *
 *   <pre><input></pre>
 * @property {type} $style
 *   Defines CSS styles which will override styles previously set.
 *
 *   <b>Global attribute</b>
 * @property {type} $summary
 *
 *
 *   <pre><table></pre>
 * @property {type} $tabindex
 *   Overrides the browser’s default tab order and follows the one specified instead.
 *
 *   <b>Global attribute</b>
 * @property {type} $target
 *
 *
 *   <pre><a>, <area>, <base>, <form></pre>
 * @property {type} $title
 *   Text to be displayed in a tooltip when hovering over the element.
 *
 *   <b>Global attribute</b>
 * @property {type} $type
 *   Defines the type of the element.
 *
 *   <pre><button>, <input>, <command>, <embed>, <object>, <script>, <source>, <style>, <menu></pre>
 * @property string $typeof
 *   RDFa Light type, {@link http://manu.sporny.org/2011/rdfa-lite/}.
 *
 *   <b>Global attribute</b>
 * @property {type} $usemap
 *
 *
 *   <pre><img>, <input>, <object></pre>
 * @property {type} $value
 *   Defines a default value which will be displayed in the element on page load.
 *
 *   <pre><button>, <option>, <input>, <li>, <meter>, <progress>, <param></pre>
 * @property string $vocab
 *   RDFa Light vocabulary, {@link http://manu.sporny.org/2011/rdfa-lite/}.
 *
 *   <b>Global attribute</b>
 * @property {type} $width
 *   Note: In some instances, such as <div>, this is a legacy attribute, in which case the CSS width property should be
 *   used instead. In other cases, such as <canvas>, the width must be specified with this attribute.
 *
 *   <pre><canvas>, <embed>, <iframe>, <img>, <input>, <object>, <video></pre>
 * @property {type} $wrap
 *   Indicates whether the text should be wrapped.
 *
 *   <pre><textarea></pre>
 *
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2014 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class HTMLAttributes {

  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "HTMLAttributes";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Static Properties


  /**
   * ARIA attributes.
   *
   * A map that allows us to add ARIA attributes based on default HTML5 attributes. The key in the array are the HTML5
   * attributes and the values the already expanded corresponding ARIA attributes. Note that this map only represents
   * the most basic and globally useful ARIA attributes, there are many more attributes that have to be set under
   * certain conditions, but that has to be handled by a specific element implementation.
   *
   * @link http://cat.bombono.org/c/html5/ru/wai-aria.html
   * @var array
   */
  public static $aria = [
    "disabled" => " aria-disabled='true'",
    "hidden"   => " aria-hidden='true'",
    "inert"    => " aria-disabled='true'",
    "readonly" => " aria-readonly='true'",
    "required" => " aria-required='true'",
  ];

  /**
   * Enumerable attributes.
   *
   * An enumerable attribute is an attribute that can contain <code>"true"</code>, <code>"false"</code>, or an empty
   * string as value. The value cannot be omitted in contrast to boolean attributes. The keys in the array are the names
   * of known enumerable attributes and the values represent the values that an enumerable attribute has to have to be
   * included in the expanded attributes as array.
   *
   * @var array
   */
  public static $enumerable = [
    "aria-busy"       => [ "error", "true" ],
    "aria-invalid"    => [ "grammar", "spelling", "true" ],
    "contenteditable" => [ "true" ],
  ];

  /**
   * List attributes.
   *
   * A list attribute is an attribute that can contain a delimiter separated list of strings within its value. The keys
   * in vthe array are the names of known list attributes and the values contain the delimiter.
   *
   * @var array
   */
  public static $list = [
    "aria-describedby" => " ",
    "aria-labelledby"  => " ",
    "class"            => " ",
  ];


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new HTML attributes object.
   *
   * @staticvar string $requestLanguageCode
   *   Used to cache the current requests language code. This is cached in this class again for performance reasons, we
   *   don't want to ask the request class every time this object is instantiated for the current language code, as it
   *   can't change during a request. This constructor is called very often and that method call would add 100% of
   *   overhead to the construction.
   * @param array $attributes
   *   The attributes array to construct the instance from.
   * @param string $languageCode [optional]
   *   The language code of the surrounding DOM. Defaults to <code>NULL</code> and the language code of the request /
   *   document is assumed to be the surrounding DOM.
   */
  public function __construct(array $attributes = [], $languageCode = null) {
    static $requestLanguageCode = null;

    // Export all attributes to class scope.
    foreach ($attributes as $attribute => $value) {
      $this->$attribute = $value;
    }

    // Use the language code from the caller if passed.
    if ($languageCode) {
      $this->languageCode = $languageCode;
    }
    // Use the language code of the current request.
    else {
      if ($requestLanguageCode === null) {
        $requestLanguageCode = Request::getLanguageCode();
      }
      $this->languageCode = $requestLanguageCode;
    }
  }

  /**
   * Get the expanded attributes string.
   *
   * @return string
   *   The expanded attributes string.
   */
  public function __toString() {
    $attributes = "";

    // Remove the language code attribute from this instance, this ensures that it wont' be expanded.
    $languageCode = $this->languageCode;
    unset($this->languageCode);

    // Go through all properties of this instance and expand them.
    foreach (get_object_vars($this) as $name => $value) {
      // Properties are always written in camelCase, we have to expand them to use dashes instead.
      if (preg_match("/[A-Z]/", $name) === 1) {
        $name = strtolower(preg_replace("/([A-Z])/", "-$1", $name));
      }

      // Don't include the enumerable attribute if its value doesn't match the allowed value.
      if (isset(self::$enumerable[$name]) && !in_array($value, self::$enumerable[$name])) {
        continue;
      }
      // Boolean attributes have no value, note that the enumerable attribute check is performed directly before the
      // boolean attribute check. This is important because enumerable attribute have to have e.g. the string value
      // true as their content.
      elseif ($value === (boolean) $value) {
        // Don't include the boolean attribute if its value evaluates to false.
        if ($value == false) {
          continue;
        }

        // We always include the value for a boolean attribute, this ensures XML compliance and might be useful in the
        // future.
        $value = $name;
      }
      elseif (isset(self::$list[$name])) {
        // Don't include the list attribute if its value is empty.
        if (empty($value)) {
          continue;
        }

        // Expand the value of the list attribute to a delimiter separated list of values.
        $value = implode(self::$list[$name], (array) $value);
      }
      // The alt attribute is the only attribute that is allowed to be empty. Any other attribute has to have a value.
      elseif (empty($value) && $name != "alt") {
        continue;
      }
      // The lang attribute is only included if it differs from the surrounding language.
      elseif ($name == "lang" && $value == $languageCode) {
        continue;
      }
      // Special handling of data attributes. A recursive method wouldn't be of much help at this point because the
      // names might match one of the other conditions, but the values of data attributes aren't defined through the
      // HTML standard.
      elseif ($name == "data") {
        foreach ($value as $dataName => $dataValue) {
          // Omit empty data attributes.
          if (empty($dataValue)) {
            continue;
          }

          // Add this data attribute to the expanded attributes of this element.
          $attributes .= " data-{$dataName}='{$this->sanitizeValue($dataValue)}'";
        }

        // Go to next iteration.
        continue;
      }

      // Expand and include this attribute if we get this far.
      $attributes .= " {$name}='{$this->sanitizeValue($value)}'";

      // Append the expanded corresponding ARIA attribute as well if there is one.
      if (isset(self::$aria[$name])) {
        $attributes .= self::$aria[$name];
      }
    }

    // Re-create the language code property.
    $this->languageCode = $languageCode;

    // We initialized the variable with an empty string and return an empty string if no attributes are present.
    return $attributes;
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Utility method to remove a CSS class(es) from the attributes.
   *
   * @param array|string $classes
   *   The class(es) to remove.
   * @return this
   */
  public function removeClass($classes) {
    // No classes available, nothing to remove.
    if (empty($this->class)) {
      return $this;
    }
    $this->class = array_diff($this->class, (array) $classes);
    return $this;
  }

  /**
   * Sanitize attribute value.
   *
   * @param string $value
   *   The attribute value to sanitize.
   * @return string
   *   The sanitized attribute value.
   */
  protected function sanitizeValue($value) {
    // Use proper string representation for real boolean values.
    if ($value === (boolean) $value) {
      return $value ? "true" : "false";
    }

    // Encode special HTML characters within string values.
    if ($value === (string) $value) {
      return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
    }

    // Any other type (null, number, float) can be used as is.
    return (string) $value;
  }

}
