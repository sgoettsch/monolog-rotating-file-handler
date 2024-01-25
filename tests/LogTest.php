<?php
declare(strict_types = 1);

namespace sgoettsch\monologRotatingFileHandler\Test;

use Monolog\Logger;
use sgoettsch\MonologRotatingFileHandler\Handler\MonologRotatingFileHandler;

class LogTest extends \PHPUnit\Framework\TestCase
{
    protected array $data;

    public function testSuccessfulLogin(): void
    {
        // path to log file
        $filename = __DIR__.'/app.log';

        $handler = new MonologRotatingFileHandler($filename);
        $log = new Logger('name');
        $log->pushHandler($handler);

        $log->info('Foo');

        $this->assertFileExists($filename);

        $content = file_get_contents($filename);

        $this->assertStringContainsString('Foo', $content);
    }
}
