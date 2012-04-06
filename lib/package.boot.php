<?php
/**
 * Dieses Package Definiert die Klassen
 *
 * Psc\Boot\BootLoader
 * Psc\Boot\ClassAutoLoader (lädt PHP Dateien aus beliebigen Quellen)
 * 
 */
namespace Psc\Boot;

use Exception;
use Phar;

/**
 * Eine Klasse die Bootstrapping Prozesse vereinheitlicht
 *
 * egal ob wir vom phar
 * nativ
 * oder in binärForm aufgerufen werden
 *
 * natives PHP!
 */
class BootLoader {
  
  protected $ds = DIRECTORY_SEPARATOR;
  
  /**
   * Ein Pfad wird relativ zum Verzeichnis des BootLoaders betrachtet
   */
  const RELATIVE     = 0x000001;

  /**
   * Ein Pfad soll als absolut angesehen werden
   */
  const ABSOLUTE     = 0x000002;
  
  /**
   * Lädt die Sourcen aus den PHP-Source Dateien
   */
  const NATIVE = 'native';
  
  /**
   * Lädt die Sourcen aus den PHAR-Dateien
   */
  const PHAR = 'native';
  
  /**
   * Der Verzeichnis zu dem alle relativen Pfade ausgerichtet werden
   *
   * @var string mit trailingslash
   */
  protected $dir;
  
  /**
   * Verzeichnis in dem die Modul-Phars und Library-Phars liegen
   * 
   * @var string voller Pfad
   */
  protected $pharsDir;
  
  /**
   * @var string voller Datei-Pfad
   */
  protected $hostConfigFile;
  
  /**
   * @var Psc\Boot\AutoLoader einer für alle!
   */
  protected $autoLoader;
    
  // objekte die wir erzeugen
  
  /**
   * @var Psc\CMS\ProjectsFactory
   */
  protected $projectsFactory;
  
  /**
   * @var Psc\CMS\Configuration
   */
  protected $hostConfig;
  
  /**
   * @var bool
   */
  protected $init = FALSE;

    
  public function __construct($dir = NULL) {
    $this->dir = $this->ts($dir ?: __DIR__);
    $this->hostConfigFile = $this->dir.'host-config.php';
  }
  
  /**
   * Versucht alle Pfade die noch nicht gesetzt wurden zu erraten
   *
   * d.h. erst alle Pfade setzen und dann init() aufrufen
   * dann boot()
   */
  public function init() {
    if (!$this->init) {
      $this->init = TRUE;
      
      
    }
    return $this;
  }
  
  /**
   * Führt den Boot der Environment aus
   *
   */
  public function boot($cmsMode = self::PHAR) {
    $this->bootPscCMS($cmsMode);
    
    //$projectsFactory = $this->getProjectsFactory();
    
  }
  
  public function bootPscCMS($mode = self::PHAR) {
    /* Wir laden den entsprechenden passenden AutoLoader */
    $autoLoader = $this->getAutoLoader();
    
  }
  
  /**
   * @calls init()
   */
  public function getProjectsFactory() {
    $this->init();
    if (!isset($this->projectsFactory)) {
      $this->projectsFactory = new CMS\ProjectsFactory($this->getHostConfig());
    }
  }
  
  /**
   * @calls init()
   */
  public function getHostConfig() {
    if (!isset($this->hostConfig)) {
      require_once $this->getHostConfigFile();
      
      $this->hostConfig = new CMS\Configuration($conf);
    }
  }

  /**
   *
   * der AutoLoader wird nicht neu erstellt, wenn es schon einen gibt
   * d.h. der parameter $mode ist unwirksam wenn vorher getAutoLoader schonmal aufgerufen wurde
   *
   * Je nach Mode wird der AutoLoader mit der Library für die Nativen Sourcen oder die PharSourcen schon gefüllt
   * @return Psc\Boot\AutoLoader
   */
  public function getAutoLoader($mode = self::PHAR) {
    if (!isset($this->autoLoader)) {
      $this->autoLoader = new ClassAutoLoader();
    }
  }
  
  /**
   * @param Psc\Boot\AutoLoader
   */
  public function setAutoLoader(AutoLoader $autoLoader) {
    $this->autoLoader = $autoLoader;
    return $this;
  }
  
  /* Setters u Getters für Locations */
  
  public function setHostConfigFile($path, $fileName = 'host-config.php') {
    $this->hostConfigFile = $this->getPath($path).$fileName;
    return $this;
  }
  
  public function getHostConfigFile() {
    return $this->hostConfigFile;
  }
  
  public function setPharBinaries($path, $flags = 0x000000, Array $aliases = array()) {
    $this->pharsDir = $this->getPath($path, $flags);
    
    if (count($aliases) > 0) {
      throw new Exception('YAGNI');
    }
    return $this;
  }

  /**
   * Gibt den vollen Pfad zu einem Verzeichnis zurück
   * 
   * @return string voller Pfad zum Verzeichnis mit DIRECTORY_SEPARATOR hinten dran
   */
  public function getPath($path, $flags = 0x000000) {
    if (($flags & self::RELATIVE) === self::RELATIVE) {
      $path = $this->dir.ltrim($path,'\\/');
    }
    
    return $this->ts($path);
  }
  
