<?php

namespace sgoettsch\MonologRotatingFileHandler\Handler;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\LogRecord;

/**
 * Stores logs to files that are rotated based on file size with a maximum file amount.
 */
class MonologRotatingFileHandler extends StreamHandler
{
    protected string $filename;
    protected int $maxFiles;
    protected int $maxFileSize;
    protected bool $mustRotate;

    /**
     * @param string $filename
     * @param int $maxFiles The maximal amount of files to keep (0 means unlimited)
     * @param int $maxFileSize The maximal file size (default 10MB)
     * phpcs:ignore
     * @param 100|200|250|300|400|500|550|600|'ALERT'|'alert'|'CRITICAL'|'critical'|'DEBUG'|'debug'|'EMERGENCY'|'emergency'|'ERROR'|'error'|'INFO'|'info'|'NOTICE'|'notice'|'WARNING'|'warning'|\Monolog\Level $level
     * @param bool $bubble Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool $useLocking Try to lock log file before doing any writes
     *
     * @throws Exception
     *
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     */
    public function __construct(
        $filename,
        $maxFiles = 10,
        $maxFileSize = 10485760,
        int|string|Level $level = Level::Debug,
        bool $bubble = true,
        int $filePermission = null,
        bool $useLocking = false
    ) {
        $this->filename = $filename;
        $this->maxFiles = (int)$maxFiles;
        $this->maxFileSize = (int)$maxFileSize;

        if ($maxFileSize <= 0) {
            throw new Exception('Max file size must be higher than 0');
        }

        parent::__construct($filename, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * {@inheritdoc}
     */
    public function close(): void
    {
        parent::close();

        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        parent::reset();

        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        clearstatcache(true, $this->filename);

        if (file_exists($this->filename)) {
            $fileSize = filesize($this->filename);
            if ($fileSize >= $this->maxFileSize) {
                $this->mustRotate = true;
                $this->close();
            }
        }

        parent::write($record);
    }

    /**
     * Rotates the files.
     */
    protected function rotate(): void
    {
        // skip GC of old logs if file size is unlimited
        if ($this->maxFileSize === 0) {
            return;
        }

        // archive older files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $source = $this->filename . '.' . $i;
            clearstatcache(true, $source);
            if (file_exists($source)) {
                $target = $this->filename . '.' . ($i + 1);

                rename($source, $target);
            }
        }

        // archive latest file
        clearstatcache(true, $this->filename);
        if (file_exists($this->filename)) {
            $target = $this->filename . '.1';

            rename($this->filename, $target);
        }

        $this->mustRotate = false;
    }
}
