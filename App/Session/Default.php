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

namespace App\Session;

use App\Database\Credentials;
use \PDO;

require_once __DIR__ . '/../Database/Credentials.php';
require_once __DIR__ . '/Login.php';

class ChallengeSession {
    private $user_id;
    private $name;
    private $email;
    private $loggedIn = false;
    private $loginDisabled = false;
    private $loginAttempts = 0;
    private $last_login_attempt = null;
    private $Admin;

    function __construct() {
        session_start();
        $this->processSession();
    }

    private function processSession() {
        // Process Logged-in variable
        if (isset($_SESSION['user_id'])) {
            $this->loggedIn = true;
            $this->user_id = $_SESSION['user_id'];
            $this->email = $_SESSION['email'];
            $this->name = $_SESSION['name'];
        } else if (isset($_SESSION['login_attempts']) && isset($_SESSION['last_login_attempt'])) {
            $this->loginAttempts = $_SESSION['login_attempts'];
            $this->last_login_attempt = $_SESSION['last_login_attempt'];
            if ($this->loginAttempts >= 5) {
                $this->loginDisabled = true;
            }
        }
    }

    public function login() {
        if (isset($_SESSION['login_attempts'])) {
            $_SESSION['login_attempts']++;
        } else {
            $_SESSION['login_attempts'] = 1;
        }
        $_SESSION['last_login_attempt'] = time();
        if (!$this->loggedIn) {
            usleep(200000);
            if (isset($_POST['email']) && isset($_POST['password'])) {            // Check if the login data is actually sent
                $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
                $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);   // Get the Access Password in a safe way
                $user = Login::login($email, $password);
                if (isset($user)) {
                    if ($user['id'] >= 0) {
                        $this->processLogin($user);
                        return true;
                    }
                }
            }
        } else {
            return true;
        }
        return false;
    }

    public function logout() {
        session_destroy();
        return
            '<form id="myForm" action="index.php" method="get">
            <noscript><input type="submit" value="Click here if you are not redirected."/></noscript>
         </form>
         <script type="text/javascript">
            document.getElementById(\'myForm\').submit();
         </script>';
    }

    private function processLogin($user) {
        $this->loginAttempts = 0;
        $this->user_id = $user['id'];
        $this->email = $user['email'];
        $this->loggedIn = true;
        $this->last_login_attempt = time();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        unset($_SESSION['login_attempts']);
    }

    public function isAdmin() {
        if (isset($this->Admin)) {
            return $this->Admin;
        } else if (isset($this->user_id)) {
            try {
                $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
                $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
                $adminQuery = $connection->prepare("SELECT * FROM Administration WHERE user_id = :user LIMIT 1");
                $adminQuery->execute(array(':user' => $this->user_id));
                $result = false;
                if (isset($adminQuery->fetch()['id'])) {
                    $result = true;
                }
                $connection = null;     // Finally we kill the connection
                $this->Admin = $result;
                return $result;
            } catch (PDOException $e) {
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getUserId() {
        return $this->user_id;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email) {
        $this->email = $email;
    }

    /**
     * @return boolean
     */
    public function isLoggedIn() {
        return $this->loggedIn;
    }

    /**
     * @param boolean $loggedIn
     */
    public function setLoggedIn($loggedIn) {
        $this->loggedIn = $loggedIn;
    }

    /**
     * @return boolean
     */
    public function isLoginDisabled() {
        return $this->loginDisabled;
    }

    /**
     * @param boolean $loginDisabled
     */
    public function setLoginDisabled($loginDisabled) {
        $this->loginDisabled = $loginDisabled;
    }

    /**
     * @return int
     */
    public function getLoginAttempts() {
        return $this->loginAttempts;
    }

    /**
     * @param int $loginAttempts
     */
    public function setLoginAttempts($loginAttempts) {
        $this->loginAttempts = $loginAttempts;
    }

    /**
     * @return null
     */
    public function getLastLoginAttempt() {
        return $this->last_login_attempt;
    }

    /**
     * @param null $last_login_attempt
     */
    public function setLastLoginAttempt($last_login_attempt) {
        $this->last_login_attempt = $last_login_attempt;
    }


}