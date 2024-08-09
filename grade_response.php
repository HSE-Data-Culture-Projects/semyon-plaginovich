<?php

require_once('../../../config.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/type/yconrunner/question.php');

// Получение данных из запроса
$input = json_decode(file_get_contents('php://input'), true);
$result = $input['result'];
$attemptid = $input['attemptid'];

$fraction = 0.0;
if (isset($result) && $result == 1) {
    $fraction = 1.0;
}

// Получение данных для вопроса и попытки
$attemptstep = $DB->get_record('question_attempt_steps', array('questionattemptid' => $attemptid));
if (!$attemptstep) {
    throw new moodle_exception('invalidattemptid',  'question');
}

// Обновление попытки вопроса
$transaction = $DB->start_delegated_transaction();

$DB->update_record('question_attempt_steps', array(
    'id' => $attemptstep->id,
    'state' => 'gradedright',
    'fraction' => $fraction
));

$transaction->allow_commit();

echo json_encode(['status' => 'success', 'fraction' => $fraction]);
