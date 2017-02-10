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

namespace App\R;

use App\Database\Credentials;
use \PDO;

require_once __DIR__ . '/../Database/Credentials.php';

class Results {

    private $dbConnection;
    private $user = 0;

    function __construct($user_id) {
        $this->makeDBConnection();
        $this->user = $user_id;
    }

    private function makeDBConnection() {
        try {
            $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
            $this->dbConnection = $connection;
        } catch (\PDOException $e) {
        }
    }

    public function getLastSubmissions($amt = 7) {
        $query = $this->dbConnection->prepare("SELECT * FROM Submission WHERE user_id = :user ORDER BY submitted_at LIMIT $amt");
        $query->execute(array(':user' => $this->user));
        return $query->fetchAll();
    }

    public function getLastSubmissionsGlobal($amt = 7) {
        $query = $this->dbConnection->prepare("SELECT * FROM Submission WHERE user_id <> :user ORDER BY submitted_at LIMIT $amt");
        $query->execute(array(':user' => $this->user));
        $query->execute();
        return $query->fetchAll();
    }

    public function getBestSubmission() {
        $query = $this->dbConnection->prepare("SELECT * FROM Submission WHERE user_id = :user ORDER BY score DESC LIMIT 1");
        $query->execute(array(':user' => $this->user));
        return $query->fetch();
    }

    public function getAnswerCSV() {
        $id = $this->getIdFromForm();
        $query = $this->dbConnection->prepare("SELECT (SELECT email FROM User WHERE id = sub.user_id) 'email',(8 + (0.3 * sub.score + 0.7 * sub.accuracy) * 8.5) 'Grade', sub.score 'AUROC', sub.accuracy 'Accuracy' FROM Submission sub WHERE sub.score IN (SELECT MAX(score) FROM Submission ext WHERE ext.user_id = sub.user_id AND ext.challenge_id = :id) AND sub.challenge_id = :id");
        $query->execute(array(':id' => $id));
        $results = $query->fetchAll(PDO::FETCH_ASSOC);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="results.csv"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        $f = fopen('php://output', 'w');
        fputcsv($f, array_keys($results[0]), ',');
        foreach ($results as $line) {
            fputcsv($f, $line, ',');
        }
    }

    private function getIdFromForm() {
        if (isset($_POST['id'])) {
            return filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
        } else {
            throw new \RuntimeException('Could Not Find Challenge');
        }
    }

    public function getActiveChallenges() {
        $query = $this->dbConnection->prepare("SELECT * FROM Challenge WHERE enabled = 1");
        $query->execute();
        return $query->fetchAll();
    }

    function __destruct() {
        $this->dbConnection = null;
    }
}