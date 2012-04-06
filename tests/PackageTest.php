<?php

namespace Psc\Boot;

class PackageTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->baseDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    require_once $this->baseDir.'package.boot.php';
    
    $this->bootLoader = new BootLoader();
  }
  
  public function testPackageExportsClasses() {
    $this->assertTrue(class_exists('Psc\Boot\BootLoader',FALSE),'Bootloader does not exist');
    $this->assertTrue(class_exists('Psc\Boot\ClassAutoLoader',FALSE),'ClassAutoLoader does not exist');
  }
  
  public function testBootLoaderSetsPackageFileDirAsDir() {
    $bootLoader = new BootLoader();
    $this->assertEquals($this->baseDir,$bootLoader->getPath(NULL,BootLoader::RELATIVE));
  }
  
  public function testDefaultForHostConfig() {
    $this->assertEquals($this->baseDir.'host-config.php',$this->bootLoader->getHostConfigFile()); 
  }
  
}
?>