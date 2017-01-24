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
 * This file contains the support functions for Exam Actions block.
 *
 * @package    block_exam_actions
 * @author     Antonio Carlos Mariani
 * @copyright  2010 onwards Universidade Federal de Santa Catarina (http://www.ufsc.br)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/coursecatlib.php');
require_once($CFG->dirroot . '/course/lib.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->dirroot . '/group/lib.php');

function block_exam_actions_set_course_page_header() {
    global $PAGE;

    $courseid = required_param('courseid', PARAM_INT);
    if (!$course = get_course($courseid)) {
        print_error('invalidcourseid');
    }
    require_login($course);
    $context = context_course::instance($courseid);

    $baseurl = new moodle_url(qualified_me());
    $baseurl->remove_all_params();
    $baseurl->param('courseid', $courseid);
    $arr_path = explode('/', $baseurl->get_path());
    $script = basename(end($arr_path), '.php');

    $PAGE->set_course($course);
    $PAGE->set_url($baseurl);
    $PAGE->set_context($context);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_pagelayout('course');
    $PAGE->set_title(get_string($script, 'block_exam_actions'));
    $PAGE->navbar->add(get_string($script, 'block_exam_actions'), $baseurl);

    $courseurl = new moodle_url('/course/view.php', array('id'=>$courseid));

    return array($baseurl, $courseurl, $course, $context);
}

function block_exam_actions_set_user_page_header() {
    global $PAGE, $USER;

    require_login();
    $context = context_user::instance($USER->id);

    $baseurl = new moodle_url(qualified_me());
    $baseurl->remove_all_params();
    $arr_path = explode('/', $baseurl->get_path());
    $script = basename(end($arr_path), '.php');

    $site = get_site();

    $PAGE->set_url($baseurl);
    $PAGE->set_context($context);
    $PAGE->set_heading($site->fullname);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title(get_string($script, 'block_exam_actions'));
    $PAGE->navbar->add(get_string($script, 'block_exam_actions'), $baseurl);

    $myurl = new moodle_url('/my');

    return array($baseurl, $myurl, $context);
}

function block_exam_actions_remote_courses() {
    global $SESSION, $USER;

    if (!isset($SESSION->exam->user_courses)) {
        $SESSION->exam->user_courses = \local_exam_authorization\authorization::user_courses($USER->username);
    }

    return $SESSION->exam->user_courses;
}

function block_exam_actions_add_course($identifier, $remote_course) {
    global $DB;

    $moodle = \local_exam_authorization\authorization::moodle($identifier);
    if (!$parent = $DB->get_field('course_categories', 'id', array('idnumber' => $identifier))) {
        $new_cat = new StdClass();
        $new_cat->name = $moodle->description;
        $new_cat->idnumber = $identifier;
        $new_cat->parent = 0;
        $new_cat = coursecat::create($new_cat);
        $parent = $new_cat->id;
    }

    if (!$catid = $DB->get_field('course_categories', 'id', array('idnumber' => $identifier . '_' . $remote_course->categoryid))) {
        $catid = $parent;
        $remote_categories = block_exam_actions_remote_categories($identifier, array($remote_course->categoryid));
        if (isset($remote_categories[$remote_course->categoryid])) {
            foreach ($remote_categories[$remote_course->categoryid]->path AS $rcid) {
                if (!$catid = $DB->get_field('course_categories', 'id', array('idnumber' => $identifier . '_' . $rcid))) {
                    $new_cat = new StdClass();
                    $new_cat->name = $remote_categories[$rcid]->name;
                    $new_cat->idnumber = $identifier . '_' . $rcid;
                    $new_cat->parent = $parent;
                    $new_cat = coursecat::create($new_cat);
                    $catid = $new_cat->id;
                }
                $parent = $catid;
            }
        }
    }

    try {
        $newcourse = new stdClass;
        $newcourse->shortname = $identifier . '_' . $remote_course->shortname;
        $newcourse->fullname  = $remote_course->fullname;
        $newcourse->category  = $catid;
        $newcourse->visible   = 0;
        $newcourse->enrollable   = 0;
        $newcourse->startdate    = time();
        return create_course($newcourse);
    } catch (Exception $e) {
        print_error($e->getMessage());
    }
}

// return a local user

function block_exam_actions_add_or_update_remote_user($r_user, $customfields=array()) {
    global $CFG, $DB;

    $userfields = array('username', 'firstname', 'lastname', 'email');
    $userfields_str = 'id, auth, ' . implode(', ', $userfields);

    if ($user = $DB->get_record('user', array('username' => $r_user->username), $userfields_str)) {
        $update = false;
        foreach ($userfields AS $field) {
            if ( $user->$field != $r_user->$field) {
                $user->$field = $r_user->$field;
                $update = true;
            }
        }
        if ($update) {
            user_update_user($user, false);
        }
    } else {
        $user = new stdClass();
        $user->confirmed   = 1;
        $user->mnethostid  = $CFG->mnet_localhost_id;
        $user->lang        = $CFG->lang;
        foreach ($userfields AS $field) {
            $user->$field = $r_user->$field;
        }

        $default_auth_plugin = \local_exam_authorization\authorization::config('auth_plugin');
        $user->auth = empty($default_auth_plugin) ? 'manual' : $default_auth_plugin;
        $user->id = user_create_user($user, false);
    }

    if (!empty($customfields)) {
        $update = false;
        foreach ($customfields AS $f => $fid) {
            if (isset($r_user->$f)) {
                $field = 'profile_field_' . $f;
                $user->$field = $r_user->$f;
                $update = true;
            }
        }
        if ($update) {
            profile_save_data($user);
        }
    }

    $user->remote_id = $r_user->remote_id;
    return $user;
}

function block_exam_actions_enrol_students($identifier, $shortname, $course) {
    global $DB, $CFG;

    if (!enrol_is_enabled('manual')) {
        print_error('Enrol manual plugin is not enabled');
    }
    if (!$enrol = enrol_get_plugin('manual')) {
        print_error('Enrol manual plugin is not enabled');
    }
    if ($instances = $DB->get_records('enrol', array('enrol' => 'manual', 'courseid' => $course->id), 'sortorder,id ASC')) {
        $enrol_instance = reset($instances);
    } else {
        $enrol_instance = $enrol->add_default_instance($course);
    }
    $roleid = $DB->get_field('role', 'id', array('shortname' => 'student'), MUST_EXIST);

    $userfields = array('username', 'firstname', 'lastname', 'email');
    $userfields_str = 'id, auth, ' . implode(', ', $userfields);
    $customfields = $DB->get_records_menu('user_info_field', null, 'shortname', 'shortname, id');

    $r_students = block_exam_actions_remote_students($identifier, $shortname, $userfields, array_keys($customfields));
    $sql = "SELECT ue.userid, MAX(ue.status) AS status
              FROM {enrol} e
              JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
              JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :contextcourse)
              JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid AND ra.roleid = :roleid)
             WHERE e.enrol = 'manual'
               AND e.courseid = :courseid
          GROUP BY ue.userid";
    $already_enrolled = $DB->get_records_sql($sql, array('courseid' => $course->id, 'roleid' => $roleid, 'contextcourse' => CONTEXT_COURSE));

    $result_students = array();
    foreach ($r_students AS $r_student) {
        $student = block_exam_actions_add_or_update_remote_user($r_student, $customfields);

        if (isset($already_enrolled[$student->id])) {
            $student->enrol = $already_enrolled[$student->id]->status;
            $student->action = 'kept';
            unset($already_enrolled[$student->id]);
        } else {
            $enrol->enrol_user($enrol_instance, $student->id, $roleid, 0, 0, ENROL_USER_SUSPENDED);
            $student->enrol = ENROL_USER_SUSPENDED;
            $student->action = 'enrolled';
        }

        $result_students[$student->id] = $student;
    }

    foreach ($already_enrolled AS $userid => $st) {
        if ($user = $DB->get_record('user', array('id' => $userid), $userfields_str)) {
            $user->enrol = $st->status;
            $user->action = 'unenrolled';
            $result_students[$user->id] = $user;

            $sql = "SELECT ue.*
                      FROM {enrol} e
                      JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
                      JOIN {context} ctx ON (ctx.instanceid = e.courseid AND ctx.contextlevel = :contextcourse)
                      JOIN {role_assignments} ra ON (ra.contextid = ctx.id AND ra.userid = ue.userid AND ra.roleid = :roleid)
                     WHERE e.enrol = 'manual'
                       AND e.courseid = :courseid";
            foreach ($DB->get_records_sql($sql, array('courseid' => $course->id, 'roleid' => $roleid, 'contextcourse' => CONTEXT_COURSE)) AS $instance) {
                $enrol->unenrol_user($enrol_instance, $userid);
            }
        }
    }

    return $result_students;
}

