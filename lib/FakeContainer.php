<?php

namespace Psc\CMS;

/**
 * a little fake class to emulate the Psc\CMS\Container for the tests
 */
class Container {
  
  public $dir;
  public $init = FALSE;
  
  public function __construct($dir) {
    $this->dir = $dir;
  }
  
  
  public function init() {
    $this->init = TRUE;
  }
  
}
?>