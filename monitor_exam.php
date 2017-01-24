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
 * This file contains the Monitor Exam page.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once(dirname(__FILE__).'/locallib.php');

list($baseurl, $returnurl, $course, $context) = block_exam_actions_set_course_page_header();
require_capability('local/exam_authorization:monitor_exam', $context);

echo $OUTPUT->header();

$tab_items = array('generated_access_keys', 'used_access_keys');
$tabs = array();
foreach ($tab_items AS $act) {
    $url = clone $baseurl;
    $url->param('action', $act);
    $tabs[$act] = new tabobject($act, $url, get_string($act, 'block_exam_actions'));
}

$action = optional_param('action', '' , PARAM_TEXT);
$action = isset($tabs[$action]) ? $action : reset($tab_items);

echo $OUTPUT->heading(get_string('monitor_exam_title', 'block_exam_actions'), 3);
print_tabs(array($tabs), $action);

switch($action) {

case 'generated_access_keys':
    $sql = "SELECT ak.*, u.firstname, lastname
              FROM {exam_access_keys} ak
              JOIN {user} u ON (u.id = ak.userid)
             WHERE ak.courseid = :courseid
          ORDER BY ak.timecreated DESC";
    $recs = $DB->get_records_sql($sql, array('courseid'=>$course->id));
    $data = array();
    foreach ($recs AS $rec) {
        $data[] = array(userdate($rec->timecreated),
                        $rec->access_key,
                        $rec->firstname.' '.$rec->lastname,
                        $rec->ip,
                        $rec->timeout . ' ' . ($rec->timeout == 1 ? get_string('minute') : get_string('minutes')),
                        $rec->verify_client_host == 1 ? get_string('yes') : get_string('no'),
                       );
    }

    $table = new html_table();
    $table->head  = array(get_string('createdon', 'block_exam_actions'),
                          get_string('access_key', 'block_exam_actions'),
                          get_string('createdby', 'block_exam_actions'),
                          get_string('real_ipaddress', 'block_exam_actions'),
                          get_string('access_key_timeout', 'block_exam_actions'),
                          get_string('verify_client_host', 'block_exam_actions'),
                         );
    $table->data = $data;
    break;

case 'used_access_keys':
    $order_options = array('used_time'=>'akl.time DESC, u.firstname, u.lastname',
                           'used_by'=>'u.firstname, u.lastname, akl.time',
                           'access_key'=>'akl.access_key, akl.time',
                           'real_ipaddress'=>'akl.ip, akl.time');
    $order = optional_param('order', '' , PARAM_TEXT);
    $orderby = isset($order_options[$order]) ? $order_options[$order] : $order_options['used_time'];

    $sql = "SELECT akl.*, ak.ip as ip_generated, u.firstname, lastname
              FROM {exam_access_keys_log} akl
              JOIN {exam_access_keys} ak ON (ak.access_key = akl.access_key)
         LEFT JOIN {user} u ON (u.id = akl.userid)
             WHERE ak.courseid = :courseid
          ORDER BY {$orderby}";
    $recs = $DB->get_records_sql($sql, array('courseid'=>$course->id, 'contextlevel'=>CONTEXT_COURSE));
    $data = array();
    foreach ($recs AS $rec) {
        $data[] = array(userdate($rec->time),
                        $rec->firstname.' '.$rec->lastname,
                        $rec->access_key,
                        $rec->ip,
                        $rec->ip_generated,
                        $rec->header_version,
                        $rec->header_ip,
                        $rec->header_network,
                        $rec->info);
    }

    $acturl = clone $baseurl;
    $acturl->param('action', $action);
    $head = array();
    foreach ($order_options AS $cmp=>$ord) {
        $url = clone $acturl;
        $url->param('order', $cmp);
        $head[] = html_writer::link($url, get_string($cmp, 'block_exam_actions'));
    }
    $head[] = get_string('real_ipaddress_generated', 'block_exam_actions');
    $head[] = get_string('header_version', 'block_exam_actions');
    $head[] = get_string('header_ip', 'block_exam_actions');
    $head[] = get_string('header_network', 'block_exam_actions');
    $head[] = get_string('info');

    $table = new html_table();
    $table->head = $head;
    $table->data = $data;
    break;
}

if (isset($table)) {
    echo html_writer::start_tag('DIV', array('class'=>'exam_box'));
    echo html_writer::table($table);
    echo html_writer::end_tag('DIV');
}

echo $OUTPUT->single_button($returnurl, get_string('back'));
echo $OUTPUT->footer();
