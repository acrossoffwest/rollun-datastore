<?php

namespace rollun\datastore\Csv;

use Ajgl\Csv\Rfc;

class CsvFileObject extends Rfc\Spl\SplFileObject
{

    const LOCK_TRIES_TIMEOUT = 50; //in ms
    const MAX_LOCK_TRIES = 40;

    /**
     * Buffer size in lines for coping operation
     */
    const BUFFER_SIZE = 3;  //i

    /**
     *
     * @var array
     */
    protected $columns;

    /**
     *
     * @param string $filename
     * @param string $rwMode see 'mode' in http://php.net/manual/en/function.fopen.php
     */
    public function __construct($filename)
    {
        parent::__construct($filename, 'c+');
        $this->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_AHEAD); //\SplFileObject::DROP_NEW_LINE |
        //$this->setCsvControl(',', '"', '"');
        $this->getColumns();
    }

    public function __destruct()
    {
        $this->unlock();
    }

    /**
     *
     * @param int  $lockMode LOCK_SH or LOCK_EX
     * @param type $maxTries
     * @param type $timeout in ms
     * @throws DataStoreException
     */
    public function lock($lockMode, $maxTries = null, $lockTriesTimeout = null)
    {
        $maxTries = $maxTries ?? static::MAX_LOCK_TRIES;
        $lockTriesTimeout = $lockTriesTimeout ?? static::LOCK_TRIES_TIMEOUT;

        if ($lockMode <> LOCK_SH && $lockMode <> LOCK_EX) {
            throw new \InvalidArgumentException('$lockMode must be LOCK_SH or LOCK_EX');
        }

        $count = 0;
        while (!$this->flock($lockMode | LOCK_NB, $wouldblock)) {
            if (!$wouldblock) {
                throw new DataStoreException('There is a problem with file: ' . $this->filename);
            }
            if ($count++ > $maxTries) {
                throw new DataStoreException('Can not lock the file: ' . $this->filename);
            }
            usleep($lockTriesTimeout);
        }
    }

    public function unlock()
    {
        return $this->flock(LOCK_UN);
    }

    public function getColumns()
    {
        if (empty($this->columns)) {
            $this->lock(LOCK_SH);
            parent::rewind();
            $current = parent::current();
            $this->columns = is_array($current) ? $current : trim($current);
            $this->unlock();
        }
        return $this->columns;
    }

//
    public function rewind()
    {
        parent::rewind();
        parent::current();
        parent::next();
        parent::current();
    }

//
//    public function key()
//    {
//        if (parent::key() === 0 && $this->isCsvMode()) {
//            parent::current();
//            parent::next();
//        }
//        return parent::key();
//    }
//
//    /**
//     * @see https://stackoverflow.com/questions/1504927/splfileobject-next-behavior/1504981#1504981
//     */
//    public function next()
//    {
//        if (parent::key() === 0 && $this->isCsvMode()) {
//            parent::current();
//            parent::next();
//        }
//        parent::next();
//        //parent::current();
//    }
//
//    public function current()
//    {
//        if (parent::key() === 0 && $this->isCsvMode()) {
//            parent::current();
//            parent::next();
//        }
//
//        $row = parent::current();
//        if ([null] === $row) {
//            return null;
//        }
//        return $row;
//    }
//
//    public function valid()
//    {
//        if (parent::key() === 0 && $this->isCsvMode()) {
//            parent::current();
//            parent::next();
//        }
//        return parent::valid(); // && !empty(parent::current())
//    }

    public function deleteRow($linePos)
    {
        if ($linePos === 0) {
            throw new \InvalidArgumentException('Can not delete header of CSV file.');
        }
        $this->csvModeOff();
        $this->lock(LOCK_EX);

        parent::seek($linePos - 1);
        parent::current();
        $charPosTo = $this->ftell();
        parent::next();
        parent::current();
        $charPosFrom = $this->ftell();

        $truncatePos = $this->moveRows($charPosFrom, $charPosTo);

        $this->fflush();
        $this->ftruncate($truncatePos);

        $this->restorePrevCsvMode();
        $this->unlock();
    }

    protected function moveRows($charPosFrom, $charPosTo)
    {
        $this->fseek($charPosFrom);
        while ($this->valid()) {
            $this->fseek($charPosFrom);
            parent::current();

            $buffer = [];
            while ($this->valid() && count($buffer) <= static::BUFFER_SIZE) {
                $buffer[] = $this->current();
                $charPosFrom = $this->ftell();
                $this->next();
            }

            $this->fseek($charPosTo);
            foreach ($buffer as $key => $line) {
                $this->fwrite($line . chr(10));  //$this->fputcsv($line); in csv mode
                $charPosTo = $this->ftell();
            }

            $this->fseek($charPosFrom);
            $current = parent::current();
        }
        return $charPosTo;
    }

}
