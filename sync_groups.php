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
 * This file contains the Sync Groups page.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

list($baseurl, $returnurl, $course, $context) = block_exam_actions_set_course_page_header();
require_capability('moodle/course:managegroups', $context);

if (optional_param('cancel', false, PARAM_TEXT)) {
    redirect($returnurl);
    exit;
}

$synchronize = optional_param('synchronize', false, PARAM_TEXT);
$groupingids = optional_param_array('groupingids', array(), PARAM_INT);
$groupids = optional_param_array('groupids', array(), PARAM_INT);

list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname($course->shortname);
$remote_courses = block_exam_actions_remote_courses();
if (!isset($remote_courses[$identifier][$shortname])) {
    print_error('no_remote_course_found', 'block_exam_actions');
}
$remote_courseid = $remote_courses[$identifier][$shortname]->id;

list($remote_groupings, $remote_groups, $remote_groupings_groups) =
    block_exam_actions_sync_groupings_groups_and_members($identifier, $shortname,
            $course, $remote_courseid, $synchronize, $groupingids, $groupids);
if ($synchronize) {
    block_exam_actions_message(get_string('groups_synced', 'block_exam_actions'), 'success');
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('sync_groups_title', 'block_exam_actions') .
            $OUTPUT->help_icon('sync_groups', 'block_exam_actions'), 3);
block_exam_actions_print_messages();

$has_group = false;
$grouped = array();

$text = '';
if (!empty($remote_groupings)) {
    $text .= html_writer::tag('B', get_string('groupings', 'block_exam_actions'));
    $text .= html_writer::start_tag('ul');
    foreach ($remote_groupings_groups As $gr) {
        $text .= html_writer::start_tag('li');
        $checked = $remote_groupings[$gr->id]->localid ? true : false;
        $params = $checked ? array('disabled'=>'disabled') : null;
        $text .= html_writer::checkbox('groupingids[]', $gr->id, $checked, $gr->name, $params);
        $text .= html_writer::start_tag('ul');
        if(isset($gr->groups)) {
            foreach ($gr->groups as $g) {
                $checked = $remote_groups[$g->id]->localid ? true : false;
                $params = $checked ? array('disabled'=>'disabled') : null;
                $checkbox = html_writer::checkbox('groupids[]', $g->id, $checked, $g->name, $params);
                $text .= html_writer::tag('li', $checkbox);
                $grouped[$g->id] = true;
                $has_group = true;
            }
        }
        $text .= html_writer::end_tag('ul');
        $text .= html_writer::end_tag('li');
    }
    $text .= html_writer::end_tag('ul');
}

if (count($remote_groups) > count($grouped)) {
    $text .= html_writer::tag('B', get_string('groups', 'block_exam_actions'));
    $text .= html_writer::start_tag('ul');
    foreach ($remote_groups AS $gid=>$g) {
        if (!isset($grouped[$gid])) {
            $checked = $remote_groups[$g->id]->localid ? true : false;
            $params = $checked ? array('disabled'=>'disabled') : null;
            $checkbox = html_writer::checkbox('groupids[]', $g->id, $checked, $g->name, $params);
            $text .= html_writer::tag('li', $checkbox);
            $has_group = true;
        }
    }
    $text .= html_writer::end_tag('ul');
}

if ($has_group) {
    $textbtn = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'synchronize',
                    'value'=>get_string('sync_groups_button', 'block_exam_actions')));
    $textbtn .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'cancel', 'value'=>get_string('cancel')));
    $text .= html_writer::tag('div', $textbtn, array('class' => 'felement fgroup'));

    $formtext = html_writer::start_tag('form', array('method'=>'post'));
    $formtext .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'courseid', 'value'=>$course->id));
    $formtext .= $text;
    $formtext .= html_writer::end_tag('form');

    echo $OUTPUT->box($formtext, 'generalbox boxalignleft boxwidthwide exam_box exam_list');

} else {
    $text = html_writer::tag('B', get_string('no_groups_to_sync', 'block_exam_actions'));
    $text .= $OUTPUT->single_button($returnurl, get_string('back'));
    echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box');
}


// ----------------------------------------------------------------------------------------

$not_mapped_groups = groups_get_all_groups($course->id);
foreach ($remote_groups AS $r_group) {
    if ($r_group->localid) {
        unset($not_mapped_groups[$r_group->localid]);
    }
}

if (!empty($not_mapped_groups)) {
    $text = html_writer::tag('B', get_string('not_mapped_groups', 'block_exam_actions'));
    $text .= html_writer::start_tag('ul');

    foreach ($not_mapped_groups AS $gid=>$g) {
        $text .= html_writer::tag('li', $g->name);
    }
    $text .= html_writer::end_tag('ul');

    echo html_writer::empty_tag('br');
    echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box');
}

echo $OUTPUT->footer();