  protected function ts($path) {
    return rtrim($path,'/\\').$this->ds;
  }
}

/**
 * Einen Autoloader erstellen:
 *
 * $autoLoader = new ClassAutoLoader()
 * $autoLoader->init();
 *
 * $autoLoader->addPaths(
 *   array(
 *    'Psc\CMS\MyClass'=>'D:/www/psc-cms/lib/Psc/CMS/MyClass.php'
 *   )
 * )
 *
 * fertig
 */
class ClassAutoLoader {
  
  protected $paths = array();

  /**
   * Wird die Klasse nicht gefunden wird FALSE zurückgegeben
   * Wird die Klasse gefunden + geladen wird TRUE zurückgegeben
   *
   */
  public function autoLoad($class) {
    /* in dieser funktion nur pures php verwenden */
    
    $class = ltrim($class,'\\');
    /* das ist großer dreck, hier war mal ein bug, wo ich statt \\ ltrim / gemacht habe, was natürlich bullshit ist
       das gibt aber einen geilen Fehler auf unix. Die Page wird einfach white und es gibt einen
       [apc-error] Cannot redeclare psc\code\code in phar:....
       oder ähnliches. Liegt einfach daran, dass der Autoloader die Code Datei zweimal reinwuppen kann, da sie hier
       ja falsch cononicalized wird
    */
    
    if (array_key_exists($class, $this->paths)) {
      $path = $this->paths[$class];
      
      $this->requirePath($path);
      
      return TRUE;
    }
    
    return FALSE;
  }
  
  protected function requirePath($path) {
    return require $path;
  }

  /**
   * Registriert sich mit SPL Autoload
   */
  public function register() {
    if (function_exists('__autoload')) {
      throw new Exception('konservatives __autoload verhindert das Laden dieses autoloaders');
    }
    spl_autoload_register(array($this,'autoLoad'));
  }
  
  public function init() {
    $this->register();
    return $this;
  }
  
  /**
   * Fügt ein PSR-0 Standard Verzeichnis hinzu
   *
   * jedes Unterverzeichnis (!) dieses Verzeichnisses wird als der erste Namespace ausgewertet
   *
   * lib/Psc
   * lib/Doctrine
   *
   * dann müsste zu addPSR0('/path/to/lib/') übergeben werden
   */
  public function addPSR0($directory) {
    $directory = realpath($directory);
    
    if (!is_dir($directory)) {
      throw new Exception('Angegebenes ist kein Verzeichnis: '.$directory);
    }
    
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory), \RecursiveIteratorIterator::LEAVES_ONLY);
    $parentLength = mb_strlen($directory);
    $paths = array();
    foreach ($iterator as $file) {
      if ($file->getExtension() === 'php') {
        $class = mb_substr(str_replace(DIRECTORY_SEPARATOR, '\\', mb_substr($file->getRealPath(),$parentLength+1)),0,-4); // mb_strlen('.php')
        $paths[$class] = (string) $file;
      }
    }
    
    return $this->addPaths($paths);
  }
  
  public function addPhar($pharFile) {
    $phar = new Phar($pharFile);

    $wrapped = rtrim('phar://'.$pharFile,'\\/');
    $parentLength = mb_strlen($wrapped)+1;

    $iterator = new \RecursiveIteratorIterator($phar, \RecursiveIteratorIterator::LEAVES_ONLY);
    $paths = array();
    foreach ($iterator as $file) {
      $fInfo = $file->getFileInfo();
      
      if ($fInfo->getExtension() === 'php') {
        $class = mb_substr(str_replace('/', '\\', mb_substr((string) $fInfo, mb_strlen($wrapped)+1)), 0,-4);
        $paths[$class] = (string) $fInfo;
      }
    }
    
    return $this->addPaths($paths);
  }

  /**
   * Fügt Klassen die geladen werden sollen dem AutoLoader hinzu
   *
   * Schlüssel:
   *    Namespace1\\Namespace2\\ClassName
   * Werte:
   *    /full/path/to/file/Namespace1/Namespace2/(...)/ClassName.php
   *    bzw
   *    X:/full/path/to/file/Namespace1/Namespace2/(...)/ClassName.php
   *    bzw
   *    phar://full/path/to/myphar.phar/Namespace1/Namespace2/(...)/ClassName.php
   *
   * Gleiche Klassennamen überschreiben bestehende
   * dies ist die Low-Level-Funktion
   * addPhar
   * addPSR0
   * sind die Convenience - Methoden
   */
  public function addPaths(Array $paths) {
    $this->paths = array_merge($this->paths,$paths);
    return $this;
  }

  /**
   * Überschreibt alle Pfade
   *
   * eher addPaths() benutzen
   * @param array $paths
   * @chainable
   */
  public function setPaths($paths) {
    $this->paths = $paths;
    return $this;
  }

  /**p
   * @return array
   */
  public function getPaths() {
    return $this->paths;
  }
}
?>