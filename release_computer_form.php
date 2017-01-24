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
 * This file contains the Release Computer form.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');

class release_computer_form extends moodleform {

    public function definition() {

        $mform = $this->_form;

        $mform->addElement('text', 'access_key', get_string('access_key', 'block_exam_actions'), 'maxlength="8" size="10"');
        $mform->addRule('access_key', get_string('required'), 'required', null, 'client');
        $mform->setType('access_key', PARAM_TEXT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'release_computer', get_string('release_computer', 'block_exam_actions'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        if (!$access_key = $DB->get_record('exam_access_keys', array('access_key' => $data['access_key']))) {
            $errors['access_key'] = get_string('access_key_unknown', 'block_exam_actions');
            \local_exam_authorization\authorization::add_to_log($data['access_key'], 0, 'access_key_unknown');
        } else if ($access_key->timecreated + $access_key->timeout*60 < time()) {
             $errors['access_key'] = get_string('access_key_timedout', 'block_exam_actions');
             \local_exam_authorization\authorization::add_to_log($data['access_key'], 0, 'access_key_timedout');
        } else {
            try {
                \local_exam_authorization\authorization::check_client_host($access_key);
            } catch (Exception $e) {
                $errors['access_key'] =  get_string($e->getMessage(), 'local_exam_authorization');
                \local_exam_authorization\authorization::add_to_log($data['access_key'], 0, $e->getMessage());
            }
        }

        return $errors;
    }

}
