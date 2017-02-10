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
use App\Session;
use PDO;

require_once __DIR__ . '/../Database/Credentials.php';
require_once __DIR__ . '/Utilities.php';

/**
 * Generic Registration Function; Simply pass the name, email, and password, and
 * this does the rest
 * @param $name Name that we are registering
 * @param $password Password that we are registering
 * @param $email Email that we are registering
 * @return bool FALSE if failure, TRUE if success
 */
function register($name, $password, $email) {
    try {
        $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
        $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
        $matching_user = Session\Utilities::getUser($connection, $email);                      // Find a matching email to check pass against
        if (sizeof($matching_user) == 0) {     // If the user search returns no users
            return Session\Utilities::createUser($connection, $name, $password, $email);              // Since there are now users with this email, register this one
        } /*else if($matching_user != false && $matching_user->rowCount() >= 0) {
            return -1;
        }*/
        $connection = null;     // Finally we kill the connection
    } catch (PDOException $e) {
    }
    return false;
}

/**
 * Simple Function to Verify if an email is a boston college email
 * @param $email email to verify
 * @return bool return value if email is verified
 */
function verifyEmail($email) {
    return preg_match('/^.+@bc.edu$/', $email) == 1;
}