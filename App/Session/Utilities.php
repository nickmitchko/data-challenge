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

use \PDO;

if (!function_exists('hash_equals')) {
    function hash_equals($str1, $str2) {
        if (strlen($str1) != strlen($str2)) {
            return false;
        } else {
            $res = $str1 ^ $str2;
            $ret = 0;
            for ($i = strlen($res) - 1; $i >= 0; $i--) {
                $ret |= ord($res[$i]);
            }
            return !$ret;
        }
    }
}

class Utilities {
    /**
     * @param PDO $connection App connection to get a User
     * @param $email Email to search for in the database
     * @return mixed Array result of the matching user
     */
    public static function getUser(PDO $connection, $email) {
        $userQuery = $connection->prepare("SELECT * FROM User WHERE email = :email LIMIT 1");
        $userQuery->execute(array(':email' => $email));
        return $userQuery->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param PDO $connection App connection used to create User
     * @param $name     Name of the User we are going to create
     * @param $password The password of the new User
     * @param $email    The email of the new User
     * @return bool Whether or not the user could be successfully created
     */
    public static function createUser(PDO $connection, $name, $password, $email) {
        $hashedPassword = Utilities::hashPassword($password);
        $createQuery = $connection->prepare("INSERT INTO User (name, hashed_password, email, created_at) VALUES (:name, :hash, :email, NOW())");
        return $createQuery->execute(array(':name' => $name, ':hash' => $hashedPassword, ':email' => $email));
    }

    /**
     * @param PDO $connection App connection used to update user Access
     * @param $user_id User id to update (SQL: User.id)
     * @return bool Whether or not the user could successfully updated
     */
    public static function updateLastLogin(PDO $connection, $user_id) {
        $updateQuery = $connection->prepare("UPDATE User SET last_login = NOW() WHERE id= :user_id");
        return $updateQuery->execute(array(':user_id' => $user_id));
    }

    /**
     * Hashes the supplied password with the php standard hashing conventions.
     * Uses the Blow-fish Algorithm
     * @param $password The String to generate a password hash from
     * @param int $cost The cost associated with generating this hash (Default:12, higher the better)
     * @return string Returns the hashed password version
     */
    public static function hashPassword($password, $cost = 12) {
        $salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
        $salt = sprintf("$2a$%02d$", $cost) . $salt;
        return crypt($password, $salt);
    }

    /**
     * @param $hash Hashed password to compare with entered password
     * @param $password raw string password to compare with the hash
     * @return bool whether or not the passwords are the same
     */
    public static function compareHashedPasswords($hash, $password) {
        return hash_equals($hash, crypt($password, $hash));
    }
}