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
namespace App\R\User;

use App\Database\Credentials;
use App\Session;
use \PDO;

require_once __DIR__ . '/../../Database/Credentials.php';
require_once __DIR__ . '/../../Session/Default.php';
require_once __DIR__ . '/Evaluate.php';


class UserUpload {
    const MAX_FILE_SIZE = 10000000; // 100kb

    private $dbConnection;
    private $session;

    function __construct(Session\ChallengeSession $currentSession) {
        $this->makeDBConnection();
        $this->session = $currentSession;
    }

    public function upload() {
        $this->checkUpload();
        $id = $this->getIdFromUpload();
        $fileName = $this->getUploadFilename();
        return $this->evaluate($id, $fileName);
    }

    private function makeDBConnection() {
        try {
            $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
            $this->dbConnection = $connection;
        } catch (\PDOException $e) {
        }
    }

    private function getIdFromUpload() {
        if (isset($_POST['id'])) {
            return filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
        } else {
            throw new \RuntimeException('Could Not Find Challenge');
        }
    }

    private function checkUpload() {
        if (!isset($_FILES['file']['error']) || is_array($_FILES['file']['error'])) {
            throw new \RuntimeException('Invalid File Upload');
        }
        switch ($_FILES['file']['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('No file sent.');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('Exceeded file-size limit.');
            default:
                throw new \RuntimeException('Unknown errors.');
        }
        if ($_FILES['file']['size'] > UserUpload::MAX_FILE_SIZE) {
            throw new RuntimeException('Exceeded file-size limit.');
        }
    }

    private function getIdOfChallenge($name) {
        $query = $this->dbConnection->prepare('SELECT `id` FROM Challenge WHERE `name` = :name LIMIT 1');
        $query->execute(array(
            ':name' => $name
        ));
        return $query->fetch()['id'];
    }

    private function getUploadFilename() {
        $filePath = $_FILES['file']['tmp_name'];
        if (file_exists($filePath)) {
            return $filePath;
        } else {
            throw new \RuntimeException('Error processing Upload');
        }
    }

    private function evaluate($id, $fileName) {
        $evaluation = new Evaluator($this->dbConnection, $this->session, $id);
        $evaluation->evaluate($fileName);
        return $evaluation->getResults();
    }

    function __destruct() {
        $this->dbConnection = null;
    }
}