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
 * This file contains the Sync Students page.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

list($baseurl, $returnurl, $course, $context) = block_exam_actions_set_course_page_header();
require_capability('local/exam_authorization:supervise_exam', $context);

echo $OUTPUT->header();

list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname($course->shortname);
$students = block_exam_actions_enrol_students($identifier, $shortname, $course);
$customfields = $DB->get_records_menu('user_info_field', null, 'shortname', 'shortname, name');

$data = array();
$ind = 0;
foreach ($students AS $st) {
    $ind++;
    $status = $st->enrol == ENROL_USER_SUSPENDED ? get_string('participationsuspended', 'enrol') : get_string('participationactive', 'enrol');
    $action = html_writer::tag('SPAN', get_string($st->action, 'block_exam_actions'), array('class'=>$st->action));
    $line = array($ind, $st->username, $st->firstname, $st->lastname, $st->auth, $status, $action);
    foreach ($customfields AS $f=>$name) {
        $field = 'profile_field_' . $f;
        $line[] = isset($st->$field) ? $st->$field : '';
    }
    $data[] = $line;
}

$table = new html_table();
$table->head = array('',
              get_string('username'),
              get_string('firstname'),
              get_string('lastname'),
              get_string('type_auth', 'plugin'),
              get_string('status'),
              get_string('action'),
             );
foreach ($customfields AS $f=>$name) {
    $table->head[] = $name;
}

$table->data = $data;

echo $OUTPUT->heading(get_string('sync_students_title', 'block_exam_actions'), 3);

echo html_writer::start_tag('DIV', array('class'=>'exam_box'));
echo html_writer::table($table);
echo html_writer::end_tag('DIV');

echo $OUTPUT->single_button($returnurl, get_string('back'));
echo $OUTPUT->footer();
