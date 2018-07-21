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
 * @package     Mod  
 * @subpackage Facetoface
 * @copyright  2014 Sriharsha <sriharsha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
//define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/local/learningplan/lib.php');
global $DB, $USER, $CFG,$PAGE,$OUTPUT;
$systemcontext = context_system::instance();
require_once($CFG->dirroot . '/mod/facetoface/approvals/includes.php');
$planid = optional_param('plan', 0, PARAM_INT);
if($planid==0){
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/mod/facetoface/dialog.js',true);
$PAGE->requires->css('/mod/facetoface/dialog.css');
$PAGE->requires->css('/local/learningplan/css/jquery.dataTables.css');
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js', true);
}
//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_url("/local/learningplan/approvals/ilt_request.php");
$PAGE->set_title(get_string('requested_courses', 'facetoface'));
$PAGE->set_heading(get_string('requested_courses', 'facetoface'));
$PAGE->navbar->add(get_string('requested_courses', 'facetoface'));
$learningplan_lib = new learningplan();
require_login();
echo $OUTPUT->header();
echo "<h2 class='tmhead2'>".get_string('approval', 'local_learningplan').'</h2>';
$submitted_data =  data_submitted();
$learningplan_lib = new learningplan();
if($submitted_data){
			
    if($submitted_data->action =='approve'){
		
        foreach($submitted_data->f2fapprovalids as $approvalid){
            $facetofaceinfo=$DB->get_record('local_learningplan_approval', array('id'=>$approvalid));
            $facetofaceinfo->approvestatus=1;           
            $facetofaceinfo->approvedby =$USER->id;          
            $facetofaceinfo->timemodified = time();
            $facetofaceinfo->usermodified = $USER->id;
            $DB->update_record('local_learningplan_approval', $facetofaceinfo);
            $create_record = $learningplan_lib->assign_users_to_learningplan($facetofaceinfo);
            echo '<div class="alert alert-success" id ="overlay">
                  <strong>Success!</strong> Indicates a successful or positive action.
                </div>
       ';
            $emailtype="lep_approvaled";
			$status="approved";
			$planid=$DB->get_field('local_learningplan_approval','planid',array('id'=>$approvalid));
			$notification=$learningplan_lib->to_send_request_notification($facetofaceinfo,$emailtype,$status,$planid);/*Function to send the approval notification*/
        }
		/*****To make the array of user id and pass in the notification****/
		$id=implode(',',$submitted_data->f2fapprovalids);
		$sql="select id,userid from {local_learningplan_approval} where id IN($id)";
		$facetofaceinfo_users=$DB->get_records_sql_menu($sql);
		
		if($planid>0){
			
			$notification=$learningplan_lib->notification_for_user_enrol($facetofaceinfo_users,$facetofaceinfo);
			$return= new moodle_url('/local/learningplan/plan_view.php', array('id' => $planid,'condtion'=>'manage'));
			redirect($return);
		}else{
			
		}
    }    
    else if($submitted_data->action =='reject'){
        $approvalid= $submitted_data->fapprovalid;
        $facetofaceinfo=$DB->get_record('local_learningplan_approval', array('id'=>$approvalid));
        $facetofaceinfo->approvestatus=2;           
        $facetofaceinfo->approvedby =$USER->id;          
        $facetofaceinfo->timemodified = time();
        $facetofaceinfo->usermodified = $USER->id;
        $facetofaceinfo->reject_msg =$submitted_data->text;
        //print_object($facetofaceinfo);exit;
        $DB->update_record('local_learningplan_approval', $facetofaceinfo);
        $id=$DB->delete_records('local_learningplan_user',array('planid' => $facetofaceinfo->planid, 'userid' => $facetofaceinfo->userid));
		$DB->delete_records('local_learningplan_user', array('id' => $id, 'planid' => $data->planid, 'userid' => $data->userid));
        echo '
        <div class="alert alert-success" id ="overlay">
  <strong>Success!</strong> Indicates a successful or positive action.
</div>
       ';
      
    }
    if($planid>0){
		$emailtype="lep_rejected";
		$status="rejected";
		$notification=$learningplan_lib->to_send_request_notification($facetofaceinfo,$emailtype,$status,$planid);
			$return= new moodle_url('/local/learningplan/plan_view.php', array('id' => $planid,'condtion'=>'manage'));
			redirect($return);
		}else{
			
		}
} // end of submitted data





$table = new html_table();
$head = array('Learning Plan Name','Requested Users','Approved Users','Reject Users');
$table->head = $head;
$table->id = 'publishedexams';    
$out =  html_writer::table($table);
echo $out;

require_once($CFG->dirroot . '/local/learningplan/approvals/lep_custom.php');
echo $OUTPUT->footer();
  