<?php

namespace Psc\Boot;

class PackageTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->baseDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    $this->testsDir = __DIR__.DIRECTORY_SEPARATOR;
    require_once $this->baseDir.'package.boot.php';
    
    extract($this->help());
    $this->bootLoader = new BootLoader($path('_files','boot','acceptance','bin'));
  }
  
  public function testPackageExportsClasses() {
    $this->assertTrue(class_exists('Psc\Boot\BootLoader',FALSE),'Bootloader does not exist');
    $this->assertTrue(class_exists('Psc\Boot\ClassAutoLoader',FALSE),'ClassAutoLoader does not exist');
  }
  
  public function testBootLoaderSetsPackageFileDirAsDir() {
    $bootLoader = new BootLoader();
    $this->assertEquals($this->baseDir,$bootLoader->getPath(NULL,BootLoader::RELATIVE));
  }

  public function testPscClassPathFindsSrc() {
    extract($this->help());
    // finds src
    $bootLoader = new BootLoader($path('_files','boot','withsrc','bin'));
    
    $this->assertEquals($path('_files','boot','withsrc','src'), $bootLoader->getPscClassPath());
  }
    
  public function testPscClassPathFindsLib() {
    extract($this->help());
    // finds lib
    $bootLoader = new BootLoader($path('_files','boot','withlib','bin'));
    
    $this->assertEquals($path('_files','boot','withlib','lib'), $bootLoader->getPscClassPath());
  }
  
  public function testPscClassDefaultsSelf() {
    extract($this->help());
    $bootLoader = new BootLoader($path('_files'));
    
    $this->assertEquals($path('_files'), $bootLoader->getPscClassPath());
  }
  
  public function testGetPharSuccess() {
    extract($this->help());
    $bootLoader = new BootLoader($path('_files'));
    
    $this->assertFileExists($bootLoader->getPhar('test'));
    $this->assertFileExists($bootLoader->getPhar('test.phar.gz'));
  }
  
  /**
   * @expectedException Psc\Boot\Exception
   */
  public function testGetPharEx() {
    $this->bootLoader->getPhar('none');
  }
  
  public function testDefaultForHostConfig() {
    $bootLoader = new BootLoader();
    $this->assertEquals($this->baseDir.'host-config.php',$bootLoader->getHostConfigFile()); 
  }
  
  public function testgetAutoLoader() {
    $this->assertInstanceOf('Psc\Boot\ClassAutoLoader',$this->bootLoader->getAutoLoader());
  }
  
  public function testAcceptance() {
    $this->bootLoader->init();
    
    $this->assertInstanceOf('Psc\CMS\ProjectsFactory', $this->bootLoader->getProjectsFactory());
    $this->assertInstanceOf('Psc\Boot\ClassAutoLoader', $this->bootLoader->getAutoLoader());
  }
  
  public function testGetDir() {
    extract($this->help());
    $this->bootLoader->init();
    
    $this->assertInstanceOf('Psc\System\Dir', $pRoot = $this->bootLoader->getDir('../customProjectsRoot/', BootLoader::RELATIVE));
    $this->assertEquals($path('_files','boot','acceptance','customProjectsRoot'), (string) $pRoot);
  }
  
  public function testSetProjectsRoot() {
    $this->bootLoader->init();
    $projectsRoot = $this->bootLoader->getDir('../customProjectsRoot');
    $this->bootLoader->setProjectsRoot($projectsRoot);
    
    // wenn das hier eine exception schmeisst, hats nicht geklappt. da in "unserer" host-config kein eintrag für projectsroot ist
    $this->assertSame($projectsRoot, $this->bootLoader->getProjectsFactory()->getProjectsRoot());
  }


  public function help() {
    $baseDir = $this->testsDir;
    $path = function() use ($baseDir) {
      $parts = func_get_args();
      return realpath($baseDir.implode(DIRECTORY_SEPARATOR, $parts)).DIRECTORY_SEPARATOR;
    };
    
    return compact('path');
  }
}
?>