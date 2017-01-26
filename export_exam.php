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
 * Export exams to external moodle.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

list($baseurl, $returnurl, $course, $context) = block_exam_actions_set_course_page_header();
require_capability('moodle/backup:backupactivity', $context);

if (optional_param('cancel', false, PARAM_TEXT)) {
    redirect($returnurl);
    exit;
}

echo $OUTPUT->header();
$activities = get_array_of_activities($course->id);

list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname($course->shortname);

if (optional_param('export', false, PARAM_TEXT)) {
    $export_activities = optional_param_array('activities', array(), PARAM_TEXT);
    if (empty($export_activities)) {
        print_error('no_selected_activities', 'block_exam_actions', $baseurl);
    }
    $data = array();
    $adminid = $DB->get_field('user', 'id', array('username'=>'admin'));
    foreach ($export_activities AS $cmid=>$act_name) {
        // executa backup com permissão de Admin em função de dados de usuários
        try {
            $bc = new backup_controller(backup::TYPE_1ACTIVITY, $cmid, backup::FORMAT_MOODLE, backup::INTERACTIVE_NO, backup::MODE_GENERAL, $adminid);
            $bc->execute_plan();
            $results = $bc->get_results();
            $backup_file = $results['backup_destination']; // May be empty if file already moved to target location.
            if (empty($backup_file)) {
                $data[] = array($act_name, get_string('empty_backup_file', 'block_exam_actions'));
            } else {
                $result = block_exam_actions_export_activity($identifier, $shortname, $backup_file);
                $backup_file->delete();
                if (is_string($result)) {
                    $data[] = array($act_name, $result);
                } else {
                    $data[] = array($act_name, get_string('error') . ': '. var_export($result, true));
                }
            }
            $bc->destroy();
        } catch (Exception $e) {
            $data[] = array($act_name, $e->getMessage());
        }
    }

    echo $OUTPUT->heading(get_string('export_result', 'block_exam_actions'), 3);
    echo html_writer::start_tag('DIV', array('align'=>'center'));
    $table = new html_table();
    $table->head  = array(get_string('activity'),
                          get_string('status'));

    $table->data = $data;
    echo html_writer::table($table);
    echo html_writer::end_tag('DIV');
    echo $OUTPUT->single_button($returnurl, get_string('back'));
} else {
    $moodle = \local_exam_authorization\authorization::get_moodle($identifier);
    echo $OUTPUT->heading(get_string('export_exam_title', 'block_exam_actions', $moodle->description), 3);

    if (empty($activities)) {
        $text = $OUTPUT->heading(get_string('no_activities_to_export', 'block_exam_actions'), 4);
        $text .= $OUTPUT->single_button($returnurl, get_string('back'));
        echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box');
    } else {
        $text = html_writer::start_tag('form', array('method'=>'post', 'action'=>$baseurl));
        $text .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'courseid', 'value'=>$course->id));

        foreach ($activities AS $act) {
            $module = get_string('modulename', $act->mod);
            $name = "{$act->name} ({$module})";
            $text .= html_writer::tag('input', $name, array('type'=>'checkbox', 'name'=>"activities[{$act->cm}]", 'value'=>$name, 'class'=>'exam_checkbox'));
            $text .= html_writer::empty_tag('BR');
        }

        $textbtn = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'export', 'value'=>get_string('export', 'block_exam_actions')));
        $textbtn .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'cancel', 'value'=>get_string('cancel')));
        $text .= html_writer::tag('div', $textbtn, array('class' => 'felement fgroup'));

        $text .= html_writer::end_tag('form');

        echo get_string('export_exam_desc', 'block_exam_actions');
        echo $OUTPUT->box($text, 'generalbox boxalignleft boxwidthwide exam_box exam_list');
    }

}

echo $OUTPUT->footer();