function block_exam_actions_show_category_tree() {
    $moodles = \local_exam_authorization\authorization::moodles();
    $tree = block_exam_actions_mount_category_tree();

    $text = html_writer::start_tag('form', array('method' => 'post', 'action' => 'release_courses.php'));
    $text .= html_writer::start_tag('ol', array('class' => 'tree'));
    $has_course = false;
    foreach ($tree AS $identifier => $categories) {
        $text .= html_writer::start_tag('li');
        $text .= html_writer::tag('span', $moodles[$identifier]->description, array('class' => 'identifier'));
        $text .= html_writer::start_tag('ol');
        list($has_c, $txt) = block_exam_actions_show_categories($identifier, $categories);
        $has_course = $has_course || $has_c;
        $text .= $txt;
        $text .= html_writer::end_tag('ol');
        $text .= html_writer::end_tag('li');
    }
    $text .= html_writer::end_tag('ol');
    if ($has_course) {
        $textbtn = html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'add', 'value'=>get_string('release_courses', 'block_exam_actions')));
        $textbtn .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'cancel', 'value'=>get_string('cancel')));
        $text .= html_writer::tag('div', $textbtn, array('class' => 'felement fgroup'));

    }
    $text .= html_writer::start_tag('form');

    return $text;
}

function block_exam_actions_show_categories($identifier, $categories) {
    global $CFG, $OUTPUT, $DB;

    $has_course = false;
    $text = '';
    foreach ($categories AS $cat) {
        $text .= html_writer::start_tag('LI');
        $label = "{$identifier}_category_{$cat->id}";
        $folder_img = html_writer::empty_tag('img', array('src' =>  $OUTPUT->pix_url('f/folder'), 'class' => 'exam_pix'));
        $text .= html_writer::tag('LABEL', $folder_img . $cat->name, array('for' => $label));
        $text .= html_writer::empty_tag('INPUT', array('type' => 'checkbox', 'id' => $label, 'class' => 'exam_category_tree'));
        $text .= html_writer::start_tag('OL');
        if (!empty($cat->courses)) {
            foreach ($cat->courses AS $c) {
                $local_shortname = "{$identifier}_{$c->shortname}";
                $encodedshortname = urlencode($c->shortname);
                $encodedidentifier = urlencode($identifier);
                if ($id = $DB->get_field('course', 'id', array('shortname'=>$local_shortname))) {
                    $check = html_writer::checkbox("shortnames[{$encodedshortname}]", $encodedidentifier, true, '', array('disabled'=>'disabled'));
                    $fullname = html_writer::link(new moodle_url('/course/view.php', array('id' => $id)), $c->fullname);
                } else {
                    $check = html_writer::checkbox("shortnames[{$encodedshortname}]", $encodedidentifier, false);
                    $fullname = '&nbsp;' . $c->fullname;
                    $has_course = true;
                }
                $img = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/course')));
                $text .= html_writer::tag('LI', "{$check}{$img}{$fullname}", array('class' => 'course'));
            }
        }
        if (!empty($cat->sub)) {
            list($has_c, $txt) = block_exam_actions_show_categories($identifier, $cat->sub);
            $has_course = $has_course || $has_c;
            $text .= $txt;
        }
        $text .= html_writer::end_tag('OL');
        $text .= html_writer::end_tag('LI');
    }
    return array($has_course, $text);
}

