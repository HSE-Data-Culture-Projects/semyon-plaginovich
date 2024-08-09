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
 * Defines the editing form for the yconrunner question type.
 *
 * @package    qtype
 * @subpackage yconrunner
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();


/**
 * yconrunner question editing form definition.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_yconrunner_edit_form extends question_edit_form
{

    protected function definition_inner($mform)
    {
        $this->add_interactive_settings();

        $mform->addElement('text', 'contestid', get_string('contestid', 'qtype_yconrunner'), array('size' => 10));
        $mform->setType('contestid', PARAM_INT);
        $mform->addRule('contestid', null, 'required', null, 'client');

        $mform->addElement('text', 'submissionid', get_string('submissionid', 'qtype_yconrunner'), array('size' => 50));
        $mform->setType('submissionid', PARAM_TEXT);
        $mform->addRule('submissionid', null, 'required', null, 'client');
    }

    protected function data_preprocessing($question)
    {
        $question = parent::data_preprocessing($question);
        $question = $this->data_preprocessing_hints($question);

        global $DB;
        $options = $DB->get_record('qtype_yconrunner', array('questionid' => $question->id));

        if (isset($question->options)) {
            $question->contestid = $options->contestid;
            $question->submissionid = $options->submissionid;
        }

        return $question;
    }

    public function save_question($question, $form)
    {
        $question = parent::save_question($question, $form);

        // Сохраняем поля contestid и submissionid
        $question->contestid = $form->contestid;
        $question->submissionid = $form->submissionid;

        // Обновляем запись в базе данных
        global $DB;
        $DB->update_record('qtype_yconrunner', array(
            'questionid' => $question->id,
            'contestid' => $question->contestid,
            'submissionid' => $question->submissionid,
        ));

        return $question;
    }

    public function qtype()
    {
        return 'yconrunner';
    }
}

