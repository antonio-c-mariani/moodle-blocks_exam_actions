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
 * This file contains the Exam Actions block.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_exam_actions extends block_base {
    /**
     * block initializations
     */
    public function init() {
        global $CFG;
        $this->title  = get_string('title', 'block_exam_actions');
    }

    /**
     * block contents
     *
     * @return object
     */
    public function get_content() {
        global $SESSION, $PAGE, $USER, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

        if (is_siteadmin($USER->id) || isguestuser($USER->id)) {
            return $this->content;
        }

        if (isset($SESSION->exam->access_key)) {
            $fullname = null;
            if ($rec_key = $DB->get_record('exam_access_keys', array('access_key' => $SESSION->exam->access_key))) {
                if ($course = $DB->get_record('course', array('id' => $rec_key->courseid), 'id, fullname')) {
                    $fullname = $course->fullname;
                }
            }
            $this->content->text = html_writer::tag('B', get_string('computer_released', 'block_exam_actions', $fullname), array('class'=>'computer_released'));
            return $this->content;
        }

        $links = array();
        $msg_text = '';
        if (is_a($PAGE->context, 'context_course')) {
            if ($PAGE->course->id == 1) {
                $links[1] = html_writer::link(new moodle_url('/blocks/exam_actions/release_computer.php'), get_string('release_this_computer', 'block_exam_actions'));
            } else {
                list($identifier, $shortname) = \local_exam_authorization\authorization::split_shortname($PAGE->course->shortname, false);
                if (empty($identifier)) {
                    $this->content->text = html_writer::tag('B', get_string('local_course', 'block_exam_actions'));
                    return $this->content;
                }

                if (has_capability('moodle/backup:backupactivity', $PAGE->context)) {
                    $links[5] = html_writer::link(new moodle_url('/blocks/exam_actions/export_exam.php', array('courseid'=>$PAGE->context->instanceid)),
                                                 get_string('export_exam', 'block_exam_actions'));
                }
                if (has_capability('moodle/course:managegroups', $PAGE->context)) {
                    $links[4] = html_writer::link(new moodle_url('/blocks/exam_actions/sync_groups.php', array('courseid'=>$PAGE->context->instanceid)),
                                                 get_string('sync_groups', 'block_exam_actions'));
                }
                if (has_capability('local/exam_authorization:supervise_exam', $PAGE->context)) {
                    if ($PAGE->course->visible && $PAGE->course->startdate <= time()) {
                        $links[1] = html_writer::link(new moodle_url('/blocks/exam_actions/generate_access_key.php', array('courseid'=>$PAGE->context->instanceid)),
                                                         get_string('generate_access_key', 'block_exam_actions'));
                    } else {
                        $links[1] = get_string('generate_access_key', 'block_exam_actions');
                    }
                    $links[3] = html_writer::link(new moodle_url('/blocks/exam_actions/sync_students.php', array('courseid'=>$PAGE->context->instanceid)),
                                                 get_string('sync_students', 'block_exam_actions'));
                    $links[6] = html_writer::link(new moodle_url('/blocks/exam_actions/manage_sessions.php', array('courseid'=>$PAGE->context->instanceid)),
                                                 get_string('manage_sessions', 'block_exam_actions'));
                }
                if (has_capability('local/exam_authorization:monitor_exam', $PAGE->context)) {
                    $links[2] = html_writer::link(new moodle_url('/blocks/exam_actions/monitor_exam.php', array('courseid'=>$PAGE->context->instanceid)),
                                                 get_string('monitor_exam', 'block_exam_actions'));
                }
            }
        } else if (is_a($PAGE->context, 'context_user')) {
            if ($SESSION->exam->write_exam) {
                $links[2] = html_writer::link(new moodle_url('/blocks/exam_actions/release_courses.php'), get_string('new_course', 'block_exam_actions'));
            }
            if ($SESSION->exam->supervise_exam) {
                $links[1] = html_writer::link(new moodle_url('/blocks/exam_actions/generate_access_key.php'), get_string('generate_access_key', 'block_exam_actions'));
            }
            if (!$SESSION->exam->take_exam) {
                $links[3] = html_writer::link(new moodle_url('/blocks/exam_actions/review_permissions.php'), get_string('review_permissions', 'block_exam_actions'));
            }

            $msg_text = $this->append_messages();
        }

        if (!empty($links)) {
            ksort($links);
            $text = '';
            foreach ($links AS $l) {
                $text .= html_writer::tag('LI', $l);
            }
            $this->content->text = html_writer::tag('UL', $text);
        }

        $this->content->text .= $msg_text;

        return $this->content;
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true, 'course-view' => true, 'site' => true);
    }

    /**
     * The block should only be dockable when the title of the block is not empty
     * and when parent allows docking.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        global $PAGE;

        return !is_a($PAGE->context, 'context_course') || $PAGE->course->id != 1;

    }

    private function append_messages() {
        global $SESSION, $OUTPUT;

        if (isset($SESSION->exam->messages['warnings'])) {
            $warnings = $SESSION->exam->messages['warnings'];
            unset($SESSION->exam->messages['warnings']);
        } else {
            $warnings = array();
        }
        if (isset($SESSION->exam->messages['errors'])) {
            $errors = $SESSION->exam->messages['errors'];
            unset($SESSION->exam->messages['errors']);
        } else {
            $errors = array();
        }

        if (!$SESSION->exam->take_exam && !$SESSION->exam->write_exam && !$SESSION->exam->supervise_exam && !$SESSION->exam->monitor_exam) {
            $warnings['no_function'] = get_string('no_function', 'block_exam_actions');
        }

        $text = '';
        if (!empty($warnings)) {
            $msg_txt = '';
            foreach ($warnings AS $code=>$msg) {
                $msg_txt .= html_writer::tag('LI', $msg, array('class'=>'exam_warning'));
            }
            $text .= html_writer::tag('UL', $msg_txt);
        }
        if (!empty($errors)) {
            $msg_txt = '';
            foreach ($errors AS $code=>$msg) {
                $msg_txt .= html_writer::tag('LI', $msg, array('class'=>'exam_error'));
            }
            $text .= html_writer::tag('UL', $msg_txt);
        }

        if (empty($text)) {
            return '';
        }

        $title = html_writer::tag('H3', get_string('warnings', 'block_exam_actions'));
        $text = $OUTPUT->box($title . $text, 'generalbox boxaligncenter boxwidthwide');

        $js_text = '<script type="text/javascript">// <![CDATA[
                    var $_ = $.noConflict(true);
                // ]]></script>
                <script type="text/javascript">// <![CDATA[
                    $_(document).ready(function(){
                    document.getElementsByTagName("body")[0].appendChild(document.getElementById("layer"))
                    var style_rules = \'\';
                    var style = \'<style type="text/css">\' + style_rules + "</style>";
                    $_("head").append(style);

                    });
                    // ]]>

                    function fechar(id){
                        domHTML0 = document.getElementById(id);
                        domHTML1 = document.getElementById(id + "1");
                        domHTML0.parentNode.removeChild(domHTML0);
                        domHTML1.parentNode.removeChild(domHTML1);
                    }
                // ]]></script>';

        $html_text = '
            <div id="layer">
            <div style="z-index: 1000; position: fixed; top: 0px; left: 0px; opacity: 0.6; background-color: #333333;
                        width: 100%; height: 100%;" onmousedown="fechar(\'id\')" id="id"></div>
            <div style="z-index: 1000; position: fixed; top: 20%; left: 20%; width: 60%; height: 50%;
                        background-color: #ffffff;" id="id1">
            <table style="width: 100%; height: 100%;">
            <tbody>
            <tr valign="top" height="80%">
            <td style="padding: 10px;">';

        $html_text .= $text;

        $html_text .= '
            </td>
            </tr>
            <tr height="20%">
            <td style="padding: 10px;" valign="middle">
            <div align="right"><input value="Fechar" onclick="fechar(\'id\')" type="button" /></div>
            </td>
            </tr>
            </tbody>
            </table>
            </div>
            </div>';

        return $js_text . $html_text;
    }
}
?>
