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
require_once __DIR__ . '/Utilities.php';


class Login {
    /**
     * Generic Access Function; Simply pass the email and password and this does the rest
     * @param $email Email the User is trying to login with
     * @param $password Password the User is trying to login with
     * @return int -1 if failure, user_id if success (>= 0)
     */
    public static function login($email, $password) {
        try {
            $connection = new PDO("mysql:host=" . Credentials::$mysql_serverAddress . ";dbname=" . Credentials::$mysql_databaseName, Credentials::$mysql_username, Credentials::$mysql_password);// Start the connection to the database
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);   // Set the PDO (database driver) mode to exceptions so we can catch errors
            $matching_user = Utilities::getUser($connection, $email);                      // Find a matching email to check pass against
            if (sizeof($matching_user) > 0) {      // If the user search returns something it isn't false
                if (Utilities::compareHashedPasswords($matching_user[0]['hashed_password'], $password)) {   // If the password hashes match continue
                    Utilities::updateLastLogin($connection, $matching_user[0]['id']);
                    return $matching_user[0];
                }
            }
            $connection = null;     // Finally we kill the connection
        } catch (PDOException $e) {
        }
        return null;
    }
}