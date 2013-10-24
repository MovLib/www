<?php
<#assign licenseFirst = "/*!">
<#assign licensePrefix = " *">
<#assign licenseLast = " */">
<#include "../Licenses/license-${project.license}.txt">
namespace ${namespace};

/**
 * Description of ${name}
 *
 * @author ${user}
 * @copyright © ${date?date?string("yyyy")} ${copyright}
 * @license ${license_link} ${license_name}
 * @link ${link}
 * @since ${version}
 */
class ${name} {

  /**
   *
   */
  public function __construct() {

  }

}
