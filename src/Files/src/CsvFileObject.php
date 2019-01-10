<?php

namespace rollun\files;

use rollun\files\FileObject;
use rollun\utils\Json\Serializer;

class CsvFileObject implements \IteratorAggregate
{

    /**
     *
     * @var FileObject
     */
    protected $fileObject;

    /**
     *
     * @var array
     */
    protected $columns;

    public static function createNewCsvFile(string $filename, array $columnsNames)
    {
        if (is_readable($filename)) {
            throw new \InvalidArgumentException(
            "There is readable file: " . $filename
            );
        }
        $fileObject = new FileObject($filename);
        $fileObject->fputcsv($columnsNames);
    }

    public function __construct(string $filename)
    {
        if (!is_readable($filename)) {
            throw new \InvalidArgumentException(
            "There is not readable file: " . $filename
            );
        }
        $this->fileObject = new FileObject($filename);
        $this->fileObject->setFlags(\SplFileObject::READ_CSV); //| \SplFileObject::DROP_NEW_LINE | \SplFileObject::READ_AHEAD |\SplFileObject: \SplFileObject::SKIP_EMPTY | \SplFileObject::READ_CSV
        $this->getColumns();
    }

    public function getFileObject()
    {
        return $this->fileObject;
    }

    public function getColumns()
    {
        if (empty($this->columns)) {
            $this->fileObject->lock(LOCK_SH);
            $this->fileObject->rewind();
            $current = $this->fileObject->current();
            $this->fileObject->unlock();
            if (!is_array($current)) {
                throw new \InvalidArgumentException(
                "There is not colums names in file: "
                . $this->fileObject->getRealPath()
                );
            }
            $this->columns = $current;
        }
        return $this->columns;
    }

    public function hasData()
    {
        $stringsCount = $this->fileObject->getStringsCount();
        return $stringsCount > 1;
    }

    public function getRow($zeroBasedStringNumber)
    {
        $this->fileObject->lock(LOCK_SH);
        $stringsCount = $this->fileObject->getStringsCount();
        if ($stringsCount - 2 < $zeroBasedStringNumber) {
            throw new \InvalidArgumentException(
            "\$zeroBasedStringNumber = $zeroBasedStringNumber .  Strings count with colums = $stringsCount \n in file: "
            . $this->fileObject->getRealPath()
            );
        }
        $this->fileObject->seek($zeroBasedStringNumber + 1);
        $row = $this->fileObject->current();
        $this->fileObject->unlock();
        return $row;
    }

    public function addRow(array $dataArray)
    {
        $this->fileObject->lock(LOCK_SH);
        $length = $this->fileObject->fputcsv($dataArray);
        if ($length === false) {
            $dataInJson = Serializer::jsonSerialize($dataArray);
            throw new \InvalidArgumentException(
            "Can not write data:  $dataInJson \n in file: "
            . $this->fileObject->getRealPath()
            );
        }
        $this->fileObject->unlock();
        return $length;
    }

    public function deleteAllRows()
    {
        $this->fileObject->lock(LOCK_SH);
        $this->fileObject->rewind();
        $this->fileObject->current();
        $t = $this->fileObject->ftell();
        $this->fileObject->truncateWithCheck($t);
        $this->fileObject->unlock();
    }

    public function getIterator()
    {
        $this->fileObject->lock(LOCK_SH);
        $this->fileObject->rewind();
        $this->fileObject->current();
        $this->fileObject->next();
        while ($this->fileObject->valid()) {
            $row = $this->fileObject->current();
            if ($row == [null]) {
                break;
            }
            $this->fileObject->next();
            yield $row;
        }
        $this->fileObject->unlock();
    }

}
