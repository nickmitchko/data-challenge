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

class Delete {

    private $session;
    private $dbConnection;

    function __construct(Session\ChallengeSession $session) {
        $this->setSession($session);
        $this->makeDBConnection();
    }

    private function makeDBConnection() {
        try {
            $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
            $this->dbConnection = $connection;
        } catch (\PDOException $e) {
            throw new \RuntimeException('Issue Connecting to Database');
        }
    }

    private function setSession(Session\ChallengeSession $session) {
        if ($session->isAdmin() && $session->isLoggedIn()) {
            $this->session = $session;
        } else {
            throw new \RuntimeException('Inappropriate Privileges');
        }
    }

    public function delete() {
        $id = $this->getIdOfDelete();
        $status = $this->removeFromDB($id);
        if ($status == false) {
            throw new \RuntimeException('Error removing entry from database');
        }
        return $id;
    }

    private function getIdOfDelete() {
        if (isset($_POST['id'])) {
            return filter_input(INPUT_POST, 'id', FILTER_SANITIZE_STRING);
        } else {
            throw new \RuntimeException('No ID sent with transaction');
        }
    }

    private function removeFromDB($id) {
        $query = $this->dbConnection->prepare("DELETE FROM Challenge WHERE id=:id");
        return $query->execute(array(
            ':id' => $id
        ));
    }
}