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


  public function dataProviderTestAllowHeadings() {
    return [
      [ 2 ],
      [ 3 ],
      [ 4 ],
      [ 5 ],
      [ 6 ],
    ];
  }

  public function dataProviderTestvalidateAInvalidURL() {
    return [
      [ "" ],                             // Empty href.
      [ "example.com" ],                  // No scheme.
      [ "http://user@example.com" ],      // Credentials.
      [ "http://user:pass@example.com" ], // Credentials.
      [ "http://example.com:8080" ],      // Port.
      [ "http://example.com/ɷ" ],         // Unescaped UTF-8.
    ];
  }

  public function dataProviderTestvalidateAValidExternal() {
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

  public function dataProviderTestvalidateAValidInternal() {
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
  public function dataProviderTestValidateFigureInvalidSrc() {
    global $kernel;
    return [
      [ ".com" ],                                     // Malformed src.
      [ "movlib.org" ],                               // Empty src scheme.
      [ "https://example.com" ],                      // External src.
      [ "https://{$kernel->domainStatic}/foo/bar" ],  // Non-existent image.
    ];
  }

  public function dataProviderTestValidateDOMInvalidBlockquoteDisallowedTags() {
    $data = [];
    $disallowedTags = $this->getProperty((new InputHTML("phpunit", "PHPUnit")), "blockquoteDisallowedTags");
    foreach ($disallowedTags as $tag => $value) {
      $content = "disallowed tag content";
      if ($tag == "ul" || $tag == "ol") {
        $content = "<li>{$content}</li>";
      }
      $data[] = [ "<{$tag}>{$content}</{$tag}>" ];
    }
    return $data;
  }

  public function dataProviderTestValidateUserClassesInvalidClass() {
    return [
      [ "invalidClass" ],
      [ "invalidClass user-center" ],
      [ "user-center invalidClass" ],
      [ "user-centerinvalidClass" ],
    ];
  }

  public function dataProviderTestValidateUserClassesValid() {
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
  public function testConstructStandardConfiguration() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $this->assertEquals("true", $input->attributes["aria-multiline"]);
    $this->assertFalse(isset($input->attributes["required"]), "Attribute required shouldn't be set by default!");
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertFalse(isset($allowedTags["figure"]), "Images shouldn't be enabled by default!");
    $this->assertFalse(isset($allowedTags["figcaption"]), "Images shouldn't be enabled by default!");
    $this->assertFalse(isset($allowedTags["blockquote"]), "Quotations shouldn't be enabled by default!");
    for ($i = 1; $i <= 6; ++$i) {
      $this->assertFalse(isset($allowedTags["h{$i}"]), "Headings shouldn't be enabled by default!");
    }
    $this->assertFalse($this->getProperty($input, "allowExternalLinks"), "External links shouldn't be enabled by default!");
  }

  /**
   * @global \MovLib\Kernel $kernel
   * @covers ::__construct
   */
  public function testConstructWithContent() {
    global $kernel;
    $content = "&lt;p&gt;PHPUnit&lt;/p&gt;";
    $input   = new InputHTML("phpunit", "PHPUnit", $content);
    $this->assertEquals($kernel->htmlDecode($content), $this->getProperty($input, "valueRaw"));
    $this->assertEquals($content, $input->value);
  }

  /**
   * @global \MovLib\Tool\Kernel $kernel
   * @covers ::__construct
   */
  public function testConstructWithPostContent() {
    global $kernel;
    $content          = "<p>PHPUnit</p>";
    $_POST["phpunit"] = $content;
    $input            = new InputHTML("phpunit", "PHPUnit", "false");
    $this->assertEquals($content, $this->getProperty($input, "valueRaw"));
    $this->assertEquals($kernel->htmlEncode($content), $input->value);
  }

  /**
   * @covers ::allowBlockqoutes
   */
  public function testAllowBlockqoutes() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowBlockqoutes();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertEquals("&lt;blockquote&gt;", $allowedTags["blockquote"]);
    $this->assertFalse(isset($allowedTags["cite"]), "<cite> should never be allowed on its own!");
  }

  /**
   * @covers ::allowExternalLinks
   */
  public function testAllowExternalLinks() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowExternalLinks();
    $this->assertTrue($this->getProperty($input, "allowExternalLinks"), "allowExternalLinks does not work properly!");
  }

  /**
   * @covers ::allowHeadings
   * @dataProvider dataProviderTestAllowHeadings
   * @param int $level
   *   The heading level to test.
   */
  public function testAllowHeadings($level) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowHeadings($level);
    $allowedTags = $this->getProperty($input, "allowedTags");
    for ($i = 1; $i <= 6; ++$i) {
      if ($i < $level) {
        $this->assertFalse(isset($allowedTags["h{$i}"]), "Heading level {$i} should not be set!");
      }
      else {
        $this->assertEquals("&lt;h{$i}&gt;", $allowedTags["h{$i}"]);
      }
    }
  }

  /**
   * @covers ::allowImages
   */
  public function testAllowImages() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowImages();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertEquals("&lt;figure&gt;", $allowedTags["figure"]);
    $this->assertFalse(isset($allowedTags["figcaption"]), "<figcaption> should never be allowed on its own!");
  }

  /**
   * @covers ::allowLists
   */
  public function testAllowLists() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowLists();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertEquals("&lt;ol&gt;", $allowedTags["ol"]);
    $this->assertEquals("&lt;ul&gt;", $allowedTags["ul"]);
  }

  /**
   * @covers ::render
   */
  public function testRenderWithPlaceholder() {
    $content = "<p>phpunit</p>";
    $id = "phpunit";
    $label   = "PHPUnit";
    $placeholder = "PHPUnit placeholder";
    $input   = new InputHTML($id, $label, $content, [ "placeholder" => $placeholder ]);
    $input->value = "wrongValue";
    $inputRendered = $input->__toString();
    $this->assertContains("<fieldset class='inputhtml'><legend>{$label}</legend><p class='jshidden'><label for='{$id}'>{$label}</label>", $inputRendered);
    $this->assertContains("<textarea placeholder='{$placeholder}' name='{$id}' id='{$id}'", $inputRendered);
    $this->assertContains("{$content}</textarea></p><div class='editor nojshidden'>", $inputRendered);
    $this->assertContains("aria-multiline='true' contenteditable='true' role='textbox' class='content'></div><span aria-hidden='true' class='placeholder'>{$placeholder}</span></div></div></fieldset>", $inputRendered);
  }

  /**
   * @covers ::render
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testRenderWithoutPlaceholder() {
    global $i18n, $kernel;
    $content = "<p>phpunit</p>";
    $label   = "PHPUnit";
    $value   = $kernel->htmlEncode($content);
    $placeholder = $i18n->t("Enter “{0}” text here …", [ $label ]);
    $input   = new InputHTML("phpunit", $label, $value);
    $inputRendered = $input->__toString();
    $this->assertContains("aria-multiline='true' placeholder='{$placeholder}'>{$content}</textarea>", $inputRendered);
    $this->assertContains("<span aria-hidden='true' class='placeholder'>{$placeholder}</span><", $inputRendered);
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
   * @expectedExceptionMessage is mandatory.
   */
  public function testValidateRequired() {
    $input = new InputHTML("phpunit", "PHPUnit", null,[ "required" => true ]);
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
   * @covers ::validateDOM
   * @dataProvider dataProviderTestValidateDOMInvalidBlockquoteDisallowedTags
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage Found disallowed tag
   * @expectedExceptionMessage in quotation.
   */
  public function testValidateDOMInvalidBlockquoteDisallowedTags($disallowedTag) {
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><blockquote>{$disallowedTag}<cite>outer cite</cite></blockquote></body></html>");
    $tidy->cleanRepair();
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowBlockqoutes();
    $input->allowImages();
    $input->allowLists();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags ]);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage Found disallowed HTML tags
   */
  public function testValidateDOMInvalidStandaloneCite() {
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><p><cite>invalid cite</cite></p></body></html>");
    $tidy->cleanRepair();
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowBlockqoutes();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags ]);
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage Found disallowed HTML tags
   */
  public function testValidateDOMInvalidTag() {
    $tidy = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><hr></body></html>");
    $tidy->cleanRepair();
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowBlockqoutes();
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags ]);
  }

  /**
   * @covers ::validateDOM
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateDOMValidCallbackValidation() {
    global $kernel;
    $tidy        = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><p><a href='https://{$kernel->domainDefault}'>movlib</a></p></body></html>");
    $tidy->cleanRepair();
    $input       = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateA"], [ "phpunit", "PHPUnit"]);
    $input->expects($this->once())->method("validateA")->will($this->returnValue("a href='//{$kernel->domainDefault}'"));
    $allowedTags = $this->getProperty($input, "allowedTags");
    $output      = $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags]);
    $this->assertEquals("<p><a href='//{$kernel->domainDefault}'>movlib</a></p>", $output);
  }

  /**
   * @covers ::validateDOM
   */
  public function testValidateDOMValidEmptyTag() {
    $tidy        = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><br></body></html>");
    $tidy->cleanRepair();
    $input       = new InputHTML("phpunit", "PHPUnit");
    $allowedTags = $this->getProperty($input, "allowedTags");
    $output      = $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags]);
    $this->assertEquals("<p><br></p>", $output);
  }

  /**
   * @covers ::validateDOM
   */
  public function testValidateDOMValidTextNode() {
    $tidy        = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>textNode</body></html>");
    $tidy->cleanRepair();
    $input       = new InputHTML("phpunit", "PHPUnit");
    $allowedTags = $this->getProperty($input, "allowedTags");
    $output      = $this->invoke($input, "validateDOM", [ $tidy->body(), &$allowedTags]);
    $this->assertEquals("<p>textNode</p>", $output);
  }

  /**
   * @covers ::validateA
   * @dataProvider dataProviderTestvalidateAInvalidURL
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $href
   *   The URL to test.
   */
  public function testValidateAInvalidURL($href) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $input->allowExternalLinks();
    $node  = (object) [ "attribute" => [ "href" => $href ] ];
    $this->invoke($input, "validateA", [ $node ]);
  }

  /**
   * @covers ::validateA
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateANoHref() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $node  = new \stdClass();
    $this->invoke($input, "validateA", [ $node ]);
  }

  /**
   * @covers ::validateA
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateANoExternal() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $node  = (object) [ "attribute" => [ "href" => "http://example.com" ] ];
    $this->invoke($input, "validateA", [ $node ]);
  }

  /**
   * @covers ::validateA
   * @dataProvider dataProviderTestvalidateAValidExternal
   * @global \MovLib\Tool\Kernel $kernel
   * @param string $scheme
   *   The scheme of the URL with trailing colon.
   * @param string $url
   *   The URL without the scheme.
   */
  public function testValidateAValidExternal($scheme, $url) {
    global $kernel;
    $input         = new InputHTML("phpunit", "PHPUnit");
    $input->allowExternalLinks();
    $node          = (object) [ "attribute" => [ "href" => "{$scheme}//{$url}"]];
    $validatedLink = $this->invoke($input, "validateA", [ $node]);
    $path          = isset($path) ? $path : "/";
    if (!in_array($scheme, [ "http:", "https:" ])) {
      $scheme = "http:";
    }
    $expectedHref = $kernel->htmlEncode("{$scheme}//{$url}");
    $this->assertEquals("a href='{$expectedHref}' rel='nofollow'", $validatedLink);
  }

  /**
   * @covers ::validateA
   * @dataProvider dataProviderTestvalidateAValidInternal
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
  public function testValidateAValidInternal($scheme, $path, $query, $fragment) {
    global $kernel;
    $input         = new InputHTML("phpunit", "PHPUnit");
    $node          = (object) [ "attribute" => [ "href" => "{$scheme}//{$kernel->domainDefault}{$path}{$query}{$fragment}"]];
    $validatedLink = $this->invoke($input, "validateA", [ $node]);
    $path          = isset($path) ? $path : "/";
    $expectedHref  = $kernel->htmlEncode("//{$kernel->domainDefault}{$path}{$fragment}");
    $this->assertEquals("a href='{$expectedHref}'", $validatedLink);
  }

  /**
   * @covers ::validateBlockquote
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage text contains a quotation without source.
   */
  public function testValidateBlockquoteEmptyCite() {
    /* @var $tidy \tidy */
    $tidy       = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><blockquote>PHPUnit quote<cite></cite></blockquote></body></html>");
    $tidy->cleanRepair();
    $input      = new InputHTML("phpunit", "PHPUnit");
    $blockquote = $tidy->body()->child[0];
    $this->invoke($input, "validateBlockquote", [ $blockquote ]);
  }

  /**
   * @covers ::validateBlockquote
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage text contains a quotation without source.
   */
  public function testValidateBlockquoteWithoutCite() {
    /* @var $tidy \tidy */
    $tidy       = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><blockquote>PHPUnit quote</blockquote></body></html>");
    $tidy->cleanRepair();
    $input      = new InputHTML("phpunit", "PHPUnit");
    $blockquote = $tidy->body()->child[0];
    $this->invoke($input, "validateBlockquote", [ $blockquote ]);
  }

  /**
   * @covers ::validateBlockquote
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage text contains quotation without text.
   */
  public function testValidateBlockquoteWithoutText() {
    /* @var $tidy \tidy */
    $tidy       = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><blockquote><cite>PHPUnit source</cite></blockquote></body></html>");
    $tidy->cleanRepair();
    $input      = new InputHTML("phpunit", "PHPUnit");
    $blockquote = $tidy->body()->child[0];
    $this->invoke($input, "validateBlockquote", [ $blockquote ]);

  }

  /**
   * @covers ::validateBlockquote
   */
  public function testValidateBlockquoteValid() {
    $quotationSource = "PHPUnit source";
    /* @var $tidy \tidy */
    $tidy       = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><blockquote class='user-left'><p>PHPUnit quotation</p><cite>{$quotationSource}</cite></blockquote></body></html>");
    $tidy->cleanRepair();
    $blockquote = $tidy->body()->child[0];
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateDOM", "validateUserClasses" ], [ "phpunit", "PHPUnit" ]);
    $input->expects($this->once())->method("validateDOM")->will($this->returnValue($quotationSource));
    $input->expects($this->once())->method("validateUserClasses")->will($this->returnValue(" class='user-left'"));
    $validatedBlockquote = $this->invoke($input, "validateBlockquote", [ $blockquote ]);
    $this->assertEquals("blockquote class='user-left'", $validatedBlockquote);
    $this->assertTrue($this->getProperty($input, "blockquote"));
    $this->assertEquals("blockquote", $this->getProperty($input, "insertLastChild"));
    $this->assertEquals("<cite>{$quotationSource}</cite>", $this->getProperty($input, "lastChild"));
  }

  /**
   * @covers ::validateFigure
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The image caption is mandatory and cannot be empty.
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateFigureEmptyCaption() {
    global $kernel;
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><figure><img src='https://{$kernel->domainStatic}/upload/user/Ravenlord.140.jpg'><figcaption></figcaption></figure></body></html>");
    $tidy->cleanRepair();
    $input  = new InputHTML("phpunit", "PHPUnit");
    $figure = $tidy->body()->child[0];
    $this->invoke($input, "validateFigure", [ $figure ]);
  }

  /**
   * @covers ::validateFigure
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The image caption is mandatory and cannot be empty.
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateFigureNoCaption() {
    global $kernel;
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><figure><img src='https://{$kernel->domainStatic}/upload/user/Ravenlord.140.jpg'></figure></body></html>");
    $tidy->cleanRepair();
    $input  = new InputHTML("phpunit", "PHPUnit");
    $figure = $tidy->body()->child[0];
    $this->invoke($input, "validateFigure", [ $figure ]);
  }

  /**
   * @covers ::validateFigure
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The image is mandatory and cannot be empty.
   */
  public function testValidateFigureNoImage() {
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><figure><img><figcaption>PHPUnit caption</figcaption></figure></body></html>");
    $tidy->cleanRepair();
    $input  = new InputHTML("phpunit", "PHPUnit");
    $figure = $tidy->body()->child[0];
    $this->invoke($input, "validateFigure", [ $figure ]);
  }

  /**
   * @covers ::validateFigure
   * @dataProvider dataProviderTestValidateFigureInvalidSrc
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $src
   *   The image's src to test.
   */
  public function testValidateFigureInvalidSrc($src) {
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><figure><img src='{$src}'><figcaption>PHPUnit caption</figcaption></figure></body></html>");
    $tidy->cleanRepair();
    $input  = new InputHTML("phpunit", "PHPUnit");
    $figure = $tidy->body()->child[0];
    $this->invoke($input, "validateFigure", [ $figure ]);
  }

  /**
   * @covers ::validateFigure
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateFigureValid() {
    global $kernel;
    $caption = "PHPUnit caption";
    $srcAfter     = "//{$kernel->domainStatic}/upload/user/Ravenlord.140.jpg";
    $src          = "https:{$srcAfter}";
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><figure><img src='{$src}'><figcaption>{$caption}</figcaption></figure></body></html>");
    $tidy->cleanRepair();
    $figure = $tidy->body()->child[0];
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateDOM", "validateUserClasses" ], [ "phpunit", "PHPUnit" ]);
    $input->expects($this->once())->method("validateDOM")->will($this->returnValue($caption));
    $input->expects($this->once())->method("validateUserClasses")->will($this->returnValue(" class='user-right'"));
    $figureValidated = $this->invoke($input, "validateFigure", [ $figure ]);
    $this->assertEquals("figure class='user-right'", $figureValidated);
    $this->assertEquals("figure", $this->getProperty($input, "insertLastChild"));
    $this->assertEquals("<img alt='{$caption}' width=\"140\" height=\"140\" src='{$srcAfter}'><figcaption>{$caption}</figcaption>", $this->getProperty($input, "lastChild"));
  }

  /**
   * @covers ::validateList
   */
  public function testValidateList() {
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><ol><li>ordered list</li></ol></body></html>");
    $tidy->cleanRepair();
    $ol = $tidy->body()->child[0];
    $input = new InputHTML("phpunit", "phpunit");
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertFalse($this->getProperty($input, "list"), "The list array should be false!");
    $olValidated = $this->invoke($input, "validateList", [ $ol ]);
    $this->assertEquals("ol", $olValidated);
    $listArray = $this->getProperty($input, "list");
    $this->assertEquals("ol", $listArray["tag"]);
    $this->assertEquals(0, $listArray["level"]);
    $this->assertEquals($allowedTags, $listArray["allowed_tags"]);
    $this->assertEquals([
        "a"      => "&lt;a&gt;",
        "b"      => "&lt;b&gt;",
        "em"     => "&lt;em&gt;",
        "i"      => "&lt;i&gt;",
        "li"     => "&lt;li&gt;",
        "ol"     => "&lt;ol&gt;",
        "strong" => "&lt;strong&gt;",
        "ul"     => "&lt;ul&gt;",
      ],
      $this->getProperty($input, "allowedTags")
    );
  }

  /**
   * @covers ::validateOl
   */
  public function testValidateOl() {
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><ol><li>ordered list</li></ol></body></html>");
    $tidy->cleanRepair();
    $ol = $tidy->body()->child[0];
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateList" ], [ "phpunit", "PHPUnit" ]);
    $input->expects($this->once())->method("validateList")->will($this->returnValue("ol"));
    $olValidated = $this->invoke($input, "validateOl", [ $ol ]);
    $this->assertEquals("ol", $olValidated);
  }

  /**
   * @covers ::validateUl
   */
  public function testValidateUl() {
    /* @var $tidy \tidy */
    $tidy   = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body><ul><li>unordered list</li></ul></body></html>");
    $tidy->cleanRepair();
    $ul = $tidy->body()->child[0];
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateList" ], [ "phpunit", "PHPUnit" ]);
    $input->expects($this->once())->method("validateList")->will($this->returnValue("ul"));
    $ulValidated = $this->invoke($input, "validateUl", [ $ul ]);
    $this->assertEquals("ul", $ulValidated);
  }

  /**
   * @covers ::validateUserClasses
   * @dataProvider dataProviderTestValidateUserClassesInvalidClass
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $class
   *   The paragraph's class to test.
   */
  public function testValidateUserClassesInvalidClass($class) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $p = (object) [ "attribute" => [ "class" => $class ] ];
    $this->invoke($input, "validateUserClasses", [ $p ]);
  }

  /**
   * @covers ::validateUserClasses
   * @dataProvider dataProviderTestValidateUserClassesValid
   * @param array $p
   *   The paragraph to test as associative array.
   */
  public function testValidateUserClassesValid($p) {
    $p = (object) $p;
    $class = isset($p->attribute["class"]) ? " class='{$p->attribute["class"]}'" : null;
    $input = new InputHTML("phpunit", "PHPUnit");
    $validatedClass = $this->invoke($input, "validateUserClasses", [ $p ]);
    $this->assertEquals("{$class}", $validatedClass);
  }

  /**
   * @covers ::validate
   * @covers ::validateDOM
   */
  public function testValidateValidMarkup() {
    global $kernel;
    $url         = "https://example.com/foo/bar?baz=42#test";
    $caption     = "Such image. Much <a href='{$url}' rel='nofollow'>source</a>. <em>Very <b>emphasize</b></em>. <i>Wow</i>.";
    $alt         = "Such image. Much source. Very emphasize. Wow.";
    $srcAfter    = "//{$kernel->domainStatic}/upload/user/Ravenlord.140.jpg";
    $src         = "https:{$srcAfter}";
    $img         = "<img src='{$src}'>";
    $imgAfter    = "<img alt='{$alt}' height=\"140\" src='{$srcAfter}' width=\"140\">";
    $markupStart = "
<h2>Le awesome heading!</h2>
<p class='user-left'>
This <i>is <b>some</b></i> <em><strong>paragraph</strong> text</em>.
</p>
<ul>
<li>listy list</li>
<li>nested list
  <ol>
    <li>listception
      <ul>
        <li>we need to go deeper...</li>
      </ul>
      </li>
  </ol>
</li>
</ul>
<blockquote class='user-center'>
<p>I am a <strong>quote</strong>!</p>
<cite><strong>This <i>is</i> <em>some <b>source</b></em> <a href='{$url}' rel='nofollow'>text</a>.</strong></cite>
</blockquote>
<figure class='user-right'>
";

    $markupEnd = "<figcaption>{$caption}</figcaption>
</figure>
";
    $markup      = "{$markupStart}{$img}{$markupEnd}";
    $markupAfter = "{$markupStart}{$imgAfter}{$markupEnd}";
    $tidy        = tidy_parse_string("<!doctype html><html><head><title>MovLib</title></head><body>{$markupAfter}</body></html>");
    $tidy->cleanRepair();
    $markupAfter = \Normalizer::normalize(str_replace("\n\n", "\n", tidy_get_output($tidy)));
    $input       = new InputHTML("phpunit", "PHPUnit", $markup);
    $input->allowBlockqoutes();
    $input->allowExternalLinks();
    $input->allowHeadings(2);
    $input->allowImages();
    $input->allowLists();
    $input->validate();
    $this->assertEquals($markupAfter, $kernel->htmlDecode($input->value));
//    echo PHP_EOL, $kernel->htmlDecode($input->value), PHP_EOL;
  }

}
