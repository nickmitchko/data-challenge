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

$session = new \App\Session\ChallengeSession();
if (!$session->isLoginDisabled()) {
    if ($session->login()) {
        success();
    }
}
fail();

function fail() {
    ?>
    <form id="myForm" action="index.php" method="post">
        <input type="hidden" name="loginFail" value="1">
        <noscript><input type="submit" value="Click here if you are not redirected."/></noscript>
    </form>
    <script type="text/javascript">
        document.getElementById('myForm').submit();
    </script>
    <?php
}

function success($user_id) {/*
    $_SESSION['user_id'] = $user_id;
    unset($_SESSION['login_attempts']);*/

    header("Location:home.php");
}