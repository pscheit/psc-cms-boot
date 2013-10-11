<?php

namespace Psc\Boot;

class PackageTest extends \PHPUnit_Framework_TestCase {
  
  public function setUp() {
    $this->baseDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    $this->testsDir = __DIR__.DIRECTORY_SEPARATOR;
    require_once $this->baseDir.'package.boot.php';
    require_once $this->baseDir.'FakeContainer.php';

    $this->containerClass = 'ACME\Container';
    
    extract($this->help());
    $this->bootLoader = new BootLoader($path('_files','boot','acceptance'), $this->containerClass);
    $this->deepBootLoader = new BootLoader($path('_files','boot','acceptance-as-dependency','vendor','ACME','library'), $this->containerClass);
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
    $this->assertInstanceOf($this->containerClass, $container = $this->bootLoader->getCMSContainer());
    $this->assertEquals(
      $this->bootLoader->getPath(NULL, BootLoader::RELATIVE),
      $container->dir
    );
  }
  
  public function testBootLoaderInittheCMSContainer() {
    $this->assertInstanceOf($this->containerClass, $container = $this->bootLoader->getCMSContainer());
    
    $this->assertTrue($container->init);
  }
  
  public function testBootLoaderRegistersTheCMSContainerAsGlobal() {
    $this->bootLoader->registerCMSContainer();
    
    $this->assertInstanceOf($this->containerClass, $GLOBALS['env']['container']);
    $this->assertSame(
      $this->bootLoader->getCMSContainer(),
      $GLOBALS['env']['container']
    );
  }
  
  public function testBootLoaderRegistersTheRootAsStringIfWebforgeCommonDirIsNotExistant() {
    extract($this->help());

    $this->bootLoader->registerRootDirectory();

    $this->assertEquals(
      $path('_files','boot','acceptance'),
      $GLOBALS['env']['root']
    );
  }

  public function testBootLoaderDoesNotOvverideGlobals() {
    $GLOBALS['env']['root'] = NULL;
    $this->bootLoader->registerPackageRoot();

    $this->assertEquals(
      NULL,
      $GLOBALS['env']['root']
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
