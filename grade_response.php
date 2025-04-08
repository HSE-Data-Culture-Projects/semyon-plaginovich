<?php
// Этот файл обрабатывает оценку ответа и сохраняет вердикт

require_once('../../../config.php');
require_once($CFG->libdir . '/questionlib.php');

// Получение данных из запроса
$input = json_decode(file_get_contents('php://input'), true);

// Проверка наличия необходимых параметров
if (!isset($input['result']) || !isset($input['attemptid'])) {
    throw new moodle_exception('invalidinput', 'question');
}

$result = $input['result']; // 1 для успешного решения (OK), 0 для неуспешного (Error)
$attemptid = $input['attemptid'];
$verdict = isset($input['verdict']) ? $input['verdict'] : 'Error'; // Значение по умолчанию

$fraction = ($result == 1) ? 1.0 : 0.0;

global $DB;

// Получение последнего шага попытки
$attemptsteps = $DB->get_records('question_attempt_steps', array('questionattemptid' => $attemptid), 'sequencenumber DESC', '*', 0, 1);
$attemptstep = reset($attemptsteps);
if (!$attemptstep) {
    throw new moodle_exception('invalidattemptid', 'question');
}

// Обновление состояния и fraction
$attemptstep->state = ($fraction == 1.0) ? 'gradedright' : 'gradedwrong';
$attemptstep->fraction = $fraction;

// Декодирование существующих опций
$options = json_decode($attemptstep->options, true);
if (!is_array($options)) {
    $options = array();
}

// Добавление или обновление вердикта
$options['verdict'] = $verdict;

// Кодирование опций обратно в JSON
$attemptstep->options = json_encode($options);

// Обновление записи в базе данных
$DB->update_record('question_attempt_steps', $attemptstep);

// Завершение транзакции
$transaction = $DB->start_delegated_transaction();
$transaction->allow_commit();

// Возврат успешного ответа
echo json_encode(['status' => 'success', 'fraction' => $fraction]);
