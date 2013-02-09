<?php

namespace Psc\Boot;

class PackageTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->baseDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    $this->testsDir = __DIR__.DIRECTORY_SEPARATOR;
    require_once $this->baseDir.'package.boot.php';
    require_once $this->baseDir.'FakeContainer.php';
    
    extract($this->help());
    $this->bootLoader = new BootLoader($path('_files','boot','acceptance'));
    $this->deepBootLoader = new BootLoader($path('_files','boot','acceptance-as-dependency','vendor','ACME','library'));
  }
  
  public function testPackageExportsBootLoadedClasse() {
    $this->assertTrue(class_exists('Psc\Boot\BootLoader',FALSE),'Bootloader does not exist');
  }
  
  public function testBootLoaderSetsPackageFileDirAsDirWhenWithNoArgument() {
    $bootLoader = new BootLoader();
    $this->assertEquals($this->baseDir,$bootLoader->getPath(NULL,BootLoader::RELATIVE));
  }

  public function testBootLoaderSetsRelativeToPackageFileDirAsDirWhenCreateWithRelative() {
    $bootLoader = BootLoader::createRelative('../tests/');
    $this->assertEquals($this->testsDir, $bootLoader->getPath(NULL,BootLoader::RELATIVE));
  }
  
  public function testAcceptanceComposerAutoLoading() {
    if (isset($GLOBALS['acceptance-autoloaded']))
      unset($GLOBALS['acceptance-autoloaded']);
    
    $this->bootLoader->loadComposer();
    
    $this->assertTrue($GLOBALS['acceptance-autoloaded']);
  }

  public function testAcceptanceComposerAutoLoadingAsDependency() {
    if (isset($GLOBALS['acceptance-deep-autoloaded']))
      unset($GLOBALS['acceptance-deep-autoloaded']);
    
    $this->deepBootLoader->loadComposer();
    
    $this->assertTrue($GLOBALS['acceptance-deep-autoloaded']);
  }
  
  public function testBootLoaderCreatesThePSCCMSContainer() {
    $this->assertInstanceOf('Psc\CMS\Container', $container = $this->bootLoader->getCMSContainer());
    $this->assertEquals(
      $this->bootLoader->getPath(NULL, BootLoader::RELATIVE),
      $container->dir
    );
  }
  
  public function testBootLoaderInittheCMSContainer() {
    $this->assertInstanceOf('Psc\CMS\Container', $container = $this->bootLoader->getCMSContainer());
    
    $this->assertTrue($container->init);
  }
  
  public function testBootLoaderRegistersTheCMSContainerAsGlobal() {
    $this->bootLoader->registerCMSContainer();
    
    $this->assertInstanceOf('Psc\CMS\Container', $GLOBALS['env']['container']);
    $this->assertSame(
      $this->bootLoader->getCMSContainer(),
      $GLOBALS['env']['container']
    );
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