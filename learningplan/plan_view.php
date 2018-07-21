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
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

$id = optional_param('id', 0, PARAM_INT);
$curr_tab = optional_param('tab', 'courses', PARAM_TEXT);
$condition = optional_param('condtion','view', PARAM_TEXT);
$enrol=optional_param('enrolid', 0, PARAM_INT);
$course_enrol=optional_param('courseid', 0, PARAM_INT);
$cehckingid=optional_param('couid', 0, PARAM_INT);
$userid=optional_param('userid', 0, PARAM_INT);
$planid=optional_param('planid', 0, PARAM_INT);
$systemcontext = context_system::instance();
//check the context level of the user and check whether the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/facetoface/dialog.js',true);
$PAGE->requires->css('/mod/facetoface/dialog.css');
$PAGE->requires->css('/local/learningplan/css/select2.min.css');
$PAGE->requires->js('/local/learningplan/js/select2.min.js', true);
$PAGE->requires->css('/local/learningplan/css/jquery.dataTables.css');
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js', true);
$PAGE->requires->js('/local/learningplan/js/custom.js');
$PAGE->requires->js('/local/learningplan/js/ajax.js');
$PAGE->requires->js('/local/learningplan/js/delete_custom.js');
$PAGE->requires->js('/local/learningplan/js/unassign_courses_confirm.js');
$PAGE->requires->js('/local/learningplan/js/unassign_users_confirm.js');
//$PAGE->requires->js('/mod/facetoface/js/modal_popup.js');
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js');
$PAGE->requires->js('/local/costcenter/js/enrollfilter.js',true);//added by shriram

$PAGE->set_url('/local/learningplan/plan_view.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'local_learningplan'));
$PAGE->set_pagelayout('admin');
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_learningplan'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add( get_string('pluginname', 'local_learningplan'), new moodle_url('/local/learningplan/index.php'));

$learningplan_renderer = $PAGE->get_renderer('local_learningplan');
$learningplan_lib = new learningplan();
$return_url = new moodle_url('/local/learningplan/plan_view.php',array('id'=>$id));
echo $OUTPUT->header();

if($id <= 0){
    echo "<h2 class='tmhead2'>".get_string('pluginname', 'local_learningplan').'</h2>';
    print_error('invalid_learningplan_id', 'local_learningplan');
}
/** Assign Users to learning plan code **/
if($enrol>0){ /***condition to assign or enrol users to LEP***/
   
    $data = new stdClass();
    $data->planid = $enrol;
    $data->userid = $USER->id;
    $data->timecreated = time();
    $data->usercreated = $USER->id;
    $data->timemodified = 0;
    $data->usermodified = 0;
   // print_object($data);exit;
    $create_record = $learningplan_lib->assign_users_to_learningplan($data);/*Function to assign users*/
    /********Function to Send Notification after enrol by user*****************/
    $users=array();
    $users[]=$USER->id;
    $notification=$learningplan_lib->notification_for_user_enrol($users,$data);
    /**********************End of the Function*****************/
    $return_url = new moodle_url('/local/learningplan/view.php',array('id'=>$id));
    redirect($return_url);
}
/***End of code commented by Ravi_369***/

$plan_record = $DB->get_record('local_learningplan', array('id' => $id));
echo "<h2 class='tmhead2'>".$plan_record->name.'</h2>';
/*Enrol Button*/

/***********The query Check Whether user enrolled to LEP or NOT**********/
$sql="select id from {local_learningplan_user} where planid=$id and userid=".$USER->id."";
$check=$DB->get_record_sql($sql);
/*End of Query*/

/*******The Below query is check the approval status for the LOGIN USERS on the his LEP************/
$check_approvalstatus=$DB->get_record('local_learningplan_approval',array('planid'=>$plan_record->id,'userid'=>$USER->id));
 
/*End of Query*/

if($check){ /****condition to check user already enrolled to the LEP If Enroled he get option enrolled ********/

        if($check_approvalstatus->approvestatus==1){
        $back_url = "#";
        echo html_writer::link($back_url, 'Enrolled to Plan', array('class' => 'pull-right enrol_to_plan'));
        }else{
        $back_url ="#";
        echo html_writer::link($back_url, 'Already Enrolled', array('class' => 'pull-right already_enrolled_plan nourl'));
        }
}else{/****Else he has 4 option like the Send Request or Waiting or Rejected or Enroled****/
       
        if(!is_siteadmin()){
            if($condition!='manage'){ /*******condition to check the manage page or browse page******/
            
                if($plan_record->approvalreqd==1  && (!empty($check_approvalstatus))) /***** If user has LEP with approve with 1 means request yes and
                                                                                        empty not check approval status means he has sent request******/
                {
                
                    $learningplan_lib = new learningplan;
                    $check_users= $learningplan_lib->check_courses_assigned_target_audience($USER->id,$plan_record->id);
                    /****The above Function is to check the user is present in the target audience or not***/
                    
                    if($check_users==1){/*if there then he will be shown the options*/
                    
                    $check_approvalstatus=$DB->get_record('local_learningplan_approval',array('planid'=>$plan_record->id,'userid'=>$USER->id));
                    
                    if($check_approvalstatus->approvestatus==0 && !empty($check_approvalstatus)){
                    $back_url = "#";
                    echo html_writer::link($back_url, 'Waiting', array('class' => 'pull-right actions nourl'));  
                    }elseif($check_approvalstatus->approvestatus==2 && !empty($check_approvalstatus)){
                    $back_url = "#";
                    echo html_writer::link($back_url, 'Rejected',array('class' => 'pull-right actions','title'=>'Your Request has been Rejected contact supervisor'));
                    }    
                    if(empty($check_approvalstatus)){
                    $back_url = new moodle_url('/local/learningplan/plan_view.php',array('id'=>$id,'enrolid'=>$id));
                    echo html_writer::link($back_url, 'Enroll to Plan', array('class' => 'pull-right enrol_to_plan ','id'=>'enroll'));
                    $notify = new stdClass();
                    $notify->name = $plan_record->name;
                    $PAGE->requires->event_handler("#enroll",
					'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('enroll_notify','local_learningplan',$notify),
															'callbackargs' => array('confirmdelete' =>$plan_record->id)));
                    }
                    }
                }else if(($plan_record->approvalreqd==1) && (empty($check_approvalstatus))){
                
                    $check_users= $learningplan_lib->check_courses_assigned_target_audience($USER->id,$plan_record->id);
                    
                    if($check_users==1){   
                    echo  html_writer::link(new moodle_url('/local/learningplan/index.php', array('approval' => $plan_record->id)),
                    'Send Request', array('class' => 'pull-right enrol_to_plan nourl','id'=>'request'));
                    $notify_info = new stdClass();
                    $notify_info->name = $plan_record->name;
                    $PAGE->requires->event_handler("#request",
					'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('delete_notify','local_learningplan',$notify_info),
															'callbackargs' => array('confirmdelete' =>$plan_record->id)));
                    
                    }
                }else if($plan_record->approvalreqd==0  && (empty($check_approvalstatus))){
                    $back_url = new moodle_url('/local/learningplan/plan_view.php',array('id'=>$id,'enrolid'=>$id));
                    echo html_writer::link($back_url, 'Enroll to Plan', array('class' => 'pull-right enrol_to_plan ','id'=>'enroll'));
                    $notify = new stdClass();
                    $notify->name = $plan_record->name;
                    $PAGE->requires->event_handler("#enroll",
					'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('enroll_notify','local_learningplan',$notify),
															'callbackargs' => array('confirmdelete' =>$plan_record->id)));
                }
            }
        }
}/** End of condtion **/


