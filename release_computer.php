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
 * This file contains the Release Computer page.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/release_computer_form.php');

$baseurl = new moodle_url('/blocks/exam_actions/release_computer.php');
$returnurl = new moodle_url('/');

$key = optional_param('key', '', PARAM_TEXT);

if (!\local_exam_authorization\authorization::check_ip_header(false) || !\local_exam_authorization\authorization::check_network_header(false)) {
    \local_exam_authorization\authorization::add_to_log($key, 0, 'cd_needed');
    print_error('cd_needed', 'block_exam_actions', $returnurl);
}
if (!\local_exam_authorization\authorization::check_version_header(false)) {
    \local_exam_authorization\authorization::add_to_log($key, 0, 'invalid_cd_version');
    print_error('invalid_cd_version', 'block_exam_actions', $returnurl);
}

if (!\local_exam_authorization\authorization::check_ip_range_student(false)) {
    \local_exam_authorization\authorization::add_to_log($key, 0, 'out_of_student_ip_ranges');
    print_error('out_of_student_ip_ranges', 'block_exam_actions', $returnurl);
}

if (!empty($key)) {
    if (!$access_key = $DB->get_record('exam_access_keys', array('access_key' => $key))) {
        \local_exam_authorization\authorization::add_to_log($key, 0, 'access_key_unknown');
        print_error('access_key_unknown', 'block_exam_actions');
    }
    if ($access_key->timecreated + $access_key->timeout*60 < time()) {
        \local_exam_authorization\authorization::add_to_log($key, 0, 'access_key_timedout');
        print_error('access_key_timedout', 'block_exam_actions');
    }
    try {
        \local_exam_authorization\authorization::check_client_host($access_key);
    } catch (Exception $e) {
        \local_exam_authorization\authorization::add_to_log($key, 0, $e->getMessage());
        print_error($e->getMessage(), 'local_exam_authorization', $returnurl);
    }
} else {
    $site = get_site();
    $context = context_system::instance();

    $PAGE->set_url($baseurl);
    $PAGE->set_context($context);
    $PAGE->set_heading($site->fullname);
    $PAGE->set_pagelayout('standard');
    $PAGE->navbar->add(get_string('release_computer', 'block_exam_actions'));

    if (isloggedin()) {
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');

        $cancel_url = new moodle_url('/my');
        $logout_url = new moodle_url('/login/logout.php');
        echo $OUTPUT->confirm(get_string('must_logout', 'block_exam_actions'), $logout_url, $cancel_url);

        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;
    }

    $editform = new release_computer_form();
    if ($editform->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $editform->get_data()) {
        $key = $data->access_key;
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('release_this_computer', 'block_exam_actions'), 3);
        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
        $editform->display();
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;
    }
}

if (!isset($SESSION->exam)) {
    $SESSION->exam = new \stdClass();
}
$SESSION->exam->access_key = $key;
redirect($returnurl);
