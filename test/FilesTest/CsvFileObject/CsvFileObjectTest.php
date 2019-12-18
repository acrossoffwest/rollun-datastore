<?php

namespace rollun\test\files\CsvFileObject;

use rollun\test\files\CsvFileObject\CsvFileObjectAbstractTest;
use rollun\files\CsvFileObject;

class CsvFileObjectTest extends CsvFileObjectAbstractTest
{

    public function getColumnsProvider()
    {
        //$columsStrings
        return array(
            ["val\n"],
            ["val"],
            ["id,val\n"],
            ["id,val"],
            ["val\nA\n", ['A']],
            ["val\nA", ['A']],
            ["id,val\n1,A", ['1', 'A']],
            ["id,val\n0123,AB CD", ['0123', 'AB CD']],
        );
    }

    /**
     * @dataProvider getColumnsProvider
     */
    public function testGetColumns($columsStrings)
    {
        $csvFileObject = $this->getCsvFileObject($columsStrings);
        $expected = explode("\n", $columsStrings)[0];
        $actual = implode(',', $csvFileObject->getColumns());
        $this->assertEquals($expected, $actual);
    }

    public function getRowProvider()
    {
        //$columsStrings
        return array(
            ["val\nA\n", ['A']],
            ["val\nA", ['A']],
            ["id,val\n1,A", ['1', 'A']],
            ["id,val\n0123,AB CD", ['0123', 'AB CD']],
        );
    }

    /**
     * @dataProvider getRowProvider
     */
    public function testGetRow($stringInFile, $arrayExpected)
    {
        $csvFileObject = $this->getCsvFileObject($stringInFile);
        $arrayActual = $csvFileObject->getRow(0);
        $this->assertEquals($arrayExpected, $arrayActual);
    }

    public function createNewCsvFileProvider()
    {
        //$columsArray
        return array(
            [["val"]],
            [["id", "val"]],
        );
    }

    /**
     * @dataProvider createNewCsvFileProvider
     */
    public function testCreateNewCsvFile($columsArray)
    {

        $fullFilename = $this->makeFullFileName();
        @unlink($fullFilename);
        CsvFileObject::createNewCsvFile($fullFilename, $columsArray);
        $arrayExpected = $columsArray;
        $csvFileObject = new CsvFileObject($fullFilename);
        $arrayActual = $csvFileObject->getColumns();
        $this->assertEquals($arrayExpected, $arrayActual);
    }

    public function testAddRow()
    {
        $fullFilename = $this->makeFullFileName();
        @unlink($fullFilename);
        CsvFileObject::createNewCsvFile($fullFilename, ["id", "val"]);
        $csvFileObject = new CsvFileObject($fullFilename);
        $expectedRows = array(
            [0, "A"],
            [1, "B"],
        );
        foreach ($expectedRows as $row) {
            $csvFileObject->addRow($row);
            $actual = $csvFileObject->getRow($row[0]);
            $this->assertEquals($expectedRows[$row[0]], $actual);
        }
        return $csvFileObject;
    }

    /**
     *
     * @param CsvFileObject $csvFileObject
     * @depends testAddRow
     */
    public function testIterator(CsvFileObject $csvFileObject)
    {
        $expected = array(
            [0, "A"],
            [1, "B"],
        );
        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        return $csvFileObject;
    }

    /**
     *
     * @param CsvFileObject $csvFileObject
     * @depends testAddRow
     */
    public function testIteratorAndPosReferenceInFile(CsvFileObject $csvFileObject)
    {
        $expected = array(
            [0, "A"],
            [1, "B"],
        );
        foreach ($csvFileObject as $value) {
            $actual[] = $csvFileObject->getFileObject()->current();
        }
        $this->assertEquals($expected, $actual);
        return $csvFileObject;
    }

    /**
     *
     * @param CsvFileObject $csvFileObject
     * @depends testIterator
     */
    public function testDeleteAllRows(CsvFileObject $csvFileObject)
    {

        $actual = $csvFileObject->getRow(0)[1];
        $this->assertEquals('A', $actual);
        $csvFileObject->deleteAllRows();
        $this->expectException(\InvalidArgumentException::class);
        $csvFileObject->getRow(0);
    }

}
