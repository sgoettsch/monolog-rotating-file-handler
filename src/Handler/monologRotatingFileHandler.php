<?php
namespace sgoettsch\monologRotatingFileHandler\Handler;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

/**
 * Stores logs to files that are rotated based on file size with a maximum file amount.
 */
class monologRotatingFileHandler extends StreamHandler
{
    protected $filename;
	protected $maxFiles;
	protected $maxFileSize;
    protected $mustRotate;

    /**
     * @param string   $filename
	 * @param int      $maxFiles       The maximal amount of files to keep (0 means unlimited)
	 * @param int      $maxFileSize    The maximal file size (default 10MB)
     * @param int      $level          The minimum logging level at which this handler will be triggered
     * @param bool     $bubble         Whether the messages that are handled can bubble up the stack or not
     * @param int|null $filePermission Optional file permissions (default (0644) are only for owner read/write)
     * @param bool     $useLocking     Try to lock log file before doing any writes
     *
	 * @throws \Exception
	 */
    public function __construct($filename, $maxFiles = 10, $maxFileSize = 10485760, $level = Logger::DEBUG, $bubble = true, $filePermission = null, $useLocking = false)
    {
        $this->filename = $filename;
		$this->maxFiles = (int) $maxFiles;
		$this->maxFileSize = (int) $maxFileSize;
		
		if($maxFileSize <= 0) {
			throw new Exception('Max file soze must be higher than 0');
		}

        parent::__construct($filename, $level, $bubble, $filePermission, $useLocking);
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        parent::close();
        
        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        parent::reset();

        if ($this->mustRotate) {
            $this->rotate();
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
    	if(file_exists($this->filename)) {
			$fileSize = filesize($this->filename);
			if($fileSize >= $this->maxFileSize) {
				$this->mustRotate = true;
			}
		}

        parent::write($record);
    }

    /**
     * Rotates the files.
     */
    protected function rotate()
    {
        // skip GC of old logs if file size is unlimited
        if ($this->maxFileSize === 0) {
            return;
        }
		
        // archive older files
        for($i = $this->maxFiles - 1; $i >= 1; $i--) {
			$source = $this->filename . '.' . $i;
			if(file_exists($source)) {
				$target = $this->filename . '.' . ($i + 1);
				
				rename($source, $target);
			}
		}
        
        // archive latest file
		if(file_exists($this->filename)) {
			$target = $this->filename . '.1';
		
			rename($this->filename, $target);
		}

        $this->mustRotate = false;
    }
}
