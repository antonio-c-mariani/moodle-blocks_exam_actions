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
 * This file contains the Load Students page.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

list($baseurl, $returnurl, $context) = block_exam_actions_set_user_page_header();

if (optional_param('cancel', false, PARAM_TEXT)) {
    redirect($returnurl);
    exit;
}

if (!$SESSION->exam->write_exam) {
    print_error('no_permission', 'block_exam_actions');
}

\local_exam_authorization\authorization::check_ip_range_editor();

$confirm = optional_param('confirmadd', false, PARAM_BOOL);
if ($confirm && confirm_sesskey()) {
    $shortnames = optional_param_array('shortnames', array(), PARAM_TEXT);
    if (empty($shortnames)) {
        block_exam_actions_message(get_string('no_selected_courses', 'block_exam_actions'), 'error');
    } else {
        $moodles = \local_exam_authorization\authorization::get_moodles();
        $remote_courses = block_exam_actions_remote_courses();
        foreach ($shortnames as $cid => $encodedshortname) {
            list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname(base64_decode($encodedshortname));
            if (isset($remote_courses[$identifier][$shortname])) {
                $remote_course = $remote_courses[$identifier][$shortname];
                if (in_array('local/exam_remote:write_exam', $remote_course->capabilities)) {
                    $new_course = block_exam_actions_add_course($identifier, $remote_course);
                    block_exam_actions_enrol_students($identifier, $shortname, $new_course);
                } else {
                    $param = array('fullname' => $remote_course->fullname, 'moodle' => $moodles[$identifier]->description);
                    print_error('no_editor', 'block_exam_actions', $baseurl, (object)$param);
                }
            } else {
                $param = array('shortname' => $shortname, 'moodle' => $moodles[$identifier]->description);
                print_error('no_remote_course_found', 'block_exam_actions', $baseurl, (object)$param);
            }
        }
        \local_exam_authorization\authorization::review_permissions($USER);
        if (count($shortnames) == 1) {
            redirect(new moodle_url('/course/view.php', array('id'=>$new_course->id)));
        } else {
            block_exam_actions_message(get_string('released_courses', 'block_exam_actions'), 'success');
            redirect($baseurl);
        }
        exit;
    }
}

echo $OUTPUT->header();

$add = optional_param('add', false, PARAM_BOOL);
if ($add) {
    $shortnames = optional_param_array('shortnames', array(), PARAM_TEXT);
    if (empty($shortnames)) {
        block_exam_actions_message(get_string('no_selected_courses', 'block_exam_actions'), 'error');
    } else {
        echo $OUTPUT->heading(get_string('release_courses', 'block_exam_actions'), 3);

        $moodles = \local_exam_authorization\authorization::get_moodles();
        $remote_courses = block_exam_actions_remote_courses();

        $text = html_writer::start_tag('form', array('method' => 'post', 'action' => 'release_courses.php'));
        $text .= html_writer::start_tag('ul');
        foreach ($shortnames as $cid => $encodedshortname) {
            list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname(base64_decode($encodedshortname));
            $orig = $moodles[$identifier]->description;
            $fullname = $remote_courses[$identifier][$shortname]->fullname;
            $check = html_writer::checkbox("shortnames[{$cid}]", $encodedshortname, true, "{$fullname} ({$orig})");
            $text .= html_writer::tag('li', $check);
        }
        $text .= html_writer::end_tag('ul');
        $text .= html_writer::tag('span', get_string('confirm_release_courses', 'block_exam_actions'));
        $text .= html_writer::empty_tag('br');
        $text .= html_writer::empty_tag('br');

        $text .= html_writer::empty_tag('input', array('name'=>"sesskey", 'value'=>sesskey(), 'type'=>'hidden'));
        $textbtn = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'confirmadd', 'value'=>get_string('release_courses', 'block_exam_actions')));
        $textbtn .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'cancel', 'value'=>get_string('cancel')));
        $text .= html_writer::tag('div', $textbtn, array('class' => 'felement fgroup'));
        $text .= html_writer::start_tag('form');

        echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box');
        echo $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->heading(get_string('remote_courses', 'block_exam_actions'), 3);
block_exam_actions_print_messages();
echo get_string('remote_courses_msg', 'block_exam_actions');
$text = block_exam_actions_show_category_tree($USER->username);
echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box release_courses');

echo $OUTPUT->footer();
