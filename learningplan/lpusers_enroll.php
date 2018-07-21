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
require_once($CFG->dirroot.'/mod/facetoface/department_job_designation.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();

$planid = optional_param('id', 0, PARAM_INT);
$base_url = new moodle_url('/local/learningplan/lpusers_enroll.php');
$PAGE->set_title(get_string("add_remove_users", 'mod_facetoface'));
$PAGE->requires->jquery();
$PAGE->requires->js('/local/learningplan/js/select2.min.js', true);
$PAGE->requires->css('/local/learningplan/css/select2.min.css',true);
$PAGE->requires->js('/local/costcenter/js/enrollfilter.js',true);
$batch_id=$DB->get_record('facetoface',array('id'=>$planid));
$learningplan_renderer = $PAGE->get_renderer('local_learningplan');
$learningplan_lib = new learningplan();

echo $OUTPUT->header();

$actionpage =$CFG->wwwroot.'/local/learningplan/lpusers_enroll.php?id='.$planid;
if ($fromform = data_submitted()) {
	$mform=new department_job_desigantion_form($actionpage,array('data'=>array('edit'=>1,'filterdata'=>$fromform)));
}else{
	$mform=new department_job_desigantion_form($actionpage);
}
$url = new moodle_url('/local/learningplan/lpusers_enroll.php?id='.$planid);
$costcenter = null;
$designation=null;
$empnumber = null;
$email=null;
$band=null;
$department = null;
$sub_subdepartment=null;
$subdepartment=null;
$organization = null;
if($mform->is_cancelled()){
	redirect($url);
}else if($mform->get_data()){
	$fromform= new stdClass();
	if ($fromform = data_submitted()) {
		
		if(!empty($fromform->email) && (is_array($fromform->email))){
			$implodeemails = implode(',', $fromform->email);
			if($implodeemails){
				$filteremails = $DB->get_records_sql("SELECT id,email FROM {user} WHERE id IN ($implodeemails) ");
				foreach($filteremails as $value) {
					$fromformemail[$value->id] = "'".$value->email."'";
				}
				$email = implode(',',$fromformemail);
			}
		}
		
		if(!empty($fromform->band) && (is_array($fromform->band))){
			foreach($fromform->band as $key => $value) {
				$fromformband[$key] = "'".$value."'";
			}
			$band = implode(',',$fromformband);
		}
		
		if(!empty($fromform->department) && (is_array($fromform->department))){
			$department = implode(',', $fromform->department);
		}
			
		if(!empty($fromform->subdepartment) && (is_array($fromform->subdepartment))){
			$subdepartment = implode(',', $fromform->subdepartment);
		}
		
		if(!empty( $fromform->sub_sub_department) && (is_array($fromform->sub_sub_department))){
			$sub_subdepartment = implode(',', $fromform->sub_sub_department);
		}
		
		if(is_siteadmin()){
			if(!empty($fromform->organization) && is_array($fromform->organization)){
				$organization = implode(',',$fromform->organization);
			}
		}
		
		if(!empty($fromform->designation) && (is_array($fromform->designation))){
			$implodedesignations = implode(',', $fromform->designation);
			if($implodedesignations){
				$filterdesignations = $DB->get_records_sql("SELECT id,designation FROM {local_userdata} where id IN ($implodedesignations)");
				foreach($filterdesignations as $value) {
					$fromformdesignation[$value->id] = "'".$value->designation."'";
				}
				$designation = implode(',',$fromformdesignation);
			}
		}
		if(!empty($fromform->empnumber) && (is_array($fromform->empnumber))){
			foreach($fromform->empnumber as $key => $value) {
				$fromformempnumber[$key] = "'".$value."'";
			}
			$empnumber = implode(',',$fromformempnumber);
		}
	}
}
$userdepartment=$DB->get_record('local_userdata',array('userid'=>$USER->id));
//$costcenterfullname=$DB->get_record('local_costcenter',array('id'=>$userdepartment->costcenterid));
$userroleshortname = $DB->get_record_sql("SELECT a.shortname FROM {role} a,{role_assignments} b,{context} c WHERE a.id=b.roleid and b.userid=$USER->id and b.contextid=c.id and c.contextlevel=10");
if((!is_null($costcenter) ||!is_null($designation) || !is_null($organization) || !is_null($empnumber) || !is_null($email) || !is_null($band) || !is_null($department) || !is_null($subdepartment) || !is_null($sub_subdepartment))  && $mform->is_submitted()){
	$collapse =0;
}else{
	$collapse=1;
}

echo "<h2 class='tmhead2'>".get_string("add_remove_users", 'mod_facetoface')."</h2>";

//======
// 01/06/2017code by bunesh for user search filter
$planrecord=$DB->get_record('local_learningplan',array('id'=>$planid));
$lpusers_image_url = $learningplan_lib->get_learningplansummaryfile($planid);
$plan_id_description = $planrecord->description;
if(!empty($plan_id_description)){
    $description_without_tags = strip_tags($plan_id_description);
    if(strlen($description_without_tags) < 340){
        $plan_id_description = '<div class="usersdesc">'.$plan_id_description.'</div>';
    }else{
        $plandescription_formated = '<div class="usersdesc">'.$plan_id_description.'</div>
                                            <a id="show_more" class="show_more_less">More</a>
                                            <a id="show_less" class="show_more_less" style="display: none;">Less</a>';
        $plandescription_formated .= "<script>
                                        $(document).ready(function(){
                                            $('#show_more').on('click', function() {
                                                $('#show_more').hide();
                                                $('.usersdesc').addClass('show_more_description');
                                                $('#show_less').show();
                                            });
                                            $('#show_less').on('click', function() {
                                                $('#show_less').hide();
                                                $('.usersdesc').removeClass('show_more_description');
                                                $('#show_more').show();
                                            });
                                        });
                                    </script>";
        $plan_id_description = $plandescription_formated;
    }
}else{
    $plan_id_description = '<div class="usersdesc"><div class="alert alert-info text-center">No Description Provided</div></div>';
}
$content = '<div class="row" style="padding-top: 10px" >
				<div class="span1"></div>
				<div class="span10">
					<div class="portlet light shadow">
						<div class="portlet-body">
							<div class="row-fluid">
								<div class="span6">
									<img id="image" class="content-classimg" src="'.$lpusers_image_url.'">
								</div>
								<div class="span6" >
									<h3 class="coursetitle" >'.$planrecord->name.'</h3>
									   '.$plan_id_description.'
								</div>
							</div>
								<div class="row" style="padding-top: 10px; margin-top: 20px; border-top: 1px solid #eee" align="left">
							</div>
						</div>
					</div>
				</div>
			</div>';

echo $content;
//bunesh code ends here

if($add_users==0  && !($loggedinuser_supervisor)){
	print_collapsible_region_start('', 'department_job_desigantion-filter', get_string('employeesearch','facetoface'),false,$collapse);
	$mform->display();
	print_collapsible_region_end();
}

echo $learningplan_renderer->learningplans_bulk_users_tab_content($planid,$designation, $department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment);

$url = new moodle_url('/local/learningplan/plan_view.php?id='.$planid.'&condtion=manage&tab=courses');
$continue = html_writer::tag('span', 'Continue', array('class'=>'knowmore'));
echo html_writer::link($url, $continue , array('class' => 'pull-left text-center row-fluid mb-15'));

echo $OUTPUT->footer();
?>
<script>
courseenrolfilter(<?php echo $planid ?>,'lp');
</script>