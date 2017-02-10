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


require_once __DIR__ . '/../App/R/Results.php';
require_once __DIR__ . '/../App/Session/Default.php';

function generatePointsString($resultsArr) {
    $str = "[";
    $k = sizeof($resultsArr);
    for ($i = 0; $i < $k - 1; ++$i) {
        $str .= '{x: ' . $i . ', y: ' . number_format($resultsArr[$i]['score'], 2) . ' },'; // {x: 1,y: 0}
    }
    $str .= '{x: ' . $i . ', y: ' . number_format($resultsArr[$i]['score'], 2) . ' }]'; // {x: 1,y: 0}
    return $str;
}

use App\R;

$session = new \App\Session\ChallengeSession();

if (!$session->isLoggedIn()) {
    echo '<form id="myForm" action="index.php" method="get">
            <noscript><input type="submit" value="Click here if you are not redirected."/></noscript>
         </form>
         <script type="text/javascript">
            document.getElementById(\'myForm\').submit();
         </script>';
} else {

    $resultsGetter = new R\Results($session->getUserId());
    $activeChallenges = $resultsGetter->getActiveChallenges();
    $lastSeven = $resultsGetter->getLastSubmissions(10);
    $lastSevenTotal = $resultsGetter->getLastSubmissionsGlobal(10);
    $bestResult = $resultsGetter->getBestSubmission()['score'];
    $bestResult = is_null($bestResult) ? 0 : number_format($bestResult, 2);
    $lastSubmission = 0;
    $userPoints = array(array('score' => 0));
    $globalPoints = array(array('score' => 0));
    if (count($lastSeven) > 0) {
        $lastSubmission = number_format($lastSeven[sizeof($lastSeven) - 1]['score'], 2);
        $userPoints = $lastSeven;
        $globalPoints = $lastSevenTotal;
    }

    $polyUser = generatePointsString($userPoints);
    $polyGlobal = generatePointsString($globalPoints);
    $admin = $session->isAdmin();
    $page = null;
    if (isset($_GET['page'])) {
        $page = $_GET['page'];
    }
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="description" content="Data Challenge for Analytics and Business Intelligence Spring 2017">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0">
        <title>Data Challenge</title>

        <!-- Add to homescreen for Chrome on Android
        <meta name="mobile-web-app-capable" content="yes">
        <link rel="icon" sizes="192x192" href="images/android-desktop.png">

        <!-- Add to homescreen for Safari on iOS
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black">
        <meta name="apple-mobile-web-app-title" content="Material Design Lite">
        <link rel="apple-touch-icon-precomposed" href="images/ios-desktop.png">

        <!-- Tile icon for Win8 (144x144 + tile color)
        <meta name="msapplication-TileImage" content="images/touch/ms-touch-icon-144x144-precomposed.png">
        <meta name="msapplication-TileColor" content="#3372DF">
        -->

        <link rel="shortcut icon" href="images/favicon.png">

        <!-- SEO: If your mobile URL is different from the desktop URL, add a canonical link to the desktop page https://developers.google.com/webmasters/smartphone-sites/feature-phones -->
        <!--
        <link rel="canonical" href="http://www.example.com/">
        -->

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
        <script type="text/javascript">
        </script>
        <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
        <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="js/material/material.min.js"></script>
        <script
                src="https://code.jquery.com/jquery-3.1.1.min.js"
                integrity="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="
                crossorigin="anonymous"></script>
        <!-- custom js -->
        <script type="text/javascript"
                src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.4.0/Chart.bundle.js"></script>
        <!--<script type="text/javascript" src="js/dropzone/dropzone.js"></script>-->
        <script type="text/javascript" src="js/main.js"></script>
        <link rel="stylesheet" href="css/home.css">
        <style>
            #view-source {
                position: fixed;
                display: block;
                right: 0;
                bottom: 0;
                margin-right: 40px;
                margin-bottom: 40px;
                z-index: 900;
            }
        </style>
    </head>
    <body>
    <div class="app-layout mdl-layout mdl-js-layout mdl-layout--fixed-drawer mdl-layout--fixed-header">
        <header class="mdl-layout__header mdl-color-text--white-100">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">Data Challenge</span>
                <div class="mdl-layout-spacer"></div>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--expandable">
                    <label class="mdl-button mdl-js-button mdl-button--icon" for="search">
                        <i class="material-icons">search</i>
                    </label>
                    <div class="mdl-textfield__expandable-holder">
                        <input class="mdl-textfield__input" type="text" id="search">
                        <label class="mdl-textfield__label" for="search">Enter your query...</label>
                    </div>
                </div>
                <!--
                <button class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon" id="hdrbtn">
                    <i class="material-icons">more_vert</i>
                </button>
                <ul class="mdl-menu mdl-js-menu mdl-js-ripple-effect mdl-menu--bottom-right" for="hdrbtn">
                    <li class="mdl-menu__item">About</li>
                    <li class="mdl-menu__item">Contact</li>
                    <li class="mdl-menu__item">Legal information</li>
                </ul>-->
            </div>
        </header>
        <div class="drawer mdl-layout__drawer mdl-color-text--grey-600">
            <header class="drawer-header">
                <h4><?php echo $session->getName(); ?></h4>
                <div class="avatar-dropdown">
                    <span><?php echo $session->getEmail(); ?></span>
                    <div class="mdl-layout-spacer"></div>
                    <button id="Logout" class="mdl-button mdl-js-button mdl-js-ripple-effect mdl-button--icon">
                        <i class="material-icons" role="presentation">arrow_drop_down</i>
                        <span class="visuallyhidden">Accounts</span>
                    </button>
                    <ul class="mdl-menu mdl-menu--bottom-right mdl-js-menu mdl-js-ripple-effect" for="Logout">
                        <li class="mdl-menu__item"><a style="text-decoration: none;" href="Logout.php">Logout</a></li>
                    </ul>
                </div>
            </header>
            <nav class="navigation mdl-navigation mdl-color-text--grey-600">
                <!--<a class="mdl-navigation__link" data-view="home"><i class="mdl-color-text--grey-600 material-icons"
                                                                    role="presentation">dashboard</i>Home</a>-->
                <a class="mdl-navigation__link" data-view="submit"><i
                            class="mdl-color-text--grey-600 material-icons"
                            role="presentation">backup</i>Submit</a>
                <a class="mdl-navigation__link" data-view="results"><i class="mdl-color-text--grey-600 material-icons"
                                                                       role="presentation">assessment</i>Results</a>

                <!--                <a class="mdl-navigation__link" href=""><i class="mdl-color-text--grey-600 material-icons"
                                                                           role="presentation">forum</i>Forums</a>
                                <a class="mdl-navigation__link" href=""><i class="mdl-color-text--grey-600 material-icons"
                                                                           role="presentation">flag</i>Updates</a>
                                <a class="mdl-navigation__link" href=""><i class="mdl-color-text--grey-600 material-icons"
                                                                           role="presentation">local_offer</i>Promos</a>
                                <a class="mdl-navigation__link" href=""><i class="mdl-color-text--grey-600 material-icons"
                                                                           role="presentation">shopping_cart</i>Purchases</a>
                                <a class="mdl-navigation__link" href=""><i class="mdl-color-text--grey-600 material-icons"
                                                                           role="presentation">people</i>Social</a>-->
                <div class="mdl-layout-spacer"></div>
                <?php if ($admin) { ?>
                    <a class="mdl-navigation__link" data-view="admin"><i class="mdl-color-text--grey-600 material-icons"
                                                                         role="presentation">settings</i>Admin</a>
                <?php } ?>
                <<!--a class="mdl-navigation__link" target="_blank"
                   href="https://bostoncollege.instructure.com/courses/1569146/assignments/6594464"><i
                            class="mdl-color-text--grey-600 material-icons"
                            role="presentation">help_outline</i><span
                            class="visuallyhidden">Help</span></a>-->
            </nav>
        </div>
        <main class="mdl-layout__content" id="main-panel">
            <div id="home"></div>
            <div id="results" style="display: none;">
                <div class="mdl-grid demo-content">
                    <div
                            class="mdl-cell mdl-shadow--2dp mdl-cell--6-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp">
                        <div
                                class="mdl-card mdl-cell mdl-cell--12-col">
                            <div class="mdl-card__title mdl-card--expand">
                                <h2 class="mdl-card__title-text">Last Score</h2>
                            </div>
                            <div class="mdl-card__media">
                                <canvas id="latestChart">
                                </canvas>
                            </div>
                            <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                                This is your last attempt.
                            </div>
                        </div>
                    </div>
                    <div
                            class="mdl-cell mdl-shadow--2dp mdl-cell--6-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp">
                        <div
                                class="mdl-card  mdl-cell mdl-cell--12-col">
                            <div class="mdl-card__title mdl-card--expand">
                                <h2 class="mdl-card__title-text">Best Result</h2>
                            </div>
                            <div class="mdl-card__media">
                                <canvas id="bestChart">
                                </canvas>
                            </div>
                            <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                                This is your best attempt.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mdl-grid demo-content">
                    <div
                            class="graphs mdl-cell mdl-shadow--2dp mdl-cell--12-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp">
                        <div class="mdl-card mdl-cell mdl-cell--12-col">
                            <div class="mdl-card__title mdl-card--expand">
                                <h2 class="mdl-card__title-text">Score History</h2>
                            </div>
                            <div class="mdl-card__media">
                                <canvas id="lastChart">
                                </canvas>
                            </div>
                            <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                                How you have done in the past.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="submit">
                <div class="mdl-grid demo-content">
                    <div id="userProgressBar" class="mdl-progress mdl-js-progress"
                         style="display: none; position: relative; float: left; width: 100%;"></div>
                    <div
                            class="mdl-cell mdl-shadow--2dp mdl-cell--4-col mdl-cell--8-col-tablet mdl-card">
                        <div class="mdl-card__title mdl-card--expand">
                            <h2 class="mdl-card__title-text">Most Recent Result</h2>
                        </div>
                        <div class="mdl-card__media">
                            <canvas id="lastChart1">
                            </canvas>
                        </div>
                        <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                            This is your last attempt.
                        </div>
                    </div>
                    <form id="dataChallengeUpload"
                          class="mdl-cell mdl-cell--4-col mdl-cell--8-col-tablet"
                          enctype="multipart/form-data">
                        <div class="mdl-card mdl-shadow--2dp" style="margin:0; width: 100%;">
                            <div class="mdl-card__title">
                                <h2 class="mdl-card__title-text">Submit Challenge</h2>
                            </div>
                            <?php if (count($activeChallenges) > 0) { ?>
                                <div class="mdl-card__media">
                                    <?php
                                    $i = 0;
                                    foreach ($activeChallenges as $challenge) {
                                        ?>
                                        <label class="mdl-radio mdl-js-radio pull-left" for="option<?php echo $i; ?>">
                                            <input type="radio" id="option<?php echo $i; ?>"
                                                   name="id" value="<?php echo $challenge['id']; ?>"
                                                   class="mdl-radio__button"
                                                   checked>
                                            <span class="mdl-radio__label"
                                                  id="label<?php echo $i; ?>"><?php echo $challenge['name']; ?></span>
                                            <div class="mdl-tooltip"
                                                 for="label<?php echo $i; ?>"><?php echo $challenge['about']; ?>
                                            </div>
                                        </label>
                                        <br>
                                        <br>
                                        <?php
                                        ++$i;
                                    } ?>
                                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--file">
                                        <input class="mdl-textfield__input" placeholder="Answers" type="text"
                                               id="userUploadFile"
                                               readonly/>
                                        <div class="mdl-button mdl-button--primary mdl-button--icon mdl-button--file">
                                            <i class="material-icons">attach_file</i><input type="file"
                                                                                            id="userUploadBtn"
                                                                                            required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mdl-card__actions mdl-card--border   mdl-card--expand">
                                    <input class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect"
                                           type="submit"
                                           value="Submit">
                                </div>
                            <?php } ?>
                        </div>
                    </form>
                    <div
                            class="mdl-cell mdl-shadow--2dp mdl-cell--4-col mdl-cell--8-col-tablet mdl-card">
                        <div
                                class="mdl-cell mdl-cell--12-col">
                            <div class="mdl-card__title mdl-card--expand">
                                <h2 class="mdl-card__title-text">Best Recent Result</h2>
                            </div>
                        </div>
                        <div class="mdl-card__media">
                            <canvas id="bestChart1">
                            </canvas>
                        </div>
                        <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                            This is your best recent attempt.
                        </div>
                    </div>
                </div>
                <div class="mdl-grid demo-content" id="AUC" style="display: none;">
                    <div class="mdl-cell mdl-cell--12-col">
                        <div
                                class="mdl-card mdl-shadow--2dp mdl-cell mdl-cell--12-col">
                            <div class="mdl-card__media">
                                <canvas id="aucChart1">
                                </canvas>
                            </div>
                            <div class="mdl-card__supporting-text mdl-color-text--grey-600">
                                This is your last AUC.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($admin) { ?>
                <div id="admin" style="display: none;">
                    <div class="mdl-grid demo-content">
                        <form id="dataChallengeAdminUpload"
                              class="mdl-cell mdl-cell--4-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp"
                              enctype="multipart/form-data">
                            <div class="mdl-card__title">
                                <h2 class="mdl-card__title-text">Create Challenge</h2>
                            </div>
                            <div class="mdl-card__supporting-text" id="createText">
                                Create a new data challenge.
                            </div>
                            <div class="mdl-card__media">
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input class="mdl-textfield__input" name="name" type="text" required
                                           aria-required=”true”>
                                    <label class="mdl-textfield__label" for="name">Name</label>
                                </div>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label">
                                    <input class="mdl-textfield__input" name="about" type="text"
                                           aria-required=”true”>
                                    <label class="mdl-textfield__label" for="about">Description</label>
                                </div>
                                <div class="mdl-textfield mdl-js-textfield mdl-textfield--file">
                                    <input class="mdl-textfield__input" placeholder="Answers" type="text"
                                           id="uploadFile"
                                           readonly/>
                                    <div class="mdl-button mdl-button--primary mdl-button--icon mdl-button--file">
                                        <i class="material-icons">attach_file</i><input type="file" id="uploadBtn"
                                                                                        required>
                                    </div>
                                </div>
                            </div>
                            <div class="mdl-card__actions mdl-card--border">
                                <input class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect"
                                       type="submit"
                                       value="Submit">
                                <div id="adminCreateProgressBar" class="mdl-progress mdl-js-progress"
                                     style="display: none;"></div>
                            </div>
                        </form>
                        <form id="dataChallengeAdminDelete"
                              class="mdl-cell mdl-cell--4-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp">
                            <div class="mdl-card__title">
                                <h2 class="mdl-card__title-text">Delete Challenge</h2>
                            </div>
                            <div class="mdl-card__supporting-text" id="delete-text">
                                Delete a Data Challenge.
                            </div>
                            <div class="mdl-card__media mdl-card--expand">
                                <?php
                                $i = 0;
                                foreach ($activeChallenges as $challenge) {
                                    ?>
                                    <label class="mdl-radio mdl-js-radio pull-left"
                                           for="option<?php echo $challenge['id']; ?>">
                                        <input type="radio" id="option<?php echo $challenge['id']; ?>"
                                               name="id" value="<?php echo $challenge['id']; ?>"
                                               class="mdl-radio__button"
                                               checked>
                                        <span class="mdl-radio__label"
                                              id="label<?php echo $i; ?>"><?php echo $challenge['name']; ?></span>
                                        <div class="mdl-tooltip"
                                             for="label<?php echo $i; ?>"><?php echo $challenge['about']; ?>
                                        </div>
                                    </label>
                                    <br>
                                    <br>
                                    <?php
                                    ++$i;
                                } ?>
                            </div>
                            <div class="mdl-card__actions mdl-card--border">
                                <input class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect"
                                       type="submit"
                                       value="Submit">
                                <div id="adminDeleteProgressBar" class="mdl-progress mdl-js-progress"
                                     style="display: none;"></div>
                            </div>
                        </form>
                        <form id="dataChallengeDownload"
                              class="mdl-cell mdl-cell--4-col mdl-cell--8-col-tablet mdl-card mdl-shadow-2dp">
                            <div class="mdl-card__title">
                                <h2 class="mdl-card__title-text">Download Results</h2>
                            </div>
                            <div class="mdl-card__supporting-text">
                                Download latest results by data challenge.
                            </div>
                            <div class="mdl-card__media mdl-card--expand">
                                <?php
                                foreach ($activeChallenges as $challenge) {
                                    ?>
                                    <label class="mdl-radio mdl-js-radio pull-left" for="option<?php echo $i; ?>">
                                        <input type="radio" id="option<?php echo $i; ?>"
                                               name="id" value="<?php echo $challenge['id']; ?>"
                                               class="mdl-radio__button"
                                               checked>
                                        <span class="mdl-radio__label"
                                              id="label<?php echo $i; ?>"><?php echo $challenge['name']; ?></span>
                                        <div class="mdl-tooltip"
                                             for="label<?php echo $i; ?>"><?php echo $challenge['about']; ?>
                                        </div>
                                    </label>
                                    <br>
                                    <br>
                                    <?php
                                    ++$i;
                                } ?>
                            </div>
                            <div class="mdl-card__actions mdl-card--border">
                                <input class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect"
                                       type="submit"
                                       value="Submit">
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>
        </main>
    </div>
    <script type="text/javascript">
        function makeCharts() {
            createLineChartSingle(
                document.getElementById("lastChart"),
                'Your Score',
                <?php echo $polyUser; ?>
            );
            createDoughnutChart(
                document.getElementById('latestChart'),
                [{
                    data: [1 -<?php echo $lastSubmission; ?>, <?php echo $lastSubmission; ?>],
                    backgroundColor: [
                        "rgb(242,241,239)",
                        "#36A2EB"
                    ]
                }],
                ["Error", "Correct"],
                "<?php echo $lastSubmission * 100; ?>"
            );
            createDoughnutChart(
                document.getElementById('bestChart'),
                [{
                    data: [1 -<?php echo $bestResult; ?>, <?php echo $bestResult; ?>],
                    backgroundColor: [
                        "rgb(242,241,239)",
                        "#36A2EB"
                    ]
                }],
                ["Error", "Correct"],
                "<?php echo $bestResult * 100; ?>"
            );
        }
        <?php
        if (!is_null($page)) {
            echo 'window.pageI = "' . $page . '";';
        }
        ?>
    </script>
    </body>
    </html>
    <?php
}