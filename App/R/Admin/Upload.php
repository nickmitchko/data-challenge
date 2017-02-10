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

use App\Database\Credentials;
use App\Session;
use \PDO;

require_once __DIR__ . '/../../Database/Credentials.php';
require_once __DIR__ . '/../../Session/Default.php';
require_once __DIR__ . '/../../R/Admin/Generate.php';


class Upload {

    const UPLOAD_DIR = "/../Uploads/Admin/";
    const MAX_FILE_SIZE = 10000000; // 100kb

    private $dbConnection;
    private $session;

    function __construct(Session\ChallengeSession $currentSession) {
        $this->makeDBConnection();
        $this->session = $currentSession;
    }

    // TODO: Get Time from Admin to Upload Challenge
    public function upload() {
        $this->checkUpload();
        $name = $this->getNameFromUpload();
        $about = $this->getDescriptionFromUpload();
        $filename = $this->moveUpload();
        $phpGenerator = new Generator($filename, $this->dbConnection);
        $fn = $phpGenerator->generateEvaluationFile();
        $this->addToDatabase($fn, $name, $about);
    }

    private function makeDBConnection() {
        try {
            $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
            $this->dbConnection = $connection;
        } catch (\PDOException $e) {
        }
    }

    private function getNameFromUpload() {
        if (isset($_POST['name'])) {
            return filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        } else {
            throw new \RuntimeException('Error With Challenge Name');
        }
    }

    private function getDescriptionFromUpload() {
        if (isset($_POST['about'])) {
            return filter_input(INPUT_POST, 'about', FILTER_SANITIZE_STRING);
        } else {
            return '';
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
        if ($_FILES['file']['size'] > Upload::MAX_FILE_SIZE) {
            throw new RuntimeException('Exceeded file-size limit.');
        }
    }

    private function moveUpload() {
        /*if (false === $extension = array_search(
                $fileInfo->file($_FILES['file']['tmp_name']),
                array(
                    'csv' => 'text/csv'
                )
                , true)
        ) {
            echo $extension;
            throw new \RuntimeException('Invalid File Format.');
        }*/
        $fileHash = sha1_file($_FILES['file']['tmp_name']);
        if (!move_uploaded_file(
            $_FILES['file']['tmp_name'],
            sprintf(__DIR__ . Upload::UPLOAD_DIR . '%s', $fileHash)
        )
        ) {
            throw new \LogicException('Could not upload file');
        }
        return sprintf(__DIR__ . Upload::UPLOAD_DIR . '%s', $fileHash);
    }

    private function addToDatabase($filename, $name, $about = '') {
        $query = $this->dbConnection->prepare("INSERT INTO Challenge (creator_id, name, answer_file, created_at, enabled, about) VALUES (:user, :name, :file, NOW(), TRUE, :about)");
        return $query->execute(array(
            ':user' => $this->session->getUserId(),
            ':file' => $filename,
            ':name' => $name,
            ':about' => $about
        ));
    }

    function __destruct() {
        $this->dbConnection = null;
    }


}