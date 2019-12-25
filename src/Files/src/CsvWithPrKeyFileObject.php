<?php

namespace rollun\files;

use rollun\files\CsvFileObject;
use rollun\files\CsvSearch\SearchResult;

class CsvWithPrKeyFileObject extends CsvFileObject
{

    public function __construct(string $filename)
    {
        parent::__construct($filename);
    }

    public function getIdentifier()
    {
        $columns = $this->getColumns();
        return $columns[0];
    }

    public function getId($row)
    {
        return $row === false ? false : $row[0];
    }

    public function hasRowWithId($id)
    {
        $searchResult = $this->searchRowById($id);
        return $searchResult->isRowFound();
    }

    public function getRowById($id, $getNextIfAbsent = false)
    {
        $searchResult = $this->searchRowById($id);

        if ($searchResult->isRowFound()) {
            return $searchResult->foundRow;
        }
        if ($getNextIfAbsent) {
            return $searchResult->nextRow;
        }
        return null;
    }

    public function searchRowById($id, $from = null, $to = null, $lastPos = null)
    {
        $nearestBeggerId = null;
        $from = $from ?? 0;
        $to = $to ?? $this->getNumberOfLines();
        $middlePos = floor(($to - $from) / 2 + $from);
        $searchResult = new SearchResult($id);
        $prevFileObject = $this->fileObject;

        foreach ($this as $rowNumber => $row) {
            $currentId = $this->getId($row);

            if ($currentId === $id) {
                $searchResult->fill($prevFileObject);
                return $searchResult;
            }

            if ($currentId > $id && (!isset($nearestBeggerId) || $nearestBeggerId > $currentId)) {
                $searchResult->fillNext($this->fileObject);
                $nearestBeggerId = $currentId;
            }

            switch (true) {
                case is_numeric($currentId) && intval($currentId) > $id:
                case is_numeric($currentId) && is_numeric($id) && $currentId == $id:
                case is_numeric($currentId) && intval($currentId) === $id:
                    $searchResult->fill($this->fileObject);
                    return $searchResult;
                case $middlePos === $lastPos && $id > $currentId :
                    $this->fileObject->next();
                case $lastPos === $middlePos && $id < $currentId :
                    $searchResult->fillNext($this->fileObject);
                    return $searchResult;
                case ($row !== false) && ($currentId < $id):
                    $from = $middlePos;
                    break;
                default:
                    $to = $middlePos;
            }

            if ($rowNumber === $to) {
                return $searchResult;
            }
        }
        // $lastPos = $middlePos;
        // var_dump(compact('id', 'searchResult', 'currentId'));
        return $searchResult;
    }

    /**
     * Use it only if id is sorted and data don't contents EOL
     *
     * @param type $id serched ID
     * @param type $from don't use it
     * @param type $to don't use it
     * @param type $lastPos don't use it
     * @return SearchResult rollun\files\CsvSearch\SearchResult
     */
    protected function dichotomicSearch($id, $from = null, $to = null, $lastPos = null)
    {
        $searchResult = new SearchResult($id);
        $from = $from ?? 0;
        $to = $to ?? $this->fileObject->getFileSize();
        $middlePos = floor(($to - $from) / 2 + $from);
        $this->fileObject->fseekWithCheck($middlePos);
        $this->fileObject->current();
        $this->fileObject->next();
        $this->fileObject->ftell();
        $this->fileObject->key();
        $row = $this->fileObject->current();

        $row = $row === [null] ? false : $row;
        $currentId = $row === false ? false : $this->getId($row);
        switch (true) {

            case $middlePos === $lastPos && $id > $currentId :
                $this->fileObject->next();
            case $lastPos === $middlePos && $id < $currentId :
                $searchResult->fillNext($this->fileObject);
                return $searchResult;
            case $currentId == $id:
                $searchResult->fill($this->fileObject);
                return $searchResult;
            case ($row !== false) && ($currentId < $id):
                $from = $middlePos;
                break;
            default:
                $to = $middlePos;
        }

        $lastPos = $middlePos;
        return $this->searchRowById($id, $from, $to, $lastPos);
    }

}
