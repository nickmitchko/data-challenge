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

namespace App\R\User;

/*
* Load answers csv
    1. Query DB
    2. Get File using array map
* Validate
    1. Check the size of the CSV File
    2. Check that the csv is has only numbers
* Calculate ROC
    1.a Use thresholding method
    1.b Use R to calculate (spawn shell)
* Return answer

*/

use App\Session\ChallengeSession;
use \PDO;

require_once __DIR__ . '/../../Session/Default.php';

class Evaluator {

    const THRESHOLD_NUM = 100;

    private $dbConnection;
    private $session;
    private $challengeId;
    private $answers;
    private $AUC;
    private $ACC;
    private $graph;

    function __construct($dbConn, $session, $dataChallengeId) {
        $this->session = $session;
        $this->verifyDBConnection($dbConn);
        $this->dbConnection = $dbConn;
        $this->challengeId = $dataChallengeId;
        $this->retrieveAnswersFromDb();
    }

    private function verifyDBConnection(\PDO $dbconn) {
        if (is_null($dbconn) || !isset($dbconn)) {
            throw new \RuntimeException("There was an error with the database connection");
        }
    }

    private function retrieveAnswersFromDb() {
        $query = $this->dbConnection->prepare('SELECT `answer_file` FROM Challenge WHERE id=:id AND enabled=1');
        $query->execute(array(':id' => $this->challengeId));
        $answerFile = $query->fetch(PDO::FETCH_ASSOC);
        require_once $answerFile['answer_file'];
        $this->answers = &$ANSWER_ARRAY;
    }

    public function evaluate($uploadedFile) {
        $submission = array_map('str_getcsv', file($uploadedFile));
        $contentLength = count($this->answers);
        $fprArray = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $tprArray = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $accArray = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $tp = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $tn = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $fp = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        $fn = array_fill(0, Evaluator::THRESHOLD_NUM, 0);
        for ($i = 1; $i < $contentLength; ++$i) {
            $actualClass = $this->answers[$i];
            $predictedProb = $submission[$i][0];
            $index = $predictedProb * Evaluator::THRESHOLD_NUM;
            $topIndex = ceil($index);
            $botIndex = floor($index);
            if ($topIndex == $botIndex) {
                --$botIndex;
            }
            if ($actualClass == 1) {
                for ($j = 0; $j <= $botIndex; ++$j) {
                    ++$tp[$j];
                }
                for ($j = $topIndex; $j < Evaluator::THRESHOLD_NUM; ++$j) {
                    ++$fn[$j];
                }
            } else {
                for ($j = 0; $j <= $botIndex; ++$j) {
                    ++$fp[$j];
                }
                for ($j = $topIndex; $j < Evaluator::THRESHOLD_NUM; ++$j) {
                    ++$tn[$j];
                }
            }
        }
        for ($t = 0; $t < Evaluator::THRESHOLD_NUM; ++$t) {
            $fprArray[$t] = $fp[$t] / ($fp[$t] + $tn[$t]);
            $tprArray[$t] = $tp[$t] / ($tp[$t] + $fn[$t]);
            $accArray[$t] = ($tp[$t] + $tn[$t]) / ($tp[$t] + $tn[$t] + $fp[$t] + $fn[$t]);
        }
        $sorted = $this->sortPoints($fprArray, $tprArray);
        if ($sorted) {
            $this->AUC = $this->calculateAreaUnderSetOfPoints($fprArray, $tprArray);
            $this->ACC = max($accArray);
            $this->graph = array($fprArray, $tprArray);
            $this->submitScore($this->AUC, $this->ACC);
        } else {
            throw new \RuntimeException('Error Sorting the AUROC graph');
        }
    }

    public function getResults($getGraph = true) {
        if ($getGraph) {
            return array('auc' => $this->AUC, 'graph' => $this->graph);
        } else {
            return array('auc' => $this->AUC);
        }
    }

    private function submitScore($AUC, $ACC) {
        $query = $this->dbConnection->prepare('INSERT INTO Submission (user_id, challenge_id, score, accuracy, submitted_at) VALUES (:user, :challenge, :score, :acc, NOW())');
        return $query->execute(array(
            ':user' => $this->session->getUserId(),
            ':challenge' => $this->challengeId,
            ':score' => $AUC,
            ':acc' => $ACC
        ));
    }

    private function calculateAreaUnderSetOfPoints($x, $y) {
        $totalArea = 0;
        for ($i = 0; $i < count($x) - 1; ++$i) {
            $base = $x[$i + 1] - $x[$i];
            $height = ($y[$i + 1] - $y[$i]);
            $totalArea += ($y[$i] * $base) + ($base * $height / 2);
        }
        return $totalArea;
    }

    private function validate($processedCsv) {
        if (count($processedCsv) != count($this->answers)) {
            throw new \RuntimeException('Uploaded model is a different length than the answer set. Are you using the right data?');
        }
    }

    /**
     * Sort the predicted model and actual answers such that they are easier to compute AUC.
     *
     * @param $array    Model Variable
     * @param $array2   Answer Variable (usually Evaluator::answers or $this->answers)
     * @return bool     Whether the points were sorted
     */
    private function sortPoints(&$array, &$array2) {
        return array_multisort($array, SORT_ASC, $array2);
    }
}