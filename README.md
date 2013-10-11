Psc - CMS - Boot
=============

PHP Bootloading Sucks - at least that helps to suck a little more less

[![Build Status](https://secure.travis-ci.org/pscheit/psc-cms-boot.png?branch=master)](http://travis-ci.org/pscheit/psc-cms-boot)

 - manages to load the autoload for composer (even if you're bootstrapping as a dependency)
 - helps in early stages bootstrapping
 - lets you bootstrap a Psc - CMS - Container for Psc - CMS - Projects

Copy the `lib/package.boot.php` next to your `bootstrap.php`
```php
use Psc\Boot\BootLoader;

require 'package.boot.php';
$bootLoader = new BootLoader(__DIR__, 'ACME\Container');
$bootLoader->loadComposer();
$bootLoader->registerContainer(); // this is optional
```

When you registered the Container `$GLOBALS['env']['container']` points to the `ACME\Container`.
When you do `$bootLoader->registerRootDirectory` the `$GLOBALS['env']['root']` points to a `\Webforge\Common\System\Dir` which is the directory of your bootstrap.php (given here as __DIR__).
