Psc - CMS - Boot
=============

PHP Bootloading Sucks - at least that helps to suck a little more less

[![Build Status](https://secure.travis-ci.org/pscheit/psc-cms-boot.png?branch=master)](http://travis-ci.org/pscheit/psc-cms-boot)

 - manages to load the autoload for composer (even if you're bootstrapping as a dependency)
 - helps in early stages bootstrapping
 - lets you bootstrap a Psc - CMS - Container for Psc - CMS - Projects

Copy the ''lib/package.boot.php'' next to your ''bootstrap.php''
```php
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = new BootLoader(__DIR__);
$bootLoader->loadComposer();
$bootLoader->registerCMSContainer(); // this is optional
```