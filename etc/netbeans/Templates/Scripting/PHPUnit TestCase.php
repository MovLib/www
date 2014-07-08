<?php

<#assign licenseFirst="/*!">
<#assign licensePrefix=" *">
<#assign licenseLast=" */">
<#import "../Licenses/${project.license}.ftl" as prj>
<#include "../Licenses/license-${project.license}.txt">
<#if namespace?? && namespace?length &gt; 0>
namespace ${namespace};
</#if>
<#assign coveredClass=${name.substring(0, 4)}>

use ${namespace}\${coveredClass};

/**
 * @coversDefaultClass ${namespace}\${coveredClass}
 * @author ${user}
 * @copyright © ${date?date?string("yyyy")} ${copyright}
 * @license ${license_link} ${license_name}
 * @link ${link}
 * @since ${version}
 */
final class ${name} extends \MovLib\TestCase {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /** @var ${namespace}\${coveredClass} */
  protected $${coveredClass};


  // ------------------------------------------------------------------------------------------------------------------- Fixtures


  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->${coveredClass} = new ${coveredClass}();
  }

  /**
   * {@inheritdoc}
   */
  protected function tearDown() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Data Provider


  public function dataProviderExample() {
    return [];
  }


  // ------------------------------------------------------------------------------------------------------------------- Tests


}
