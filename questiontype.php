<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Question type class for the yconrunner question type.
 *
 * @package    qtype
 * @subpackage yconrunner
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/yconrunner/question.php');

/**
 * The yconrunner question type.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_yconrunner extends question_type {

    public function move_files($questionid, $oldcontextid, $newcontextid) {
        parent::move_files($questionid, $oldcontextid, $newcontextid);
        $this->move_files_in_hints($questionid, $oldcontextid, $newcontextid);
    }

    protected function delete_files($questionid, $contextid) {
        parent::delete_files($questionid, $contextid);
        $this->delete_files_in_hints($questionid, $contextid);
    }

    public function save_question_options($question) {
        global $DB;

        $context = $question->context;

        $options = $DB->get_record('qtype_yconrunner', array('questionid' => $question->id));
        if (!$options) {
            $options = new stdClass();
            $options->questionid = $question->id;
            $options->contestid = $question->contestid ?? 0; // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
            $options->submissionid = $question->submissionid ?? 0; // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
            $DB->insert_record('qtype_yconrunner', $options);
        } else {
            $options->contestid = $question->contestid ?? 0; // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
            $options->submissionid = $question->submissionid ?? 0; // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
            $DB->update_record('qtype_yconrunner', $options);
        }

        $this->save_hints($question);
    }

    protected function initialise_question_instance(question_definition $question, $questiondata) {
        parent::initialise_question_instance($question, $questiondata);

        if (isset($questiondata->options)) {
            $question->set_contestid($questiondata->options->contestid ?? 0); // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
            $question->set_submissionid($questiondata->options->submissionid ?? 0); // Убедитесь, что используется значение по умолчанию, если свойство отсутствует
        }
    }

    public function get_random_guess_score($questiondata) {
        // TODO.
        return 0;
    }

    public function get_possible_responses($questiondata) {
        // TODO.
        return array();
    }

}
