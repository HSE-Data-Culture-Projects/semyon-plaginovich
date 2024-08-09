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
 * yconrunner question renderer class.
 *
 * @package    qtype
 * @subpackage yconrunner
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Generates the output for yconrunner questions.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_yconrunner_renderer extends qtype_renderer
{
    public function formulation_and_controls(question_attempt $qa, question_display_options $options)
    {
        $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var('answer');

        $submitbutton = html_writer::tag('button', get_string('submit', 'qtype_yconrunner'),
            array('type' => 'button', 'class' => 'btn btn-secondary ml-2', 'id' => 'submit-button'));

        $contestid = 0;
        $submissionid = 0;

        global $DB;
        $options = $DB->get_record('qtype_yconrunner', array('questionid' => $question->id));
        if ($options) {
            $contestid = $options->contestid;
            $submissionid = $options->submissionid;
        }

        $languages = array(
            'python' => 'Python',
            'cpp' => 'C++',
            'java' => 'Java',
            'csharp' => 'C#'
        );

        $languageoptions = html_writer::select($languages, 'language', '', null, array('class' => 'form-control d-inline', 'id' => 'language-select'));

        $fileupload = html_writer::empty_tag('input', array('type' => 'file', 'name' => 'file', 'id' => 'file-upload', 'class' => 'form-control d-inline'));

        $form = html_writer::start_tag('form', array(
            'method' => 'post',
            'id' => $qa->get_outer_question_div_unique_id(),
            'class' => 'mform',
        ));
        $form .= html_writer::start_tag('div');
        $form .= html_writer::tag('div', '', array('id' => 'question-text'));
        $form .= html_writer::start_tag('div', array('class' => 'ablock'));
        $form .= html_writer::tag('div', $languageoptions, array('class' => 'form-group'));
        $form .= html_writer::tag('div', $fileupload, array('class' => 'form-group'));
        $form .= html_writer::tag('div', '<div id="editor" style="height: 200px; width: 100%;">' . s($currentanswer) . '</div>', array('class' => 'form-group'));
        $form .= html_writer::tag('div', $submitbutton, array('class' => 'form-group'));
        // Поле для вывода сообщения скрыто изначально
        $form .= html_writer::tag('div', '', array('id' => 'result-message', 'class' => 'alert alert-info', 'role' => 'alert', 'style' => 'display:none;'));
        $form .= html_writer::end_tag('div');
        $form .= html_writer::end_tag('form');

        $form .= $this->page->requires->js_call_amd('qtype_yconrunner/yconrunner', 'init', array(array(
            'contestid' => $contestid,
            'submissionid' => $submissionid,
            'attemptid' => $qa->get_database_id(),
        )));

        return $form;
    }

    public function specific_feedback(question_attempt $qa)
    {
        // TODO.
        return '';
    }

    public function correct_response(question_attempt $qa)
    {
        // TODO.
        return '';
    }
}

