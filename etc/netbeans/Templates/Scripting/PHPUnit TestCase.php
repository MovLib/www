<?php
<#assign licenseFirst="/*!">
<#assign licensePrefix=" *">
<#assign licenseLast=" */">
<#import "../Licenses/${project.license}.ftl" as prj>
<#include "../Licenses/license-${project.license}.txt">
<#if namespace?? && namespace?length &gt; 0>
namespace ${namespace};
</#if>

/**
 * @coversDefaultClass
 * @author ${user}
 * @copyright © ${date?date?string("yyyy")} ${copyright}
 * @license ${license_link} ${license_name}
 * @link ${link}
 * @since ${version}
 */
class ${name} extends \MovLib\Test\TestCase {

  /**
   * @covers ::__construct
   * @group
   */
  public function testConstruct() {

  }

}
