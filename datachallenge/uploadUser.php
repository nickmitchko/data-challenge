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
/*
error_reporting(E_ALL);
ini_set('display_errors', 1);*/
ini_set('memory_limit', '128M');


require_once __DIR__ . '/../App/R/User/Upload.php';
require_once __DIR__ . '/../App/Session/Default.php';
$session = new App\Session\ChallengeSession();

if ($session->isLoggedIn()) {
    try {
        $upload = new \App\R\User\UserUpload($session);
        $success = $upload->upload();
        echo json_encode($success);
    } catch (Exception $e) {
        echo json_encode(array('success' => 0, 'message' => $e->getMessage()));
    }
}