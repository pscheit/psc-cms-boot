<?php

namespace ACME;

/**
 * a little fake class to emulate a Container for the tests
 */
class Container {
  
  public $dir;
  public $init = FALSE;

  public $autoLoader;
  
  public function __construct($dir) {
    $this->dir = $dir;
  }
  
  
  public function init() {
    $this->init = TRUE;
  }

  public function setAutoLoader($autoLoader) {
    $this->autoLoader = $autoLoader;
  }
  
}
