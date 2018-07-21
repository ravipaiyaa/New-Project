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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Cost center
 * @copyright  2015 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

require_once($CFG->dirroot . '/local/costcenter/costcenter_form.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$userid = optional_param('userid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INT);
$moveto = optional_param('moveto', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$costcenterids = optional_param('id', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);

$conf = new object();

require_login();
$costcenter = new costcenter();


if ($id > 0) {
    if (!($costcenter_instance = $DB->get_record('local_costcenter', array('id' => $id)))) {
        print_error('invalidtoolid1122', 'local_costcenter');
    }
} else {
    $costcenter_instance = new stdClass();
    $costcenter_instance->id = -1;
}


$PAGE->set_url('/local/costcenter/costcenter.php');
$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);

if (!has_capability('local/costcenter:manage', $systemcontext)) {
    print_error('nopermission');
}

$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);

$returnurl = new moodle_url('/local/costcenter/index.php');

$strheading = get_string('pluginname', 'local_costcenter');

/* ---Start of delete the costcenter--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $costcenter->costcenter_delete_instance($id);
        redirect($returnurl);
    }
    $strheading = get_string('deletecostcenter', 'local_costcenter');
    $PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), "/local/costcenter/index.php", get_string('viewcostcenter', 'local_costcenter'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $checkchilditems = $DB->get_records('local_costcenter', array('parentid' => $id));

    /* ---Check if any conditions are satisfied--- */
    if ($checkchilditems) {
        $yesurl = new moodle_url('/local/costcenter/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('cannotdeletecostcenter', 'local_costcenter', array('scname' => $costcenter_instance->fullname));
        echo $message;
        echo $OUTPUT->continue_button(new moodle_url('/local/costcenter/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey())));
    } else {
        $yesurl = new moodle_url('/local/costcenter/costcenter.php?id=' . $id . '', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_costcenter');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* ---End of delete the Cost center--- */

/* ---Start of hide or display the Cost center--- */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    $message_object = new stdClass();
    if (!empty($hide) && empty($show)) {
        $disabled = 0;
    } else {
        $disabled = 1;
    }

    //If it ia the parent for other scholls, dont allow to hide it
    //$parent = $DB->record_exists('local_costcenter', array('parentid' => $id));
    //
    //if ($parent) {
    //    $message = get_string('failure', 'local_costcenter');
    //    $style = array('style' => 'notifyproblem');
    //} else {
        $DB->set_field('local_costcenter', 'visible', $disabled, array('id' => $id));
        $message_object->costcenter = $DB->get_field('local_costcenter', 'fullname', array('id' => $id));
        $message_object->visible = $DB->get_field('local_costcenter', 'visible', array('id' => $id));
        if ($message_object->visible == 1) {
            $message_object->visible = 'Activated';
        } else {
            $message_object->visible = 'Inactivated';
        }
        $message = get_string('success', 'local_costcenter', $message_object);
        $style = array('style' => 'notifysuccess');
    //}
    $costcenter->set_confirmation($message, $returnurl, $style);
}
if ($visible >= 0 && $id && confirm_sesskey()) {

    //If it is parent for other costcenter, dont allow to hide it
    $parent = $DB->record_exists('local_costcenter', array('parentid' => $id));

    if ($parent) {
        $message = get_string('failure', 'local_costcenter');
        $style = array('style' => 'notifyproblem');
    } else {

        $DB->set_field('local_costcenter', 'visible', $visible, array('id' => $id));
        $message_object->costcenter = $DB->get_field('local_costcenter', 'fullname', array('id' => $id));
        $message_object->visible = $DB->get_field('local_costcenter', 'visible', array('id' => $id));
        if ($message_object->visible == 1) {
            $message_object->visible = 'Activated';
        } else {
            $message_object->visible = 'Inactivated';
        }
        $message = get_string('success', 'local_costcenter', $message_object);
        $style = array('style' => 'notifysuccess');
    }
    $costcenter->set_confirmation($message, $returnurl, $style);
}

if ($unassign) {
    $returnurl = new moodle_url('/local/costcenter/index.php');
    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        $costcenter->unassign_users_instance($id, $userid);
    }
}

if (!empty($moveto) and $data = data_submitted()) {
    if (!$destcostcenter = $DB->get_record('local_costcenter', array('id' => $data->moveto))) {
        print_error('cannotfindcostcenter', '', '', $data->moveto);
    }
    $currenturl = "{$CFG->wwwroot}/local/costcenter/index.php";
    if (empty($data)) {
        $costcenter->set_confirmation(get_string('pleaseselectcostcenter', 'local_costcenter'), $currenturl);
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



$heading = ($id > 0) ? get_string('editcostcenter', 'local_costcenter') : get_string('createcostcenter', 'local_costcenter');
$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), new moodle_url('/local/costcenter/index.php', array('id' => $id)));

$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$editform = new costcenter_form(null, array('id' => $id, 'tool' => $costcenter_instance, 'editoroptions' => $editoroptions));


if ($id > 0) {
    $costcenter_instance->description = array('text' => $costcenter_instance->description, 'format' => FORMAT_HTML);
}
$editform->set_data($costcenter_instance);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        $costcenter->costcenter_update_instance($data->id, $data);
    } else {
        $costcenter->costcenter_add_instance($data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();

//if ($id < 0)
//    $currenttab = 'create';
//else
//    $currenttab = 'edit';

echo $OUTPUT->heading(get_string('pluginname', 'local_costcenter'));

//$costcenter->print_costcentertabs($currenttab, $id);

if ($id < 0)
    echo $OUTPUT->box(get_string('addcostcentertabdes', 'local_costcenter'));
else
    echo $OUTPUT->box(get_string('editcostcentertabdes', 'local_costcenter'));

echo $OUTPUT->footer();