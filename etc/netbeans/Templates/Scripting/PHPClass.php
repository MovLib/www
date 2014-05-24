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
 * @todo Description of ${name}
 *
 * @author ${user}
 * @copyright © ${date?date?string("yyyy")} ${prj.name}
 * @license ${prj.licenseLink} ${prj.licenseName}
 * @link ${prj.website}
 * @since ${prj.version}
 */
class ${name} {


  // ------------------------------------------------------------------------------------------------------------------- Constants


  // @codingStandardsIgnoreStart
  /**
   * Short class name.
   *
   * @var string
   */
  const name = "${name}";
  // @codingStandardsIgnoreEnd


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   *
   */
  public function __construct() {

  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


}