function block_exam_actions_mount_category_tree() {
    $remote_courses = block_exam_actions_remote_courses();
    $moodles = \local_exam_authorization\authorization::moodles();

    $tree = array();
    foreach ($remote_courses AS $identifier => $rcs) {
        $rcourses = array();
        foreach ($rcs AS $c) {
            if (in_array('local/exam_remote:write_exam', $c->capabilities)) {
                if (!isset($rcourses[$c->categoryid])) {
                    $rcourses[$c->categoryid] = array();
                }
                $rcourses[$c->categoryid][] = $c;
            }
        }

        if (!empty($rcourses)) {
            $cats = block_exam_actions_remote_categories($identifier, array_keys($rcourses));
            foreach ($cats AS $catid => $cat) {
                $cat->sub = array();
                $cat->courses = isset($rcourses[$catid]) ? $rcourses[$catid] : array();
            }
            foreach ($cats AS $catid => $cat) {
                $size = count($cat->path);
                if ($size > 1) {
                    $fatherid = $cat->path[$size-2];
                    $cats[$fatherid]->sub[$catid] = $cat;
                }
            }
            foreach (array_keys($cats) AS $catid) {
                $cat = $cats[$catid];
                if (count($cats[$catid]->path) > 1) {
                    unset($cats[$catid]);
                }
            }
            $tree[$identifier] = $cats;
        }
    }
    return $tree;
}


