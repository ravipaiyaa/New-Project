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
 *
 * @package    costcenter
 * @subpackage assign managers
 * @copyright  2013 Naveen {naveen@eabyas.in}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$moveto = optional_param('moveto', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$userid = optional_param('userid', 0, PARAM_INT);
$costcenterids = optional_param('id', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

global $DB, $CFG;
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_login();
$systemcontext = context_system::instance();

if (!has_capability('local/costcenter:manage', $systemcontext)) {
    print_error('nopermission');
}

$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('assignmanager_title', 'local_costcenter'));
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), "/local/costcenter/index.php", get_string('viewusers', 'local_costcenter'));
$PAGE->navbar->add(get_string('assignusers', 'local_costcenter'));
$PAGE->set_url(new moodle_url('/local/costcenter/assignusers.php', array('id' => $costcenterids)));
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->js('/local/costcenter/js/costcenter.js');
$PAGE->requires->js('/local/costcenter/js/delete_confirm.js');

$output = $PAGE->get_renderer('local_costcenter');

$returnurl = new moodle_url('/local/costcenter/assignusers.php', array('id' => $costcenterids));

$costcenter = new costcenter();
/* ---function for unassigning the users from costcenter--- */
if ($unassign) {
    $returnurl = new moodle_url('/local/costcenter/assignusers.php');

    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        $costcenter->unassign_users_instance($id, $userid);
         //echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }

    //$strheading = get_string('unassingheading', 'local_costcenter');
    //$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), "/local/costcenter/index.php", get_string('viewcostcenter', 'local_costcenter'));
    //$PAGE->navbar->add($strheading);
    //$PAGE->set_title($strheading);
    //
    //echo $OUTPUT->header();
    //echo $OUTPUT->heading($strheading);
    //
    //$yesurl = new moodle_url('/local/costcenter/assignusers.php', array('id' => $id, 'userid' => $userid, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    //$message = get_string('unassignmanager', 'local_costcenter');
    //echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    //
    //echo $OUTPUT->footer();
    //die;
}
/* ---End of function unassigning users--- */
if (!empty($moveto) and $data = data_submitted()) {
    if (!$destcostcenter = $DB->get_record('local_costcenter', array('id' => $data->moveto))) {
        print_error('cannotfindcostcenter', '', '', $data->moveto);
    }
    $currenturl = "{$CFG->wwwroot}/local/costcenter/assignusers.php";
    if (empty($data)) {
        $hierarche->set_confirmation(get_string('pleaseselectcostcenter', 'local_costcenter'), $currenturl);
    }
    $users = array();
    foreach ($data as $key => $value) {
        if (preg_match('/^c\d+$/', $key)) {
            $userid = substr($key, 1);
            array_push($users, $userid);
        }
    }

    $costcenter->add_users($users, $data->moveto);
}
echo $output->header();
echo $output->heading(get_string('pluginname', 'local_costcenter'));

$currenttab = 'assignmanager';
$costcenter->print_costcentertabs($currenttab, $id = NULL);

//Exception handling for empty costcenters
$costcenterlist = $DB->get_records('local_costcenter');
if (empty($costcenterlist)) {
    print_error('costcenternotcreated', 'local_costcenter', $CFG->wwwroot . '/local/costcenter/costcenter.php');
}


echo $output->box(get_string('asignmanagertabdes', 'local_costcenter'));
echo $output->costcenter_managers($costcenter);

echo $output->box(get_string('assignmanagertxt', 'local_costcenter'));
echo $output->assign_manager($costcenter, $costcenterids);

echo $output->footer();
