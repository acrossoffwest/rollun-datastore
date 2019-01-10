<?php

namespace rollun\files\CsvSearch;

use rollun\files\FileObject;

class SearchResult
{

    public $requestedId;
    public $foundRow;
    public $nextRow;
    public $charPos;
    public $linePos;
    public $nextCharPos;
    public $nextLinePos;

    public function __construct($requestedId)
    {
        $this->requestedId = $requestedId;
    }

    public function isRowFound()
    {
        $this->checkExecution();
        return isset($this->foundRow);
    }

    public function isRequestedIdBiggest()
    {
        $this->checkExecution();
        if (!$this->isRowFound()) {
            throw new \RuntimeException(
            "Row with \$id= $this->requestedId - is not found."
            );
        }
        return ($this->nextRow === false);
    }

    public function isRequestedIdBiggierThanBiggest()
    {
        $this->checkExecution();
        if ($this->isRowFound()) {
            throw new \RuntimeException(
            "Row with \$id= $this->requestedId - is found."
            );
        }
        return ($this->foundRow === false);
    }

    public function fillNext(FileObject $fileObject)
    {
        $this->nextCharPos = $fileObject->ftell();
        $this->nextLinePos = $fileObject->key();
        $this->nextRow = $fileObject->current();
        return $this;
    }

    public function fill(FileObject $fileObject)
    {
        $this->charPos = $fileObject->ftell();
        $this->linePos = $fileObject->key();
        $this->foundRow = $fileObject->current();
        if ($this->foundRow !== false) {
            $fileObject->next();
            $this->fillNext($fileObject);
        }
        return $this;
    }

    protected function checkExecution()
    {
        if (!(isset($this->foundRow) || isset($this->nextRow))) {
            throw new \RuntimeException(
            "SearchResult is not received for \$id= $this->requestedId"
            );
        }
    }

}
