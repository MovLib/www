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
      [ "" ],                                   // Empty src.
      [ ".com" ],                               // Malformed src.
      [ "movlib.org" ],                         // Empty src scheme.
      [ "https://example.com" ],                // External src.
      [ "https://{$kernel->domainStatic}/foo/bar" ],  // Non-existent image.
    ];
  }

  public function dataProviderTestValidatePInvalidClass() {
    return [
      [ "" ],
      [ "invalidClass" ],
      [ "invalidClass user-center" ],
      [ "user-center invalidClass" ],
      [ "user-centerinvalidClass" ],
    ];
  }

  public function dataProviderTestValidatePValid() {
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
    $this->assertEquals("true", $input->attributes["contenteditable"]);
    $this->assertEquals("textbox", $input->attributes["role"]);
    $this->assertFalse(isset($input->attributes["name"]), "Attribute name is not permitted by HTML standard!");
    $this->assertFalse(isset($input->attributes["required"]), "Attribute required is not permitted by HTML standard!");
    $allowedTags = $this->getProperty($input, "allowedTags");
    $this->assertFalse(isset($allowedTags["figure"]), "Images shouldn't be enabled by default!");
    $this->assertFalse(isset($allowedTags["figcaption"]), "Images shouldn't be enabled by default!");
    for ($i = 1; $i <= 6; ++$i) {
      $this->assertFalse(isset($allowedTags["h{$i}"]), "Headings shouldn't be enabled by default!");
    }
    $this->assertFalse($this->getProperty($input, "allowExternalLinks"), "External links shouldn't be enabled by default!");
    $this->assertFalse($this->getProperty($input, "required"), "Texts should not be required by default!");
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
   * @covers ::render
   */
  public function testRenderWithPlaceholder() {
    $content = "<p>phpunit</p>";
    $label   = "PHPUnit";
    $placeholder = "PHPUnit placeholder";
    $input   = new InputHTML("phpunit", $label, $content, $placeholder);
    $input->value = "wrongValue";
    $inputRendered = $input->__toString();
    $this->assertContains("<fieldset><legend>{$label}</legend><div ", $inputRendered);
    $this->assertContains("{$content}</div><span aria-hidden='true' class='placeholder'>{$placeholder}</span></div></fieldset>", $inputRendered);
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
    $placeholder = $i18n->t("Enter the “{0}” text here …", [ $label ]);
    $input   = new InputHTML("phpunit", $label, $value);
    $inputRendered = $input->__toString();
    $this->assertContains("<fieldset><legend>{$label}</legend><div class='inputhtml'", $inputRendered);
    $this->assertContains("{$content}</div><span aria-hidden='true' class='placeholder'>{$placeholder}</span></div></fieldset>", $inputRendered);
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
   * @expectedExceptionMessage The “PHPUnit” text contains a quotation without a supplied source.
   */
  public function testValidateInvalidBlockquoteWithoutCite() {
    $input = new InputHTML("phpunit", "PHPUnit", "<p><blockquote><p>missing citation</p></blockquote></p>");
    $input->allowBlockqoutes();
    $input->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The “PHPUnit” text contains the invalid element <code><blockquote></code> inside a quotation or a figure.
   */
  public function testValidateInvalidBlockquoteWithBlockquote() {
    $input = new InputHTML("phpunit", "PHPUnit", "<p><blockquote><p>this is a quote<blockquote><p>this is a nested quote</p><cite>inner cite</cite></blockquote></p><cite>outer cite</cite></blockquote></p>");
    $input->allowBlockqoutes();
    $input->allowImages();
    $input->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The “PHPUnit” text contains the invalid element <code><figure></code> inside a quotation or a figure.
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateInvalidBlockquoteWithFigure() {
    global $kernel;
    $input = new InputHTML("phpunit", "PHPUnit", "<p><blockquote><figure><figcaption>caption</figcaption><img src='https://{$kernel->domainStatic}/user/Ravenlord.140.jpg'></figure><cite>cite</cite></blockquote></p>");
    $input->allowBlockqoutes();
    $input->allowImages();
    $input->validate();
  }

  /**
   * @covers ::validate
   * @expectedException \MovLib\Exception\ValidationException
   * @expectedExceptionMessage The “PHPUnit” text contains invalid HTML tags.
   */
  public function testValidateInvalidStandaloneCite() {
    $input = new InputHTML("phpunit", "PHPUnit", "<p><cite>wrong citation</cite></p>");
    $input->allowBlockqoutes();
    $input->validate();
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
    $input = new InputHTML("phpunit", "PHPUnit", [ "required" => true ]);
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
    $input = $this->getMock("\\MovLib\\Presentation\\Partial\\FormElement\\InputHTML", [ "validateP" ], [ "phpunit", "PHPUnit", "<p>phpunit</p>" ]);
    $input->expects($this->once())->method("validateP")->will($this->returnValue("p"));
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
    $input = new InputHTML(
      "phpunit",
      "PHPUnit",
      "<p><a href='https://{$kernel->domainDefault}'>MovLib<img alt='phpunit' src='https://{$kernel->domainStatic}/user/Ravenlord.140.jpg'></a></p>textNode",
      [ "data-allow-img" => true ]
    );
    $input->allowImages();
    $input->validate();
    $this->assertEquals($kernel->htmlEncode("<p><a href='//{$kernel->domainDefault}/'>MovLib<img alt='phpunit' height='140' src='//{$kernel->domainStatic}/user/Ravenlord.140.jpg' width='140'></a></p>\n<p>textNode</p>"), $input->value);
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
   * @covers ::validateFigure
   * @dataProvider dataProviderTestValidateFigureInvalidSrc
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $src
   *   The image's src to test.
   */
  public function testValidateFigureInvalidSrc($src) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $img   = (object) [ "attribute" => [ "src" => $src ] ];
    $this->invoke($input, "validateFigure", [ $img ]);
  }

  /**
   * @covers ::validateFigure
   * @expectedException \MovLib\Exception\ValidationException
   */
  public function testValidateFigureNoSrc() {
    $input = new InputHTML("phpunit", "PHPUnit");
    $img   = new \stdClass();
    $this->invoke($input, "validateFigure", [ $img ]);
  }

  /**
   * @covers ::validateFigure
   * @global \MovLib\Tool\Kernel $kernel
   */
  public function testValidateFigureValid() {
    global $kernel;
    $input        = new InputHTML("phpunit", "PHPUnit");
    $alt          = "phpunit alt text";
    $srcAfter     = "//{$kernel->domainStatic}/user/Ravenlord.140.jpg";
    $src          = "https:{$srcAfter}";
    $img          = (object) [ "attribute" => [ "alt" => $alt, "src" => $src]];
    $validatedImg = $this->invoke($input, "validateFigure", [ $img ]);
    $this->assertEquals("img alt='{$alt}' height='140' src='{$srcAfter}' width='140'", $validatedImg);
  }

  /**
   * @covers ::validateFigure
   */
  public function testValidateFigureValidNoAlt() {
    global $kernel;
    $input        = new InputHTML("phpunit", "PHPUnit");
    $srcAfter     = "//{$kernel->domainStatic}/user/Ravenlord.140.jpg";
    $src          = "https:{$srcAfter}";
    $img          = (object) [ "attribute" => [ "src" => $src]];
    $validatedImg = $this->invoke($input, "validateFigure", [ $img ]);
    $this->assertEquals("img alt='' height='140' src='{$srcAfter}' width='140'", $validatedImg);
  }

  /**
   * @covers ::validateP
   * @dataProvider dataProviderTestValidatePInvalidClass
   * @expectedException \MovLib\Exception\ValidationException
   * @param string $class
   *   The paragraph's class to test.
   */
  public function testValidatePInvalidClass($class) {
    $input = new InputHTML("phpunit", "PHPUnit");
    $p = (object) [ "attribute" => [ "class" => $class ] ];
    $this->invoke($input, "validateP", [ $p ]);
  }

  /**
   * @covers ::validateP
   * @dataProvider dataProviderTestValidatePValid
   * @param array $p
   *   The paragraph to test as associative array.
   */
  public function testValidatePValid($p) {
    $p = (object) $p;
    $class = isset($p->attribute["class"]) ? " class='{$p->attribute["class"]}'" : null;
    $input = new InputHTML("phpunit", "PHPUnit");
    $validatedP = $this->invoke($input, "validateP", [ $p ]);
    $this->assertEquals("p{$class}", $validatedP);
  }

}
