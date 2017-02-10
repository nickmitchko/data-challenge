<?php
/**
 * Copyright (c) 2017 Nicholai G. Mitchko
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace App\R\Admin;

class Generator {


    const PHP_TEMPLATE = '<?php
/**
 * Copyright (c) 2017. Boston College - All Rights Reserved.
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * This file is proprietary and confidential.
 *
 * Author:    This is a software generated file
 * Project:   Data Challenge
 */
 
$ANSWER_ARRAY = array(%s);';

    const PHP_ARRAY_TEMPLATE = '%F';
    const GENERATE_FILE_DIR = '/../Generated/';

    private $filename;
    private $dbConnection;

    function __construct($filename, $dbConn) {
        $this->filename = $filename;
        $this->verifyDBConnection($dbConn);
        $this->dbConnection = $dbConn;
    }

    private function verifyDBConnection(\PDO $dbconn) {
        if (is_null($dbconn) || !isset($dbconn)) {
            throw new \RuntimeException("There was an error with the database connection");
        }
    }

    private function readCSV($csvFile) {
        return array_map('str_getcsv', file($csvFile));
    }

    public function generateEvaluationFile() {
        $csv = $this->readCSV($this->filename);
        $len = count($csv);
        $array_str = '';
        for ($i = 0; $i < $len - 1; ++$i) {
            $array_str .= sprintf(Generator::PHP_ARRAY_TEMPLATE, $csv[$i][0]) . ',';
        }
        $array_str .= sprintf(Generator::PHP_ARRAY_TEMPLATE, $csv[$len - 1][0]);
        $phpString = sprintf(Generator::PHP_TEMPLATE, $array_str);
        $filename = __DIR__ . Generator::GENERATE_FILE_DIR . sha1($phpString);
        $this->saveFile($filename, $phpString);
        return $filename;
    }

    private function saveFile($filename, $phpString) {
        try {
            $success = file_put_contents($filename, $phpString);
            if (!$success) {
                throw new \RuntimeException('Could not save answer file');
            }
        } catch (\Exception $e) {
            throw new \RuntimeException('Could not save answer file');
        }
    }

}