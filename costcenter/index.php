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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage costcenter
 * @copyright  2015 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $USER, $PAGE;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot . '/local/costcenter/costcenter_form.php');

//$PAGE->requires->css('/local/costcenter/css/style.css');

$id = optional_param('id', 0, PARAM_INT);
$flat = optional_param('flat', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$moveto = optional_param('moveto', 0, PARAM_INT);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$userid = optional_param('userid', 0, PARAM_INT);
$costcenterids = optional_param('id', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$employe_assign = optional_param('eassign',0,PARAM_BOOL);
require_login();

$systemcontext = context_system::instance();
if (!is_siteadmin() && !has_capability('local/costcenter:view', $systemcontext)) {
    redirect($CFG->wwwroot . '/local/error.php?id=1');
}

$costcenter = new costcenter();
if($employe_assign ){
    if($data = data_submitted()){
        foreach($data->u_id as $userid){
        $userdata = new stdClass();
        $userdata->userid = $userid;
        $userdata->costcenterid = $id;
        $userdata->supervisorid = 0;
        $userdata->reportingmanagerid = 0;
        $userdata->usermodified = $USER->id;
        $userdata->timecreated = time();
        $userdata->timemodified = time();
    $DB->insert_record('local_userdata',$userdata);         
        }
        redirect($PAGE->url);
    }
}
 
    //<!--rajesh has written this code--> 
$iconimage='';//html_writer::empty_tag('img', array('src'=>$CFG->wwwroot.'/theme/clean/pix/small/orderAndPaging.png','size'=>'15px'));
  //<!--code end here-->       
if ($id > 0) {
    if(has_capability('local/costcenter:create', $systemcontext)){
        $create_new = "<span class=''>".get_string('createcostcenter', 'local_costcenter')."</span>";
    }else{
        $create_new = "";
    }  
    //$form_header =  "<h2 class='tmhead2' id='local_costcenter_heading'><span class='iconimage'>".$iconimage."</span>".get_string('pluginname', 'local_costcenter')."</h2>".$create_new." ";
    $page_header = "<h2 class='tmhead2' id='local_costcenter_heading'>".get_string('pluginname', 'local_costcenter')."</h2>";
    //$form_header = "<h2 class='tmhead2' id='local_costcenter_heading'>".get_string('pluginname', 'local_costcenter')."</h2><span class='new_create'>".get_string('editcostcenter', 'local_costcenter')."</span>";
    $collapse = false;
    if (!($costcenter_instance = $DB->get_record('local_costcenter', array('id' => $id)))) {
        print_error('invalidtoolid1122', 'local_costcenter');
    }
    $costcenter_instance->description = array('text' => $costcenter_instance->description, 'format' => FORMAT_HTML);
} else {
    if(has_capability('local/costcenter:create', $systemcontext)){
        $create_new = "<span class='newcostcenter'>".get_string('createcostcenter', 'local_costcenter')."</span>";
    }else{
        $create_new = "";
    }
    //$form_header =  "<h2 class='tmhead2' id=''><span class='iconimage'>".$iconimage."</span>".get_string('pluginname', 'local_costcenter')."</h2>".$create_new." ";
    $page_header =  "<h2 class='tmhead2' id='local_costcenter_heading'>".get_string('pluginname', 'local_costcenter')."</h2>";
    $collapse = true;
    $costcenter_instance = new stdClass();
    $costcenter_instance->id = -1;
}

if ($unassign) {
    $returnurl = new moodle_url('/local/costcenter/assignusers.php');
    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        $costcenter->unassign_users_instance($id, $userid);
    }
}

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

$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/costcenter/index.php');
$PAGE->set_heading(get_string('pluginname', 'local_costcenter'));
$PAGE->set_title(get_string('pluginname', 'local_costcenter'));
$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), new moodle_url('/local/costcenter/index.php'));
$PAGE->navbar->add(get_string('viewcostcenter', 'local_costcenter'));
$PAGE->requires->jquery();

$output = $PAGE->get_renderer('local_costcenter');

echo $output->header();
//echo "<h2 class='tmhead2' id='local_costcenter_heading'>".get_string('pluginname', 'local_costcenter')."</h2>";
echo $page_header;
//$costcenter->print_costcentertabs('view', $id = NULL);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$editform = new costcenter_form(null, array('id' => $id, 'tool' => $costcenter_instance, 'editoroptions' => $editoroptions));

$editform->set_data($costcenter_instance);

$costcenterlist = $DB->get_records('local_costcenter');
if ((empty($costcenterlist)and has_capability('local/costcenter:manage', $systemcontext)) || ($editform->is_submitted() && !$editform->is_validated())) {
    $collapse = false;
//    print_error('costcenternotcreated', 'local_costcenter', $CFG->wwwroot . '/local/costcenter/costcenter.php');
}
//if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding and has_capability('local/costcenter:manage', $systemcontext)) {
//    echo $output->box(get_string('allowframembedding', 'local_costcenter'));
//}
if ($editform->is_cancelled()) {
    redirect($PAGE->url);
} else if ($data = $editform->get_data()) {
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        $costcenter->costcenter_update_instance($data->id, $data);
    } else {
        $costcenter->costcenter_add_instance($data);
        if($data->parentid == 0){
            $costcenter->add_questioncategory('0,1', $data->shortname, '');
        }
    }
    redirect($PAGE->url);
}

if(has_capability('local/costcenter:create', $systemcontext)){
    print_collapsible_region_start('', 'costcenter-form', $create_new, false, $collapse);
    echo "<div class = 'costcenter_class'>";
        $editform->display();
    echo "</div>";
    print_collapsible_region_end();
}

echo $output->departments_view();
//echo html_writer::script(' $(document).ready(function() {
//                        $("#department-index").dataTable({
//                        searching: true,
//                        responsive: true,
//                         "aaSorting": [],
//                         "lengthMenu": [[5, 10, 25,50,100, -1], [5,10,25, 50,100, "All"]],
//                        "aoColumnDefs": [{ \'bSortable\': false, \'aTargets\': [ 0 ] }],
//                        language: {
//                            search: "_INPUT_",
//                            searchPlaceholder: "Search"
//                        }
//                        });
//                        });');

echo $output->footer();