function block_exam_actions_remote_students($identifier, $course_shortname, $userfields=array(), $customfields=array()) {
    if (empty($userfields)) {
        $userfields = array('username');
    }

    $function = 'local_exam_remote_get_students';
    $params = array('shortname' => $course_shortname);
    $params['userfields'] = array_merge($userfields, $customfields);

    $r_students = array();
    foreach (\local_exam_authorization\authorization::call_remote_function($identifier, $function, $params) as $st) {
        $r_student = new stdClass();
        $r_student->remote_id = $st->id;
        foreach ($st->userfields AS $obj) {
            $field = $obj->field;
            $r_student->$field = $obj->value;
        }
        $r_students[$r_student->remote_id] = $r_student;
    }
    return $r_students;
}

function block_exam_actions_remote_categories($identifier, $categoryids) {
    $function = 'local_exam_remote_get_categories';
    $params = array('categoryids' => $categoryids);
    $rcats = \local_exam_authorization\authorization::call_remote_function($identifier, $function, $params);
    $remote_categories = array();
    foreach ($rcats AS $rcat) {
        $remote_categories[$rcat->id] = $rcat;
    }
    return $remote_categories;
}

function block_exam_actions_generate_access_key($courseid, $userid, $access_key_timeout=30, $verify_client_host=1) {
    global $DB;

    $chars = "abcdefghijkmnopqrstuvxwz23456789";

    $key_rec = true;
    while($key_rec) {
        $key = '';
        for($i=1; $i <=8; $i++) {
            $r = rand(0, strlen($chars)-1);
            $key .= substr($chars, $r, 1);
        }
        $key_rec = $DB->get_record('exam_access_keys', array('access_key' => $key));
    }

    $acc = new stdClass();
    $acc->access_key = $key;
    $acc->userid     = $userid;
    $acc->courseid   = $courseid;
    $acc->ip         = \local_exam_authorization\authorization::remote_addr();
    $acc->timeout    = $access_key_timeout;
    $acc->verify_client_host = $verify_client_host;
    $acc->timecreated= time();
    $DB->insert_record('exam_access_keys', $acc);

    return $key;
}

function block_exam_actions_courses_menu($userid, $capability) {
    $courses_menu = array();
    $courses = enrol_get_all_users_courses($userid, true, 'id, fullname, visible, startdate', 'fullname');
    $now = time();
    foreach ($courses as $c) {
        $context = context_course::instance($c->id);
        if ($c->visible && $c->startdate <= $now && has_capability($capability, $context)) {
            $courses_menu[$c->id] = $c->fullname;
        }
    }
    return $courses_menu;
}

function block_exam_actions_export_activity($identifier, $shortname, $backup_file) {
    global $USER;

    $function = 'local_exam_remote_restore_activity';
    $params = array(
        'username' => $USER->username,
        'shortname' => $shortname,
        'backup_file' => $backup_file,
        );

    return \local_exam_authorization\authorization::call_remote_function($identifier, $function, $params);
}

