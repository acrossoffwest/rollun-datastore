<?php

namespace rollun\test\files\CsvFileObject;

use rollun\test\files\CsvFileObject\CsvFileObjectAbstractTest;
use rollun\files\CsvWithPrKeyFileObject;

class CsvWithPrKeyFileObjectTest extends CsvFileObjectAbstractTest
{
    public function getRowsProvider()
    {
        return [
            [
                [
                    [0, "A"],
                    [1, "B"],
                    [2, "C\nD"],
                    [3, "E\nF"],
                    [4, "G\n\"123\"H"],
                    [5, "I\n\"123\"J\nK\""],
                ]
            ]
        ];
    }
    public function getRowProvider()
    {
        $sets = $this->arrayProvider();
        return $sets;
    }

    /**
     * @group testNow
     * @dataProvider getRowProvider
     */
    public function testGetRow($testedRows, $queryIdAndExpectedId)
    {
        $csvWithPrKeyFileObject = $this->getCsvWithPrKeyFileObject("id,val\n", $testedRows);

        foreach ($queryIdAndExpectedId as $value) {
            $queryId = $value[0];
            $expectedId = $value[1];
            $row = $csvWithPrKeyFileObject->getRowById($queryId, true);
            $actualId = $csvWithPrKeyFileObject->getId($row);
            $this->assertEquals($expectedId, $actualId);
        }
    }

    //              $this->expectException(\InvalidArgumentException::class);
//        $rows = array(
//            "id,val",
//            "0, A",
//            "1, B",
//            "2, C",
//            "3, D",
//            "4, E",
//        );
//        //$columsStrings
//        return $rows;
//    }
//
//    /**
//     * @dataProvider getRowByIdProvider
//     */
//    public function testGetRowById()
//    {
    // var_dump($this->arrayProvider());
//        $stringInFile = implode("\n", $rows);
//        $csvWithPrKeyFileObject = $this->getCsvWithPrKeyFileObject($stringInFile);
//        foreach ($rows as $key => $value) {
//            $result = $csvWithPrKeyFileObject->getRowById($key);
//            $actualRow
//        }
//
//
//        $expected = explode(',', $rows[$id + 1]);
//        $this->assertEquals($expected, $actual);
//    }
//
//    public function getRowByIdAbsentIdProvider()
//    {
//        //$columsStrings
//        return array(
//            [-1, 0],
//            [0.5, 1],
//            [1.5, 2],
//            [2.5, 3],
//            [3.5, 4],
//            [4.5, null],
//            [6, null],
//        );
//    }
//
//    /**
//     * @dataProvider getRowByIdAbsentIdProvider
//     */
//    public function testGetRowByIdAbsentId($id, $expected)
//    {
//        $rows = array(
//            "id,val",
//            "0, A",
//            "1, B",
//            "2, C",
//            "3, D",
//            "4, E",
//        );
//        $stringInFile = implode("\n", $rows);
//        $csvWithPrKeyFileObject = $this->getCsvWithPrKeyFileObject($stringInFile);
//        $actual = $csvWithPrKeyFileObject->getRowById($id);
//        $this->assertEquals($expected, $actual);
//    }
}
