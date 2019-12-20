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
            [2, "C\nD"],
            [3, "E\r\nF"],
            [4, "G\r\n\"123\"H"],
            [5, "I\r\n\"123\"J\r\nK\""],
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
            [2, "C\nD"],
            [3, "E\r\nF"],
            [4, "G\r\n\"123\"H"],
            [5, "I\r\n\"123\"J\r\nK\""],
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
            [2, "C\nD"],
            [3, "E\r\nF"],
            [4, "G\r\n\"123\"H"],
            [5, "I\r\n\"123\"J\r\nK\""],
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
     * @depends testAddRow
     */
    public function testNumberOfRowsOfFile(CsvFileObject $csvFileObject)
    {
        $this->assertEquals(6, $csvFileObject->getNumberOfRows());
    }

    public function testNumberOfRowsOfEmptyFile()
    {
        $fullFilename = $this->makeFullFileName();
        @unlink($fullFilename);
        CsvFileObject::createNewCsvFile($fullFilename, []);
        $csvFileObject = new CsvFileObject($fullFilename);
        $this->assertEquals(0, $csvFileObject->getNumberOfRows());
    }

    public function testWithWrongCustomConfigs()
    {
        $fullFilename = $this->makeFullFileName();
        @unlink($fullFilename);
        try {
            CsvFileObject::createNewCsvFile($fullFilename, ['id', 'val'], '|', "'", '/');
        } catch (\Exception $exception) {
            $this->assertEquals("In writing mode, the escape char must be a backslash '\\'. The given escape char '/' will be ignored.", $exception->getMessage());
            // $this->expectException()
        }
    }

    public function testWithCustomConfigs()
    {
        $fullFilename = $this->makeFullFileName();
        @unlink($fullFilename);
        CsvFileObject::createNewCsvFile($fullFilename, ['id', 'val'], '|', "'");
        $csvFileObject = new CsvFileObject($fullFilename);
        $this->assertEquals(0, $csvFileObject->getNumberOfRows());
        $expectedRows = array(
            [0, "E\r\nF"],
            [1, "G\r\n\"123\"H"],
            [2, "I\r\n\"123\"J\r\nK\""],
        );
        foreach ($expectedRows as $row) {
            $csvFileObject->addRow($row);
            $actual = $csvFileObject->getRow($row[0]);
            $this->assertEquals($expectedRows[$row[0]], $actual);
        }
        $this->assertEquals(3, $csvFileObject->getNumberOfRows());
    }

    public function testReadRowsFromFileGeneratedByLibreOffice()
    {
        $expected = array(
            [0, "123\nSd \"123\" s, df\n4234234"],
            [1, "A"],
            [2, "B, C, D, “E”"],
            [3, "\"123\", 1, 321"],
        );
        $fullFilename = $this->makeFullFileName('CsvFileGeneratedByLibreOffice.csv');
        $csvFileObject = new CsvFileObject($fullFilename);
        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        $this->assertEquals(4, $csvFileObject->getNumberOfRows());
    }

    public function testReadRowsFromFileGeneratedByGoogleSpreadsheet()
    {
        $expected = array(
            [0, "A"],
            [1, "B\nC\n\"test message\"\n"],
            [2, "\"test\""],
            [3, "1, \"quotes\", 3"],
        );
        $fullFilename = $this->makeFullFileName('CsvFileGeneratedByGoogleSpreadsheet.csv');
        $csvFileObject = new CsvFileObject($fullFilename);
        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        $this->assertEquals(4, $csvFileObject->getNumberOfRows());
    }

    public function testReadRowsFromFileGeneratedByMsExcelMacintoshWithEncodingUtf8()
    {
        $expected = array(
            [0, "A"],
            [1, "123\nSd \"123\" s, df\n4234234"],
            [2, "B, \"C\", D"],
            [3, "\"E\""],
            [4, "\"F\", \"G\", \"I"],
            [5, "J"],
        );
        $fullFilename = $this->makeFullFileName('CsvFileGeneratedByMSExcelMacintoshUtf8.csv');
        $csvFileObject = new CsvFileObject($fullFilename, ';');

        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        $this->assertEquals(6, $csvFileObject->getNumberOfRows());
    }

    public function testReadRowsFromFileGeneratedByMsExcelMacintoshThrowEncodingException()
    {
        $expected = array(
            [0, "A"],
            [1, "123\nSd \"123\" s, df\n4234234"],
            [2, "B, \"C\", D"],
            [3, "\"E\""],
            [4, "\"F\", \"G\", \"I"],
            [5, "J"],
        );
        $fullFilename = $this->makeFullFileName('CsvFileGeneratedByMSExcelMacintosh.csv');
        $csvFileObject = new CsvFileObject($fullFilename, ';');

        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        $this->assertEquals(6, $csvFileObject->getNumberOfRows());
    }

    public function testReadRowsFromFileGeneratedByMsExcelMsDosThrowEncodingException()
    {
        $expected = array(
            [0, "A"],
            [1, "123\nSd \"123\" s, df\n4234234"],
            [2, "B, \"C\", D"],
            [3, "\"E\""],
            [4, "\"F\", \"G\", \"I"],
            [5, "J"],
        );
        $fullFilename = $this->makeFullFileName('CsvFileGeneratedByMsExcelMsDos.csv');
        $csvFileObject = new CsvFileObject($fullFilename, ';');

        foreach ($csvFileObject as $value) {
            $actual[] = $value;
        }
        $this->assertEquals($expected, $actual);
        $this->assertEquals(6, $csvFileObject->getNumberOfRows());
    }

    /**
     *
     * @param CsvFileObject $csvFileObject
     * @depends testAddRow
     */
    public function testNumberOfLinesOfFile(CsvFileObject $csvFileObject)
    {
        $this->assertEquals(7, $csvFileObject->getNumberOfLines());
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
