PHPUnit Accelerator
===================
[![Build Status](https://secure.travis-ci.org/mybuilder/phpunit-accelerator.svg?branch=master)](http://travis-ci.org/mybuilder/phpunit-accelerator)

Inspired by [Kris Wallsmith faster PHPUnit article](http://kriswallsmith.net/post/18029585104/faster-phpunit), we've created a [PHPUnit](http://phpunit.de) test listener that speeds up PHPUnit tests about 20% by freeing memory.

Setup and Configuration
-----------------------
Install it via composer:

    composer require "padraic/phpunit-accelerator:^2.0"

Then update the vendor libraries

    composer update --no-interaction --no-suggest --prefer-dist

Usage
-----
Just add to your `phpunit.xml` configuration
```xml
<phpunit>
    <listeners>
        <listener class="\MyBuilder\PhpunitAccelerator\TestListener"/>
    </listeners>
</phpunit>
```
