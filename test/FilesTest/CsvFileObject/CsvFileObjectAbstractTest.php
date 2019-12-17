<?php

namespace rollun\test\files\CsvFileObject;

use rollun\files\FileObject;
use rollun\files\CsvFileObject;
use rollun\files\FileManager;
use rollun\test\files\FilesAbstractTest;
use rollun\files\CsvWithPrKeyFileObject;

abstract class CsvFileObjectAbstractTest extends FilesAbstractTest
{

    protected function makeFile(string $stringInFile)
    {
        $fileManager = new FileManager;
        $fullFilename = $this->makeFullFileName();
        $stream = $fileManager->createAndOpenFile($fullFilename, true);
        $fileManager->closeStream($stream);
        file_put_contents($fullFilename, $stringInFile);
        return $fullFilename;
    }

    protected function getCsvFileObject(string $stringInFile, array $rows = null)
    {

        $fullFilename = $this->makeFile($stringInFile);
        $csvFileObject = new CsvFileObject($fullFilename);
        if (is_null($rows)) {
            return $csvFileObject;
        }
    }

    protected function getCsvWithPrKeyFileObject(string $stringInFile, array $rows = null)
    {
        $fullFilename = $this->makeFile($stringInFile);
        $csvWithPrKeyFileObject = new CsvWithPrKeyFileObject($fullFilename);
        if (!is_null($rows)) {
            foreach ($rows as $row) {
                $csvWithPrKeyFileObject->addRow($row);
            }
        }

        return $csvWithPrKeyFileObject;
    }

    protected function arrayProvider()
    {
        $rowsValsSet = array(
            ['1', '2222', '33333333', '4444444444444'],
            ['111111111111111', '2222', '3', ''],
            [-5, -4, -3, -2],
                //['', "\n", " \"\"\n\"\" ", "!@ \t#$% \r%^ *& (*))_ "]
        );
        $idsSet = array(
            ['id' => [1, 2, 3, 4], 'queryId' => [0, 1.5, 2.5, 3.5, 9]],
            ['id' => ['a', 'cc', 'eee', 'gggggg'], 'queryId' => ['_', 'b', 'ddddd', 'ff', 'h']],
            ['id' => [-8, -6, -4, -2], 'queryId' => [-9, -7, -5, -3, -1]],
        );

        foreach ($idsSet as $ids) {
            $queryIdAndExpectedId = [];
            //$queryIdAndExpectedId[$queryId, $expectedId];
            $queryIdAndExpectedId[] = [$ids['queryId'][0], $ids['id'][0]];
            $queryIdAndExpectedId[] = [$ids['id'][0], $ids['id'][0]];
            $queryIdAndExpectedId[] = [$ids['queryId'][1], $ids['id'][1]];
            $queryIdAndExpectedId[] = [$ids['id'][1], $ids['id'][1]];
            $queryIdAndExpectedId[] = [$ids['queryId'][2], $ids['id'][2]];
            $queryIdAndExpectedId[] = [$ids['id'][2], $ids['id'][2]];
            $queryIdAndExpectedId[] = [$ids['queryId'][3], $ids['id'][3]];
            $queryIdAndExpectedId[] = [$ids['id'][3], $ids['id'][3]];
            // TODO: If search result empty will throw RuntimeException
            // $queryIdAndExpectedId[] = [$ids['queryId'][4], null];
            foreach ($rowsValsSet as $rowsVals) {
                $testedRows = [];
                foreach ($rowsVals as $key => $rowVal) {
                    $testedRows[] = [$ids['id'][$key], $rowVal];
                }
                $testedRowsSet[] = [$testedRows, $queryIdAndExpectedId];
            }
        }
        return $testedRowsSet;
    }

}
