<?php

namespace Psc\Boot;

class ClassAutoLoaderTest extends \PHPUnit_Framework_TestCase {
  
  public $requiredPaths = array();
  
  protected $baseDir;
  protected $libDir;
  
  protected static $psr0Dir;
  
  public static function setUpBeforeClass() {
    self::$psr0Dir = realpath(__DIR__.'/_files/psr0/').DIRECTORY_SEPARATOR;
  }
  
  public function setUp() {
    $this->baseDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    $this->libDir = realpath(__DIR__.'/../lib/').DIRECTORY_SEPARATOR;
    require_once $this->baseDir.'package.boot.php';
    
    $this->autoLoader = $this->createAutoLoader();
  }
  
  public function assertPreConditions() {
    $this->assertFileExists(self::$psr0Dir.DIRECTORY_SEPARATOR.'Psc','Fixture Verzeichnis nicht gefunden');
    $this->assertFileExists(self::$psr0Dir.DIRECTORY_SEPARATOR.'Doctrine','Fixture Verzeichnis nicht gefunden');
  }
  
  public function testConstruct() {
    $this->assertInternalType('array',$this->autoLoader->getPaths());
    
    /*
      // mocktest
      $this->expectRequirePaths();
      $this->autoLoader->requirePath('/my/test/path.php');
      $this->assertEquals(array('/my/test/path.php'),$this->requiredPaths);
    */
  }
  
  public function testAddPaths() {
    $this->autoLoader->addPaths($paths = array(
      'Psc\CMS\MyClass'=>$this->libDir.'Psc/CMS/MyClass.php',
      'Psc\Exception'=>$this->libDir.'Psc/Exception.php'
    ));
    
    // vorher ist der autoloader ja leer
    $this->assertEquals($paths, $this->autoLoader->getPaths());
  }
  
  /**
   */
  public function testaddPSR0AddsAllPaths() {
    $this->autoLoader->addPSR0(self::$psr0Dir);
    
    $this->assertEquals(self::getPSR0Paths(), $this->autoLoader->getPaths());
  }

  public function testaddPharAddsAllPaths() {
    $phar = __DIR__.DIRECTORY_SEPARATOR.'_files'.DIRECTORY_SEPARATOR.'test.phar.gz';
    $this->autoLoader->addPhar($phar);
    
    $paths = $this->autoLoader->getPaths();
    
    $this->assertArrayHasKey('Psc\CMS\Project', $paths);
    $this->assertArrayHasKey('Psc\Exception', $paths);
    $this->assertArrayHasKey('Psc\PSC', $paths);
  }
  
  protected function createAutoLoader() {
    return $this->getMock('Psc\Boot\ClassAutoLoader', array('requirePath'));
  }
  
  protected function expectRequirePaths($invocation = NULL) {
    $invocation = $invocation ?: $this->any();
    
    $that = $this;
    $logPath = function ($path) use ($that) {
      $that->requiredPaths[] = $path;
    };
    
    $this->autoLoader->expects($invocation)->method('requirePath')
                     ->will($this->returnCallback($logPath));
  }

  public static function getPSR0Paths() {
    $psr0Dir = self::$psr0Dir;
    $path = function() use ($psr0Dir) {
      $parts = func_get_args();
      return realpath($psr0Dir.implode(DIRECTORY_SEPARATOR, $parts));
    };
    
    return array(
      'Psc\CMS\MyClass1' => $path('Psc','CMS','MyClass1.php'),
      'Psc\CMS\MyClass2' => $path('Psc','CMS','MyClass2.php'),
      'Psc\Exception' => $path('Psc','Exception.php'),
      'Doctrine\ORM\EntityManager' => $path('Doctrine','ORM','EntityManager.php'),
      'Doctrine\Common\Collections\Collection' => $path('Doctrine','Common','Collections\Collection.php')
    );
  }
}
?>