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
 * Listing of all sessions for a user.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require(__DIR__ . '/../../config.php');
require_once('./locallib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

list($baseurl, $returnurl, $course, $context) = block_exam_actions_set_course_page_header();
require_capability('local/exam_authorization:supervise_exam', $context);

$allnames = implode(',', get_all_user_name_fields());
$sql = "SELECT ses.id, ses.timecreated, ses.timemodified, ses.firstip, ses.lastip, ses.sid, ue.userid, u.username, {$allnames}
          FROM {enrol} en
          JOIN {user_enrolments} ue ON (ue.enrolid = en.id AND ue.status = :uestatus)
          JOIN {sessions} ses ON (ses.userid = ue.userid)
          JOIN {user} u ON (u.id = ue.userid)
         WHERE en.courseid = :courseid
           AND en.status = :enstatus";
$params = array('courseid' => $course->id, 'enstatus' => ENROL_INSTANCE_ENABLED, 'uestatus' => ENROL_USER_ACTIVE);

$delete = optional_param('delete', 0, PARAM_INT);
$confirmdelete = optional_param('confirmdelete', 0, PARAM_INT);
if (($delete || $confirmdelete) && confirm_sesskey()) {
    $sql .= " AND ses.id = :sesid";
    $params['sesid'] = empty($delete) ? $confirmdelete : $delete;
    if ($session = $DB->get_record_sql($sql, $params)) {
        if ($session->sid === session_id()) {
            print_error('delete_current_session', 'block_exam_actions');
        } else {
            if ($confirmdelete) {
                \core\session\manager::kill_session($session->sid);
                redirect($baseurl, get_string('deleted_session', 'block_exam_actions'), 5);
            } else {
                echo $OUTPUT->header();
                echo $OUTPUT->heading(get_string('manage_sessions_title', 'block_exam_actions'));
                $baseurl->param('confirmdelete', $delete);
                $baseurl->param('sesskey', sesskey());
                $name = fullname($session) . " ({$session->username})";
                $message = get_string('confirm_delete_session', 'block_exam_actions', $name);
                echo $OUTPUT->confirm($message, $baseurl, $returnurl);
                echo $OUTPUT->footer();
                exit;
            }
        }
    } else {
        print_error('unknown_session', 'block_exam_actions');
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage_sessions_title', 'block_exam_actions'));

$data = array();
$current = session_id();
foreach ($DB->get_recordset_sql($sql, $params) as $ses) {
    $lastaccess = block_exam_actions_format_duration(time() - $ses->timemodified);
    $url = new moodle_url($baseurl, array('delete' => $ses->id, 'sesskey' => sesskey()));
    if ($ses->sid === $current) {
        $deletelink = '';
    } else {
        $deletelink = html_writer::link($url, get_string('logout'));
    }
    $data[] = array($ses->username, fullname($ses), userdate($ses->timecreated), $lastaccess, block_exam_actions_format_ip($ses->lastip), $deletelink);
}

$table = new html_table();
$table->head  = array(get_string('username'), get_string('fullname'), get_string('login'), get_string('lastaccess'), get_string('lastip'), get_string('action'));
$table->align = array('left', 'left', 'left', 'left', 'right');
$table->data  = $data;
echo html_writer::table($table);

echo $OUTPUT->single_button($returnurl, get_string('back'));
echo $OUTPUT->footer();