$learning_plan_assigned = $DB->record_exists('local_learningplan_user', array('planid' => $id, 'userid' => $USER->id));

/*view of the single plan information in the plan view*/
echo $learningplan_renderer->single_plan_view($id);

/*view of the single plan information in the plan view*/
//echo $learningplan_renderer->plan_overview($id);

if(is_siteadmin() || has_capability('local/assign_multiple_departments:manage', $systemcontext)){ /**Condition for to whom the tab should display**/
    /*view of the tabs*/
    echo $learningplan_renderer->plan_tabview($id, $curr_tab,$condition);  
}else{ 
    
    if($condition=='manage'){ /*condition to check the to browse page or manage page*/
        /*view of the tabs*/    
        echo $learningplan_renderer->plan_tabview($id, $curr_tab,$condition);
    }
        /*view of lep courses*/
   // if(!is_siteadmin() && !has_capability('local/assign_multiple_departments:manage', $systemcontext) && !has_capability('local/learningplan:create',$systemcontext) && !has_capability('local/learningplan:manage',$systemcontext)){
   
    if($condition!='manage' && (empty($cehckingid))){
    
    echo $learningplan_renderer->assigned_learningplans_courses_employee_view($id, $USER->id,$condition);
    }
    if($cehckingid){
        $check_approvalstatus=$DB->get_record('local_learningplan_approval',array('planid'=>$plan_record->id,'userid'=>$USER->id));
         
       if(($plan_record->approvalreqd==1 && $check_approvalstatus->approvestatus==1) || ($plan_record->approvalreqd!=1 && empty($check_approvalstatus))){   
        $condition='';
        echo $learningplan_renderer->assigned_learningplans_courses_browse_employee_view($id, $USER->id,$condition);
    }  
    } 
   // }
}/*end of the condition*/
echo "
<script>
$('#request').on('click', function() {

    $('#request').disable();
});
</script>
";
/*Done and Commented By Ravi_369 */
echo $OUTPUT->footer();
?>
<script>
courseenrolfilter(<?php echo $id ?>,'lp');
</script>