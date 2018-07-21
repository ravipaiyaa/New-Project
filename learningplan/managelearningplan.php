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
 * @subpackage learningplan
 * @copyright  2016 Syed HameedUllah <hameed@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $USER, $CFG,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/local/learningplan/lib.php');
require_once($CFG->dirroot . '/local/learningplan/learning_plan_form.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->css('/local/learningplan/css/jquery.dataTables.css');
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js', true);
$PAGE->requires->js('/mod/facetoface/js/select2.full.js', true);
$PAGE->requires->css('/mod/facetoface/css/select2.min.css');
//$PAGE->requires->js('/local/users/js/custom.js');
$PAGE->requires->js('/local/learningplan/js/delete.js');
$PAGE->requires->js('/local/learningplan/js/delete_confirm.js');
$PAGE->requires->js('/local/learningplan/js/ajax.js');
//$PAGE->requires->js('/local/learningplan/js/custom.js');

$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$visible = optional_param('visible', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$approval = optional_param('approval', 0, PARAM_INT);
$costcenterid = optional_param('costcenterid', 1, PARAM_INT);
$dept = optional_param('department', 0, PARAM_INT);
$subdept = optional_param('subdepartment', 0, PARAM_INT);
$sub_sub_dept = optional_param('sub_sub_department', 0, PARAM_INT);

$return_url = new moodle_url('/local/learningplan/managelearningplan.php');
$systemcontext = context_system::instance();
//check the context level of the user and check whether the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/learningplan/managelearningplan.php');
$PAGE->set_title(get_string('pluginname', 'local_learningplan'));
$PAGE->set_pagelayout('admin');
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_learningplan'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add( get_string('pluginname', 'local_learningplan'), new moodle_url('/local/learningplan/managelearningplan.php'));
$PAGE->navbar->add( get_string('manage_lep', 'local_learningplan'));

//$sql = "SELECT * FROM {user} u, {local_userdata} ud WHERE ud.userid = u.id AND u.id = {$userid}";
if (is_siteadmin()) {
    //$sql = "SELECT * FROM {user} WHERE id = {$userid}";
}

$learningplan_renderer = $PAGE->get_renderer('local_learningplan');
$learningplan_lib = new learningplan();


echo $OUTPUT->header();
echo "<h2 class='tmhead2'>".get_string('manage_lep', 'local_learningplan').'</h2>';
$form = new learningplan_form(null,array('id'=>$id,'costcenterid'=> $costcenterid,'department'=>$dept,'subdepartment'=>$subdept,'sub_sub_department'=>$sub_sub_dept));
if($form->get_data()){
   
}
if($form->is_cancelled()){
    
    $return_url = new moodle_url('/local/learningplan/managelearningplan.php');
        redirect($return_url);
}

if($id > 0){
    $toform = $DB->get_record('local_learningplan', array('id' => $id));
    $description = array('text' => $toform->description, 'format' => 1);
    $objective = array('text' => $toform->objective, 'format' => 1);
    $toform->description = $description;
    $toform->objective = $objective;
    
    $startdate_day = date('j', $toform->startdate);
    $startdate_month = date('m', $toform->startdate);
    $startdate_year = date('Y', $toform->startdate);
    $startdate = array('month' => $startdate_month, 'day'=> $startdate_day, 'year' => $startdate_year);
    
    $enddate_day = date('j', $toform->enddate);
    $enddate_month = date('m', $toform->enddate);
    $enddate_year = date('Y', $toform->enddate);
    $enddate = array('month' => $enddate_month, 'day'=> $enddate_day, 'year' => $enddate_year);
    
    $toform->startdate = $startdate;
    $toform->enddate = $enddate;
    $form->set_data($toform);
}

if($id > 0 || $id != null || ($form->is_submitted() && !$form->is_validated())){
    $collapse = false;
}else{
    $collapse = true;
}

if($id > 0 && $delete == 1 && $confirm == 1 && confirm_sesskey()){
    $learningplan_lib->delete_learning_plan($id);
    redirect($return_url);
}
if($approval>0){
    $changestatus = new stdClass();
    $changestatus->id = $approval;
    $changestatus->approvalreqd = 2;
    $changestatus->timemodified = time();
    $changestatus->usermodified = $USER->id;
    $confirm = $DB->update_record('local_learningplan',$changestatus);
  redirect($return_url);
}
$fromform = data_submitted();
if($fromform){
    
    
    $record_check = $DB->record_exists('local_learningplan', array('shortname' => $fromform->shortname));
    
    if(!$record_check || $fromform->enddate<$fromform->startdate){
       // print_object($fromform);
        $startdate_day = $fromform->startdate['day'];
        $startdate_month = $fromform->startdate['month'];
        $startdate_year = $fromform->startdate['year'];
        $enddate_day = $fromform->enddate['day'];
        $enddate_month = $fromform->enddate['month'];
        $enddate_year = $fromform->enddate['year'];
        $startdate_check = mktime(0, 0, 0, $startdate_month, $startdate_day, $startdate_year);
        $enddate_check = mktime(0, 0, 0, $enddate_month, $enddate_day, $enddate_year);
        
        if($startdate_check <= $enddate_check){
            
            if ($fromform->submitbutton == 'Save changes') {
               
                if($fromform->id > 0){
                    //Update Plan
                  
                    // $costcenterlist=implode(',',$fromform->costcenterid);
                    //if(!$costcenterlist){
                    //    $costcenterlist=$fromform->costcenterid;
                    //}
                      if(is_array($fromform->costcenter))  {
                                $systemcontext = context_system::instance();
                                if(!is_siteadmin()  && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
                                 $costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
                                 $inserted_costcenter=implode(',',$fromform->costcenter);
                                 $costcenterlist=$costcenter.','.$inserted_costcenter;
                                
                                }
                       
                       
                        
                        //$costcenterlist=implode(',',$fromform->costcenter);
                    }else{
                        
                       $systemcontext = context_system::instance();
                        if(!is_siteadmin()  && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
                                
                                 $sql="select id,id as idd from {local_costcenter} where parentid=0";
                                 $allcostcenter=$DB->get_records_sql_menu($sql);
                                 $costcenter=implode(',',$allcostcenter);
                                 //$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
                                 $costcenterlist=$costcenter;
                            
                        }else{
                       
                                 $costcenterlist = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
                        }
                       
                    }
                    //$costcenterlist=implode(',',$fromform->costcenterid);
                    $deptlist=implode(',',$fromform->department);
                    $subdeptlist=implode(',',$fromform->subdepartment);
                    $subsubdeptlist=implode(',',$fromform->subsubdepartment);
                    $bandlist=implode(',',$fromform->bands);
                    $record = new stdClass();
                    $record->id = $fromform->id;
                    $record->costcenter = $costcenterlist;
                    $record->department = $deptlist;
                    $record->subdepartment = $subdeptlist;
                    $record->subsubdepartment =  $subsubdeptlist;
                    $record->name = $fromform->name;
                    $record->band=$bandlist;
                     $record->approvalreqd = $fromform->needapproval;
                    //$record->shortname = $fromform->shortname;
                    $record->learning_type = $fromform->learning_type;
                    $record->visible = $fromform->visible;
                    $record->credits = $fromform->credits;
                    $record->location = $fromform->location;
                    $record->description = $fromform->description['text'];
                    
                    $startdate_day = $fromform->startdate['day'];
                    $startdate_month = $fromform->startdate['month'];
                    $startdate_year = $fromform->startdate['year'];
                    
                    $enddate_day = $fromform->enddate['day'];
                    $enddate_month = $fromform->enddate['month'];
                    $enddate_year = $fromform->enddate['year'];
                    
                    $startdate = mktime(0, 0, 0, $startdate_month, $startdate_day, $startdate_year);
                    $enddate = mktime(0, 0, 0, $enddate_month, $enddate_day, $enddate_year);
                    $record->startdate = $startdate;
                    $record->enddate = $enddate;
                    $record->objective = $fromform->objective['text'];
                    $record->summaryfile = $fromform->summaryfile;
                    //Updating of learning plans
                    $systemcontext = context_system::instance();
                    file_save_draft_area_files($fromform->summaryfile, $systemcontext->id, 'local', 'learningplan', $fromform->summaryfile, array('maxfiles' => 5));
                   
                    $learningplan_lib->update_learning_plan($record);
                    redirect($return_url);
                }else{
                    //Insert Plan
                  
                      if(is_array($fromform->costcenter))  {
                                $systemcontext = context_system::instance();
                                if(!is_siteadmin()  && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
                                 $costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
                                 $inserted_costcenter=implode(',',$fromform->costcenter);
                                 $costcenterlist=$costcenter.','.$inserted_costcenter;
                                
                                }
                       
                       
                        
                        //$costcenterlist=implode(',',$fromform->costcenter);
                    }else{
                        
                       $systemcontext = context_system::instance();
                        if(!is_siteadmin()  && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
                                
                               $sql="select id,id as idd from {local_costcenter} where parentid=0";
                                 $allcostcenter=$DB->get_records_sql_menu($sql);
                                 $costcenter=implode(',',$allcostcenter);
                                 //$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
                                 $costcenterlist=$costcenter;
                            
                        }else{
                       
                                 $costcenterlist = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
                        }
                       
                    }
                 
                    //$costcenterlist=implode(',',$fromform->costcenterid);
                    $deptlist=implode(',',$fromform->department);
                    $subdeptlist=implode(',',$fromform->subdepartment);
                    $subsubdeptlist=implode(',',$fromform->subsubdepartment);
                    $bandlist=implode(',',$fromform->bands);
                    $record = new stdClass();
                    $record->costcenter = $costcenterlist;
                    $record->department = $deptlist;
                    $record->subdepartment = $subdeptlist;
                    $record->subsubdepartment =  $subsubdeptlist;
                    $record->name = $fromform->name;
                    $record->approvalreqd = $fromform->needapproval;
                    $record->band=$bandlist;
                    $record->shortname = $fromform->shortname;
                    $record->learning_type = $fromform->learning_type;
                    $record->visible = $fromform->visible;
                    $record->credits = $fromform->credits;
                    $record->location = $fromform->location;
                    $record->description = $fromform->description['text'];
                    
                    $startdate_day = $fromform->startdate['day'];
                    $startdate_month = $fromform->startdate['month'];
                    $startdate_year = $fromform->startdate['year'];
                    
                    $enddate_day = $fromform->enddate['day'];
                    $enddate_month = $fromform->enddate['month'];
                    $enddate_year = $fromform->enddate['year'];
                    
                    $startdate = mktime(0, 0, 0, $startdate_month, $startdate_day, $startdate_year);
                    $enddate = mktime(0, 0, 0, $enddate_month, $enddate_day, $enddate_year);
                    
                    $record->startdate = $startdate;
                    $record->enddate = $enddate;
                    
                    $record->objective = $fromform->objective['text'];
                    $record->summaryfile = $fromform->summaryfile;
                    
                    $systemcontext = context_system::instance();
                    file_save_draft_area_files($fromform->summaryfile, $systemcontext->id, 'local', 'learningplan',$fromform->summaryfile, array('maxfiles' => 5));
                    //create learning plans
                    $learningplan_lib->create_learning_plan($record);
                    redirect($return_url);
                }
            }
        }
    }else{
           
           $collapse = false;
           //echo $collapse;exit;
    }
}
if($visible > 0){
    $record = $DB->get_field('local_learningplan', 'visible', array('id' => $visible));
    $visible_data = new stdClass();
    $visible_data->id = $visible;
    if($record == 0){
        $visible_data->visible = 1;
    }elseif($record == 1){
        $visible_data->visible = 0;
    }
    $update_record = $learningplan_lib->update_learning_plan($visible_data);
    redirect($return_url);
}

if (has_capability('local/learningplan:create', $systemcontext) || has_capability('local/learningplan:update', $systemcontext)) {
    $add_learningplan = get_string('add_learningplan', 'local_learningplan');
    print_collapsible_region_start('', 'learningplan-form', $add_learningplan, false, $collapse);
    $form->display();
    print_collapsible_region_end();
}

//if(is_siteadmin()){
        /*HERE SENDING THE PARAMETER TO VIEW THE MANAGE PAGE WITH ALL TABS By Ravi_369*/
    $condition="manage";
    echo $learningplan_renderer->all_learningplans($condition);
//}

echo $OUTPUT->footer();
