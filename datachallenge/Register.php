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

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../App/Session/Default.php';
require_once __DIR__ . '/../App/Session/Register.php';

$tooltip = null;
$success = false;
if (isset($_POST['email']) && isset($_POST['password']) && isset($_POST['name'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    if (\App\Session\verifyEmail($email)) {
        $success = \App\Session\register($name, $password, $email);
        if (!$success) {
            $tooltip = "There was an error creating your account";
        } else {
            if (isset($_SESSION['login_attempts'])) {
                unset($_SESSION['login_attempts']);
            }
        }
    } else {
        $tooltip = $email;
    }
}

$session = new \App\Session\ChallengeSession();

if ($session->isLoggedIn()) {
    header("Location:home.php");
    if (isset($_SESSION['login_attempts'])) {
        unset($_SESSION['login_attempts']);
    }
}

?>

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title>Data Challenge</title>
    <!-- Meta -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Nicholai Mitchko">
    <link rel="shortcut icon" href="favicon.ico">
    <link
            href='https://fonts.googleapis.com/css?family=Roboto:400,500,400italic,300italic,300,500italic,700,700italic,900,900italic'
            rel='stylesheet' type='text/css'>
    <!-- Global CSS -->
    <!-- Plugins CSS -->
    <link rel="stylesheet" href="css/font-awesome/font-awesome.css"><!--
    <link rel="stylesheet" href="css/material/material.min.css">-->
    <link rel="stylesheet" href="https://code.getmdl.io/1.2.1/material.blue-deep_purple.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <!-- Theme CSS -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <link rel="stylesheet" href="css/index.css">
</head>

<body>
<div class="wrapper">
    <div class="mdl-card mdl-card mdl-shadow--2dp">
        <div class="mdl-card__title">
            <h2 class="mdl-card__title-text">Registration</h2>
        </div>
        <?php
        if (!$success) {
            if (isset($tooltip)) {
                echo '<div class="mdl-card__supporting-text" style="color: #d50000;">' . $tooltip . '</div>';
            } else {
                echo '<div class="mdl-card__supporting-text">Data Challenge</div>';
            }
            ?>
            <form id="registrationForm" action="" method="post">
                <div class="mdl-card__media">
                    <div class="link">
                        <div class="mdl-textfield mdl-js-textfield">
                            <input class="mdl-textfield__input" name="email" type="text" id="email" required
                                   aria-required=”true” pattern=".+@bc\.edu">
                            <label class="mdl-textfield__label" for="email">Boston College Email</label>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield">
                            <input class="mdl-textfield__input" name="password" type="password" id="pass" required
                                   aria-required=”true”>
                            <label class="mdl-textfield__label" for="password">Password</label>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield">
                            <input class="mdl-textfield__input" name="name" type="text" id="name" required
                                   aria-required=”true”>
                            <label class="mdl-textfield__label" for="name">Name</label>
                        </div>
                    </div>
                </div>
                <div class="mdl-card__actions mdl-card--border">
                    <input class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" type="submit"
                           value="Submit">
                </div>
            </form>
            <?php
        } else {
            echo '<div class="mdl-card__supporting-text">Success! Please Access <a href="index.php">Here</a></div>';
        }
        ?>
    </div>
</div>
<script src="js/material/material.min.js"></script>
<script
        src="https://code.jquery.com/jquery-3.1.1.min.js"
        integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
        crossorigin="anonymous"></script>
<!-- custom js -->
<script type="text/javascript" src="js/main.js"></script>
</body>
</html>
