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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Presentation\Partial\FormElement\InputHTML;

/**
 * @coversDefaultClass \MovLib\Presentation\Partial\FormElement\InputHTML
 * @author Markus Deutschl <mdeutschl.mmt-m2012@fh-salzburg.ac.at>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
class InputHTMLTest extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var \MovLib\Presentation\Partial\FormElement\InputHTML */
  protected $inputHTML;


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * Called before each test.
   */
  protected function setUp() {
    $this->inputHTML = new InputHTML("phpunit", "PHPUnit");
  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderTestValidateTagAInvalidURL() {
    return [
      [ "" ],                             // Empty href.
      [ "example.com" ],                  // No scheme.
      [ "http://user@example.com" ],      // Credentials.
      [ "http://user:pass@example.com" ], // Credentials.
      [ "http://example.com:8080" ],      // Port.
      [ "http://example.com/ɷ" ],         // Unescaped UTF-8.
    ];
  }

  public function dataProviderTestValidateTagAValidExternal() {
    return [
      [ "dns:", "example.com/" ],                                        // Wrong protocol.
      [ "http:", "example.com/" ],                                       // Allowed protocol #1.
      [ "https:", "example.com/" ],                                      // Allowed protocol #2.
      [ "https:", "example.com/foo/bar" ],                              // With path.
      [ "https:", "example.com/foo/bar?baz=1&powerlevel=9001" ],        // With path and query.
      [ "https:", "example.com/foo/bar#baz" ],                          // With path and fragment.
      [ "https:", "example.com/foo/bar?baz=1&powerlevel=9001#vegeta" ], // With path, query and fragment.
    ];
  }

  public function dataProviderTestValidateTagAValidInternal() {
    return [
      [ "dns:", "/my", null, null ],                        // Wrong protocol.
      [ "http:",  "/my", null, null ],                      // Non-secure protocol.
      [ "https:",  null, null, null ],                      // Path omitted.
      [ "https:",  "/my", "?query=phpunit", null ],         // With query string.
      [ "https:",  "/my", "?query=phpunit", "#fragment" ],  // With query string and fragment.
      [ "https:",  "/my", null, "#fragment" ],              // With fragment.
    ];
  }

  /**
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function dataProviderTestValidateTagImgInvalidSrc() {
    global $kernel;
    return [
      [ "" ],                                   // Empty src.
      [ ".com" ],                               // Malformed src.
      [ "movlib.org" ],                         // Empty src scheme.
      [ "https://example.com" ],                // External src.
      [ "https://{$kernel->domainStatic}/foo/bar" ],  // Non-existent image.
    ];
  }

  public function dataProviderTestValidateTagPInvalidClass() {
    return [
      [ "" ],
      [ "invalidClass" ],
      [ "invalidClass user-center" ],
      [ "user-center invalidClass" ],
      [ "user-centerinvalidClass" ],
    ];
  }

  public function dataProviderTestValidateTagPValid() {
    return [
      [ [ "attribute" => null ] ],
      [ [ "attribute" => [ "class" => "user-left" ] ] ],
      [ [ "attribute" => [ "class" => "user-center" ] ] ],
      [ [ "attribute" => [ "class" => "user-right" ] ] ],
    ];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


  /**
   * @covers ::__construct
   */
  public function testConstructAllowImages() {
    $input = new InputHTML("phpunit", "PHPUnit", "PHPUnit", [ "data-allow-img" => true ]);
    $this->assertEquals("&lt;img&gt;", $this->getProperty($input, "allowedTags")[TIDY_TAG_IMG]);
  }

  /**
   * @covers ::__construct
   */
  public function testConstructStandardConfiguration() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $this->assertEquals("true", $input->attributes["aria-multiline"]);
    $this->assertFalse(isset($this->getProperty($input, "allowedTags")[TIDY_TAG_IMG]), "Image tags shouldn't be enabled by default!");
    $this->assertFalse(isset($input->attributes["data-external"]), "External links shouldn't be enabled by default!");
  }

  /**
   * @covers ::__construct
   */
  public function testConstructWithContent() {
    $content = "PHPUnit";
    $input   = new InputHTML("phpunit", "PHPUnit", $content);
    $this->assertEquals($content, $this->getProperty($input, "contentRaw"));
  }

  /**
   * @covers ::__construct
   */
  public function testConstructWithPostContent() {
    $content          = "PHPUnit";
    $_POST["phpunit"] = $content;
    $input            = new InputHTML("phpunit", "PHPUnit", "false");
    $this->assertEquals($content, $this->getProperty($input, "contentRaw"));
  }

  /**
   * @covers ::__toString
   */
  public function testToStringWithRawContent() {
    $content = "<p>phpunit</p>";
    $label   = "PHPUnit";
    $markup  = "<fieldset><legend>{$label}</legend><div contenteditable='true'>{$content}</div></fieldset>";
    $input   = new InputHTML("phpunit", $label, $content);
    $input->value = "wrongValue";
    $this->assertContains("<fieldset><legend>{$label}</legend><div ", $input->__toString());
    $this->assertContains("{$content}</div></fieldset>", $input->__toString());
  }

  /**
   * @covers ::__toString
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testToStringWithoutRawContent() {
    global $kernel;
    $content = "<p>phpunit</p>";
    $label   = "PHPUnit";
    $markup  = "<fieldset><legend>{$label}</legend><div contenteditable='true'>{$content}</div></fieldset>";
    $value   = $kernel->htmlEncode($content);
    $input   = new InputHTML("phpunit", $label);
    $input->value = $value;
    $this->assertContains("<fieldset><legend>{$label}</legend><div ", $input->__toString());
    $this->assertContains("{$content}</div></fieldset>", $input->__toString());
  }

  /**
   * @covers ::validate
   * @todo Implement validate
   */
  public function testValidate() {
    $this->markTestIncomplete("This test has not been implemented yet.");
  }

  /**
   * @covers ::validate
   * @todo Implement validate
   */
  public function testValidateEmpty() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->validate();
    $this->assertEquals("", $input->value);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateInvalidTag() {
    $input = new InputHTML("phpunit", "PHPUnit", "<hr>");
    $input->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateRequired() {
    $input = new InputHTML("phpunit", "PHPUnit", null, [ "required" => true ]);
    $input->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateTidyParseError() {
    $input = new InputHTML("phpunit", "PHPUnit", "<invalidTag>");
    $input->validate();
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateValidCallbackValidation() {
    global $kernel;
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateTagP" ], [ "phpunit", "PHPUnit", "<p>phpunit</p>" ]);
    $input->expects($this->once())->method("validateTagP")->will($this->returnValue("p"));
    $input->validate();
    $this->assertEquals($kernel->htmlEncode("<p>phpunit</p>"), $input->value);
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateValidEmptyTag() {
    global $kernel;
    $input = new InputHTML("phpunit", "PHPUnit", "<br>");
    $input->validate();
    $this->assertEquals($kernel->htmlEncode("<p><br></p>"), $input->value);
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateValidMarkup() {
    global $kernel;
    $input = new InputHTML("phpunit", "PHPUnit", "<p><a href='https://{$kernel->domainDefault}'>MovLib<img alt='phpunit' src='https://{$kernel->domainStatic}/user/Ravenlord.140.jpg'></a></p>textNode");
    $input->validate();
    $this->assertEquals($kernel->htmlEncode("<p><a href='https://{$kernel->domainDefault}'>MovLib<img alt='phpunit' src='https://{$kernel->domainStatic}/user/Ravenlord.140.jpg'></a></p>\n<p>textNode</p>"), $input->value);
  }

  /**
   * @covers ::validate
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateValidTextNode() {
    global $kernel;
    $input = new InputHTML("phpunit", "PHPUnit", "textNode");
    $input->validate();
    $this->assertEquals($kernel->htmlEncode("<p>textNode</p>"), $input->value);
  }

  /**
   * @covers ::validateTagA
   * @dataProvider dataProviderTestValidateTagAInvalidURL
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $href
   *   The URL to test.
   */
  public function testValidateTagAInvalidURL($href) {
    $input = new InputHTML("phpunit", "PHPUnit", null, [ "data-external" => true]);
    $node  = (object) [ "attribute" => [ "href" => $href ] ];
    $this->invoke($input, "validateTagA", [ $node ]);
  }

  /**
   * @covers ::validateTagA
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateTagANoHref() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $node  = new \stdClass();
    $this->invoke($input, "validateTagA", [ $node ]);
  }

  /**
   * @covers ::validateTagA
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateTagANoExternal() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $node  = (object) [ "attribute" => [ "href" => "http://example.com" ] ];
    $this->invoke($input, "validateTagA", [ $node ]);
  }

  /**
   * @covers ::validateTagA
   * @dataProvider dataProviderTestValidateTagAValidExternal
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $scheme
   *   The scheme of the URL with trailing colon.
   * @param string $url
   *   The URL without the scheme.
   */
  public function testValidateTagAValidExternal($scheme, $url) {
    global $kernel;
    $input         = new InputHTML("phpunit", "PHPUnit", null, [ "data-external" => true]);
    $node          = (object) [ "attribute" => [ "href" => "{$scheme}//{$url}"]];
    $validatedLink = $this->invoke($input, "validateTagA", [ $node]);
    $path          = isset($path) ? $path : "/";
    if (!in_array($scheme, [ "http:", "https:" ])) {
      $scheme = "http:";
    }
    $expectedHref = $kernel->htmlEncode("{$scheme}//{$url}");
    $this->assertEquals("a href='{$expectedHref}' rel='nofollow'", $validatedLink);
  }

  /**
   * @covers ::validateTagA
   * @dataProvider dataProviderTestValidateTagAValidInternal
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $scheme
   *   The scheme of the URL with trailing colon.
   * @param string $path
   *   The path of the URL.
   * @param string $query
   *   The query string of the URL.
   * @param string $fragment
   *   The hash part of the URL.
   */
  public function testValidateTagAValidInternal($scheme, $path, $query, $fragment) {
    global $kernel;
    $input         = new InputHTML("phpunit", "PHPUnit");
    $node          = (object) [ "attribute" => [ "href" => "{$scheme}//{$kernel->domainDefault}{$path}{$query}{$fragment}"]];
    $validatedLink = $this->invoke($input, "validateTagA", [ $node]);
    $path          = isset($path) ? $path : "/";
    $expectedHref  = $kernel->htmlEncode("//{$kernel->domainDefault}{$path}{$fragment}");
    $this->assertEquals("a href='{$expectedHref}'", $validatedLink);
  }

  /**
   * @covers ::validateTagImg
   * @dataProvider dataProviderTestValidateTagImgInvalidSrc
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $src
   *   The image's src to test.
   */
  public function testValidateTagImgInvalidSrc($src) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $img   = (object) [ "attribute" => [ "src" => $src ] ];
    $this->invoke($input, "validateTagImg", [ $img ]);
  }

  /**
   * @covers ::validateTagImg
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateTagImgNoSrc() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $img   = new \stdClass();
    $this->invoke($input, "validateTagImg", [ $img ]);
  }

  /**
   * @covers ::validateTagImg
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateTagImgValid() {
    global $kernel;
    $input        = new InputHTML("phpunit", "PHPUnit");
    $alt          = "phpunit alt text";
    $src          = "https://{$kernel->domainStatic}/user/Ravenlord.140.jpg";
    $img          = (object) [ "attribute" => [ "alt" => $alt, "src" => $src]];
    $validatedImg = $this->invoke($input, "validateTagImg", [ $img ]);
    $this->assertEquals("img alt='{$alt}' height='140' src='{$src}' width='140'", $validatedImg);
  }

  /**
   * @covers ::validateTagImg
   */
  public function testValidateTagImgValidNoAlt() {
    global $kernel;
    $input        = new InputHTML("phpunit", "PHPUnit");
    $src          = "https://{$kernel->domainStatic}/user/Ravenlord.140.jpg";
    $img          = (object) [ "attribute" => [ "src" => $src]];
    $validatedImg = $this->invoke($input, "validateTagImg", [ $img ]);
    $this->assertEquals("img alt='' height='140' src='{$src}' width='140'", $validatedImg);
  }

  /**
   * @covers ::validateTagP
   * @dataProvider dataProviderTestValidateTagPInvalidClass
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $class
   *   The paragraph's class to test.
   */
  public function testValidateTagPInvalidClass($class) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $p = (object) [ "attribute" => [ "class" => $class ] ];
    $this->invoke($input, "validateTagP", [ $p ]);
  }

  /**
   * @covers ::validateTagP
   * @dataProvider dataProviderTestValidateTagPValid
   * @param array $p
   *   The paragraph to test as associative array.
   */
  public function testValidateTagPValid($p) {
    $p = (object) $p;
    $class = isset($p->attribute["class"]) ? " class='{$p->attribute["class"]}'" : null;
    $input = new InputHTML("phpunit", "PHPUnit");
    $validatedP = $this->invoke($input, "validateTagP", [ $p ]);
    $this->assertEquals("p{$class}", $validatedP);
  }

}
