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
    public function formulation_and_controls(question_attempt $qa, question_display_options $options) {
        $question = $qa->get_question();
        $currentanswer = $qa->get_last_qt_var('answer');
        
        // Получение последнего шага попытки
        $laststep = $qa->get_last_step();
        $verdict = '';
        if ($laststep) {
            $stepoptions = $laststep->get_qt_var('options'); // Извлечение options из шага
            if (is_array($stepoptions) && isset($stepoptions['verdict'])) {
                $verdict = $stepoptions['verdict'];
            }
        }
        
        // Формирование элементов формы
        $submitbutton = html_writer::tag('button', get_string('submit', 'qtype_yconrunner'),
            array('type' => 'button', 'class' => 'btn btn-secondary ml-2', 'id' => 'submit-button'));
        
        $contestid = 0;
        $submissionid = 0;
        
        global $DB;
        $optionsrecord = $DB->get_record('qtype_yconrunner', array('questionid' => $question->id));
        if ($optionsrecord) {
            $contestid = $optionsrecord->contestid;
            $submissionid = $optionsrecord->submissionid;
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
        $form .= html_writer::empty_tag('input', array(
            'type' => 'hidden',
            'name' => $qa->get_qt_field_name('answer'),
            'id' => 'answer-field',
            'value' => s($currentanswer)
        ));
        $form .= html_writer::tag('div', $submitbutton, array('class' => 'form-group'));
        
        // Добавление контейнера для истории вердиктов
        $form .= html_writer::tag('h5', 'История вердиктов:', array('class' => 'mt-4'));
        $form .= html_writer::tag('ul', '', array('id' => 'verdict-history', 'class' => 'list-group'));
        
        // Отображение последнего вердикта, если доступен
        if (!empty($verdict)) {
            // Можно добавить разные классы для разных вердиктов
            $alertClass = ($verdict === 'OK') ? 'alert-success' : 'alert-danger';
            $form .= html_writer::tag('div', s($verdict), array('id' => 'result-message', 'class' => "alert $alertClass mt-2", 'role' => 'alert'));
        } else {
            // Скрытая область для сообщения
            $form .= html_writer::tag('div', '', array('id' => 'result-message', 'class' => 'alert alert-info mt-2', 'role' => 'alert', 'style' => 'display:none;'));
        }
        
        $form .= html_writer::end_tag('div');
        $form .= html_writer::end_tag('form');
        
        // Подключение JavaScript модуля
        $this->page->requires->js_call_amd('qtype_yconrunner/yconrunner', 'init', array(array(
            'contestid' => $contestid,
            'submissionid' => $submissionid,
            'attemptid' => $qa->get_database_id(),
        )));
        
        return $form;
    }

    public function specific_feedback(question_attempt $qa)
    {
        return '';
    }

    public function correct_response(question_attempt $qa)
    {
        return '';
    }
}

