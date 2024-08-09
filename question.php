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
 * yconrunner question definition class.
 *
 * @package    qtype
 * @subpackage yconrunner
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Represents a yconrunner question.
 *
 * @copyright  THEYEAR YOURNAME (YOURCONTACTINFO)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class qtype_yconrunner_question extends question_graded_automatically
{

    public $contestid;
    public $submissionid;

    public function set_contestid($contestid)
    {
        $this->contestid = $contestid;
    }

    public function get_contestid()
    {
        return $this->contestid;
    }

    public function set_submissionid($submissionid)
    {
        $this->submissionid = $submissionid;
    }

    public function get_submissionid()
    {
        return $this->submissionid;
    }

    public function get_validation_error(array $response)
    {
        // TODO.
        return '';
    }

    public function get_correct_response()
    {
        // TODO.
        return array();
    }

    public function check_file_access($qa, $options, $component, $filearea,
                                      $args, $forcedownload)
    {
        // TODO.
        if ($component == 'question' && $filearea == 'hint') {
            return $this->check_hint_file_access($qa, $options, $args);
        } else {
            return parent::check_file_access($qa, $options, $component, $filearea,
                $args, $forcedownload);
        }
    }

    public function grade_response(array $response)
    {
        if (isset($response['result'])) {
            $result = $response['result'];
            $fraction = 0.0;

            if ($result == 1) {
                $fraction = 1.0;
            }

            return array($fraction, question_state::graded_state_for_fraction($fraction));
        } else {
            return array(0.0, question_state::$needsgrading);
        }
    }

    public function get_expected_data()
    {
        return array('grade' => PARAM_FLOAT);
    }

    public function summarise_response(array $response)
    {
        return $response['answer'] ?? '';
    }

    public function is_complete_response(array $response)
    {
        return true;
    }

    public function is_gradable_response(array $response)
    {
        return true;
    }

    public function is_same_response(array $prevresponse, array $newresponse)
    {
        return $prevresponse === $newresponse;
    }

    public function compute_final_grade($responses, $totaltries)
    {
        // TODO.
        return 0;
    }
}