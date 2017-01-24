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
//
// Este bloco é parte do Moodle Provas - http://tutoriais.moodle.ufsc.br/provas/
// Este projeto é financiado pela
// UAB - Universidade Aberta do Brasil (http://www.uab.capes.gov.br/)
// e é distribuído sob os termos da "GNU General Public License",
// como publicada pela "Free Software Foundation".

/**
 * This file contains the Generate Access Key form.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class generate_access_key_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $courses = $this->_customdata['data'];

        if (count($courses) > 1) {
            $courses = array(0=>get_string('select_course', 'block_exam_actions')) + $courses;
        }

        $mform->addElement('hidden', 'origin', $this->_customdata['origin']);
        $mform->setType('origin', PARAM_TEXT);

        $mform->addElement('select', 'courseid', get_string('course', 'block_exam_actions'), $courses);
        $mform->addHelpButton('courseid', 'course', 'block_exam_actions');

        $options = array('1' => '1 ' . get_string('minute'),
                         '3' => '3 ' . get_string('minutes'),
                         '5' => '5 ' . get_string('minutes'),
                         '15' => '15 ' . get_string('minutes'));
        $mform->addElement('select', 'access_key_timeout', get_string('access_key_timeout', 'block_exam_actions'), $options);
        $mform->addHelpButton('access_key_timeout', 'access_key_timeout', 'block_exam_actions');
        $mform->setDefault('access_key_timeout', 5);

        $mform->addElement('selectyesno', 'verify_client_host', get_string('verify_client_host', 'block_exam_actions'));
        $mform->addHelpButton('verify_client_host', 'verify_client_host', 'block_exam_actions');
        $mform->setType('verify_client_host', PARAM_INT);
        $mform->setDefault('verify_client_host', 1);

        $buttons = array();
        $buttons[] = &$mform->createElement('submit', 'generate', get_string('generate_access_key', 'block_exam_actions'));
        $buttons[] = &$mform->createElement('cancel');
        $mform->addGroup($buttons, 'buttons', '', array(' '), false);
        $mform->closeHeaderBefore('buttons');

    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (empty($data['courseid'])) {
            $errors['courseid'] = get_string('empty_course', 'block_exam_actions');
        }

        return $errors;
    }

}

