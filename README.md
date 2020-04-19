# Rotating File Handler for Monolog
Handler for PHP logging library [Monolog](https://github.com/Seldaek/monolog) for rotating files automatically based on a specific size.

## Features
* Rotate files based on files size 
* Remove files more than the X

## Installation
Install the latest version with [Composer](https://getcomposer.org/)

```bash
$ composer require sgoettsch/monolog-rotating-file-handler
```
## Basic Usage
```php
<?php

use sgoettsch\monologRotatingFileHandler\Handler\monologRotatingFileHandler;
use Monolog\Logger;

// path to log file
$filename = 'app.log';

// Instantiate handler
$handler = new monologRotatingFileHandler($filename);

// Create a log channel
$log = new Logger('name');

// Set handler
$log->pushHandler($handler);

// Add records to the log
$log->debug('Foo');
$log->warning('Bar');
$log->error('Baz');
```

## Issues
Feel free to [report any issues](https://github.com/sgoettsch/monolog-rotating-file-handler/issues/new)