function block_exam_actions_sync_groupings_groups_and_members($identifier, $shortname, $course, $remote_courseid,
                $synchronize=false, $remote_groupingids_to_create=array(), $remote_groupids_to_create=array()) {
    global $DB;

    // Create new groupings

    $r_groupings = \local_exam_authorization\authorization::call_remote_function($identifier,
                        'core_group_get_course_groupings', array('courseid'=>$remote_courseid));
    $remote_groupings = array();
    foreach ($r_groupings AS $r_grouping) {
        $r_grouping->localid = groups_get_grouping_by_name($course->id, $r_grouping->name);
        if ($synchronize && !$r_grouping->localid && in_array($r_grouping->id, $remote_groupingids_to_create)) {
            $grouping = new stdClass();
            $grouping->courseid = $course->id;
            $grouping->name     = $r_grouping->name;
            $r_grouping->localid = groups_create_grouping($grouping);
        }
        $remote_groupings[$r_grouping->id] = $r_grouping;
    }

    // Create new groups

    $r_groups = \local_exam_authorization\authorization::call_remote_function($identifier,
                        'core_group_get_course_groups', array('courseid'=>$remote_courseid));
    $remote_groups = array();
    foreach ($r_groups AS $r_group) {
        $r_group->localid = groups_get_group_by_name($course->id, $r_group->name);
        if ($synchronize && !$r_group->localid && in_array($r_group->id, $remote_groupids_to_create)) {
            $group = new stdClass();
            $group->courseid = $course->id;
            $group->name     = $r_group->name;
            $r_group->localid = groups_create_group($group);
        }
        $remote_groups[$r_group->id] = $r_group;
    }

    // Assign groups to groupings

    if (empty($remote_groupings)) {
        $remote_groupings_groups = array();
    } else {
        $params = array('returngroups'=>1, 'groupingids'=>array_keys($remote_groupings));
        $remote_groupings_groups = \local_exam_authorization\authorization::call_remote_function(
                        $identifier, 'core_group_get_groupings', $params);
        if ($synchronize && !empty($remote_groupings)) {
            foreach ($remote_groupings_groups AS $r_grouping) {
                if ($remote_groupings[$r_grouping->id]->localid) {
                    foreach ($r_grouping->groups AS $r_group) {
                        if ($remote_groups[$r_group->id]->localid) {
                            groups_assign_grouping($remote_groupings[$r_grouping->id]->localid,
                                        $remote_groups[$r_group->id]->localid);
                        }
                    }
                }
            }
        }
    }

    // Sync group member

    if ($synchronize && !empty($remote_groups)) {
        $students = block_exam_actions_enrol_students($identifier, $shortname, $course);
        $r_usernames = array();
        foreach($students AS $st) {
            $r_usernames[$st->remote_id] = $st->username;
        }

        $r_groups_members = \local_exam_authorization\authorization::call_remote_function($identifier,
                        'core_group_get_group_members', array('groupids'=>array_keys($remote_groups)));
        foreach ($r_groups_members AS $r_group_members) {
            if ($remote_groups[$r_group_members->groupid]->localid) {
                $groupid = $remote_groups[$r_group_members->groupid]->localid;
                $group_members = groups_get_members($groupid, 'u.id');
                foreach ($r_group_members->userids AS $r_userid) {
                    if (isset($r_usernames[$r_userid])) {
                        if ($userid = $DB->get_field('user', 'id', array('username'=>$r_usernames[$r_userid]))) {
                            if (isset($group_members[$userid])) {
                                unset($group_members[$userid]);
                            } else {
                                groups_add_member($groupid, $userid);
                            }
                        }
                    }
                }
                foreach ($group_members AS $userid=>$gm) {
                    groups_remove_member($groupid, $userid);
                }
            }
        }
    }

    return array($remote_groupings, $remote_groups, $remote_groupings_groups);
}

/**
 * Show user friendly duration since last activity.
 *
 * @param int $duration in seconds
 * @return string
 */
function block_exam_actions_format_duration($duration) {

    // NOTE: The session duration is not accurate thanks to
    //       $CFG->session_update_timemodified_frequency setting.
    //       Also there is no point in showing days here because
    //       the session cleanup should purge all stale sessions
    //       regularly.

    if ($duration < 60) {
        return get_string('now');
    }

    if ($duration < 60 * 60 * 2) {
        $minutes = (int)($duration / 60);
        $ago = $minutes . ' ' . get_string('minutes');
        return get_string('ago', 'core_message', $ago);
    }

    $hours = (int)($duration / (60 * 60));
    $ago = $hours . ' ' . get_string('hours');
    return get_string('ago', 'core_message', $ago);
}

/**
 * Show some user friendly IP address info.
 *
 * @param string $ip
 * @return string
 */
function block_exam_actions_format_ip($ip) {
    if (strpos($ip, ':') !== false) {
        // For now ipv6 is not supported yet.
        return $ip;
    }
    $url = new moodle_url('/iplookup/index.php', array('ip' => $ip));
    return html_writer::link($url, $ip);
}
