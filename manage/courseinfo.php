<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/blocks/manage/lib.php');
global $CFG,$USER, $DB, $PAGE, $OUTPUT;
require_login();
$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/manage/js/jquery-ui.js', true);
$PAGE->requires->css('/blocks/manage/css/jquery-ui.css');
$PAGE->requires->js('/blocks/manage/js/tabs_script.js');
//$PAGE->requires->js('/blocks/manage/js/moodle_confirmation.js');

require_once $CFG->libdir.'/gradelib.php';
require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/grade/report/user/lib.php';
require_once($CFG->dirroot .'/local/mycourses/lib.php');
require_once($CFG->dirroot .'/local/includes.php');

$id  = required_param('id', PARAM_INT); // Course id



/**The Below code is for when user click on the course view url he is to login as high role so the code written by niranjan**/
	    $systemcontext = context_system::instance();
	
		$roles = get_user_roles($systemcontext, $USER->id);
		//print_object($roles);
		if(empty($USER->access['rsw']) && (count($roles) >= 1) && !is_siteadmin($USER->id)){
		$singlerole = $roles[key($roles)];
		$rolerecord = $DB->get_record('role', array('id'=>5));
		if(role_switch($rolerecord->id, $systemcontext)){
		// purge_all_caches();
		//redirect('index.php');
		redirect('courseinfo.php?id='.$id.'');
		}
		}
	/*end of the code*/




//$systemcontext = get_context_instance(CONTEXT_SYSTEM);
$coursecontext = get_context_instance(CONTEXT_COURSE, $id);
$PAGE->set_context($coursecontext);
$PAGE->set_url('/blocks/manage/courseinfo.php?id='.$id.'');

require_once($CFG->dirroot.'/local/includes.php');
	$userlist=new has_user_permission();
	
	/*****if course belongs to accademy the should not check  have permission*******/
	
	$haveaccess=$userlist->access_courses_permission($id);
	
	if(!$haveaccess) {
		 redirect($CFG->wwwroot . '/local/error.php?id=2');
	}

//get the required layout
if(is_siteadmin() || has_capability('moodle/course:update', $coursecontext) || has_capability('moodle/course:create', $coursecontext)){
	$PAGE->set_pagelayout('course');
}else{
	$PAGE->set_pagelayout('changed_course_view');
}

$mycourse = new mycourses();
$course = $DB->get_record('course', array('id'=>$id));
if(!$course){
	print_error('invalidcourseid');
}
$PAGE->set_course($course);
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->requires->css('/blocks/manage/css/coursedetails.css');

//if($course->format == 'tabtopics'){
	$PAGE->requires->js('/blocks/manage/js/course_module.js');
	$PAGE->requires->js_init_call('M.custom_tabtopics.init', array($course->id, $isZeroTab), false, NULL);
//}
//$authorintro="Overview Cobalt Course";

$PAGE->navbar->add($course->fullname);
echo $OUTPUT->header();

$includes = new user_course_details;
$url = $includes->course_summary_files($course);
$summ = '<img src="'.$url.'" alt="'.$course->fullname.'" title="'.$course->fullname.'" style="width:100%;"/>';

//code added by anil
$renderer = $PAGE->get_renderer('block_manage');

$course_summary = $course->summary;
if(!empty($course_summary)){
	$description_without_tags = strip_tags($course_summary);
	if(strlen($description_without_tags) < 580){
		$course_summary = '<div class="course_description">'.$course_summary.'</div>';
	}else{
		$course_summary = '<div class="course_description">
								'.$course_summary.'
							</div>
							<a id="show_more" class="show_more_less">More</a>
							<a id="show_less" class="show_more_less" style="display: none;">Less</a>';
	}
}else{
	$course_summary = '<div class="alert alert-info">No Description Provided</div>';
}

echo '<div class="custom_course_top_section row-fluid">';
echo 	'<div class="custom_course_image span5 pull-left desktop-first-column">
			'.$summ.'
		</div>';
echo	'<div class="custom_course_detail span7 pull-left">
			<h3 class="custom_course_name pull-left row-fluid">'.$course->fullname.'</h3>
			<div class="pull-left row-fluid">
					'.$course_summary.'
			</div>
		</div>
	</div>';
$coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$id));	
if(!isloggedin() || (empty($enrolled) && !is_siteadmin())){
		
	$course_credits = !empty($coursedetails->credits) ? $coursedetails->credits : 'N/A';
	$course_completiondays = !empty($coursedetails->coursecompletiondays) ? $coursedetails->coursecompletiondays : 'N/A';
	
	$course_detail_info = "<ul class='crse_extradetails'>
							<li>
								<span class='mr-20'>
									<i class='fa fa-star' title=".get_string('credits', 'block_manage')." ></i>
									<span>".$course_credits."</span>
								</span>
							</li>
							<li>
								<span>
									<i class='fa fa-clock-o' title=".get_string('daystocomplete', 'block_manage')." ></i>
									<span>".$course_completiondays."</span>
								</span>
							</li>
						</ul>";
	
	$enrol = $DB->get_record('enrol', array('courseid'=>$id, 'enrol'=>'self'));
	//echo '<div style="margin: 3% 0; float: left;font-weight: 500;">Credits: <span class="course_era_cost">' . $credits . '</span></div>';
	//echo '<div class="enrol"><a href="'.$CFG->wwwroot.'/course/view.php?id='.$id.'">Enrol</a></div>';
	echo '<div class="span7 enrol pull-right" style="margin: 10px 0px;">'.
			$course_detail_info.'
			<form action="'.$CFG->wwwroot.'/enrol/index.php" method="post" id="mform1" style="padding: 0px 0px;" class="mform" accept-charset="utf-8" autocomplete="off">';
			echo '<input type="hidden" value="'.$id.'" name="id">
							<input name="instance" value="'.$enrol->id.'" type="hidden">
							<input name="sesskey" value="'.sesskey().'" type="hidden">
							<input name="_qf__'.$enrol->id.'_enrol_self_enrol_form" value="1" type="hidden">
							<input name="mform_isexpanded_id_selfheader" value="1" type="hidden">';
							$check = disable_course_enroll($coursedetails);
							$msg = disable_course_enroll_msg($coursedetails);
							$msgwait=disable_course_enroll_msgwait($coursedetails);
							$enrolled=disable_course_enrol_enrol($coursedetails);
							//print_object($coursedetails);
							
							$startdate=$DB->get_field('course','startdate',array('id'=>$coursedetails->courseid));
							//print_object($startdate);
							$date=date("Y-m-d");
							$curentdate=strtotime($date);
							//print_object($curentdate);
							 $startdate1=date("d-m-Y",$startdate);
							if($check == true){
								if($enrolled==true){
								echo '<input type="submit" id="id_submitbutton" value="Enrol" name="submitbutton">';
								}elseif($startdate>$curentdate){
								echo'<div class="alert alert-danger">You are already enrolled and 
											the course starts on <strong>';echo $startdate1;
								echo '</strong> 
									  </div></h6>'	;
								}else{
										echo'<h6>You are Enrolled</h6>'	;
								}
							}elseif(empty($msg) && empty($msgwait)){
								echo'<h6>Enrol Date is End</h6>';
							}elseif($msgwait){
								echo'<h6>Enrolment yet to start</h6>';
							}
							
			echo '</form></div>';
} else {
	
	//echo '<div class="view_gradeslink"><a href="'.$CFG->wwwroot.'/grade/report/user/index.php?id='.$course->id.'">View Grades</a></div>';
	echo '<div class="span7 enrol pull-right" style="margin:10px 0px;"><a class="knowmore pull-right" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">View content</a></div>';
}
		
		//  if($coursedetails->enrollstartdate!=0){
		//	$datestart=date('d M Y',$coursedetails->enrollstartdate);
		//}else{
		//	$datestart="N/A";
		//}
		// if($coursedetails->enrollenddate!=0){
		//	$dateend=date('d M Y',$coursedetails->enrollenddate);
		//}else{
		//	$dateend="N/A";
		//}
	
		//echo "<ul class='crse_extradetails'>
		//		<li>Enroll startdate: <b class='iteminfo'>".$datestart."</b></li>
		//		<li>Enroll enddate: <b class='iteminfo'>".$dateend."</b></li>
		//		<li>Course Completion Days: <b class='iteminfo'>".$coursedetails->coursecompletiondays."</b></li>
		//		</ul>";
				
echo $renderer->course_sections($id);


echo $OUTPUT->footer();
?>






