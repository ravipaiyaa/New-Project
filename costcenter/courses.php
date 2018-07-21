<?php
require_once('../../config.php');

global $CFG,$DB,$OUTPUT,$PAGE;

require_login();

require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/course/renderer.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/filterclass.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
require_once($CFG->dirroot.'/local/logs/lib.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/local/costcenter/js/jquery.dataTables.min.js',true);
$PAGE->requires->js('/local/costcenter/js/select2.full.js',true);
$PAGE->requires->js('/local/costcenter/js/custom.js');
$PAGE->requires->js('/local/costcenter/js/delete_confirm.js');

$PAGE->requires->css('/local/costcenter/css/select2.min.css');
$PAGE->requires->css('/local/costcenter/css/custom_styles.css');
$PAGE->requires->css('/local/costcenter/css/jquery.dataTables.min.css');

	
$systemcontext = context_system::instance();

$id        = optional_param('id', 0, PARAM_INT);
$hide      = optional_param('hide', 0, PARAM_INT);
$show      = optional_param('show', 0, PARAM_INT);
$editid = optional_param('edit', 0, PARAM_INT);
$deleteid = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$featured_course = optional_param('featured_id', 0, PARAM_INT);
$featured = optional_param('featured', 0, PARAM_INT);
$categoryid = optional_param('category', 0, PARAM_INT);

$PAGE->set_pagelayout('admin');

$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('courses'));
$PAGE->set_url('/local/costcenter/courses.php');
$PAGE->set_heading(get_string('courses'));
$PAGE->set_title(get_string('courses'));

$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('courses'),new moodle_url('/local/costcenter/courses.php'));
$output = $PAGE->get_renderer('core','course');

$renderer = new coursecat_helper();

echo $output->header();
 
 $collapse = true;
 
 if(is_siteadmin()){
     $costcenter="";
  }else{
    $costcenter = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
  } 
  
 if(!has_capability('local/costcenter_course:enrol', $systemcontext)){
	
	redirect($CFG->wwwroot . '/local/error.php?id=1');
}
 
 
$mypageurl = new moodle_url('/my/index.php', array('la'=>true));

/*Filter Added For The Course View Page*/
$actionpage ='#';
$mform=new custom_filter($actionpage);/*Object Create for The Filter Class*/

if (is_siteadmin() && has_capability('local/assign_multiple_departments:manage', $systemcontext)) { /*Checking cap and Allowing to those users*/
  $label=get_string('organization', 'local_users');/*Label for selectbox costcenter*/
  $name="costcenter";/*Name for selectbox costcenter*/
  $functions=$mform->get_costcenters();/*Function for selectbox costcenter*/
  $mform->filters($label,$name,$functions);/*calling function for selectbox costcenter*/
 }
  /*Select Box for The Type*/
  $label=get_string('type', 'local_users');/*Label for selectbox costcenter*/
	$identify = array();
	//$identify[''] = get_string('select');/*values for selectbox costcenter*/
	$identify['1'] = get_string('mooc');
	$identify['2'] = get_string('ilt');
	$identify['3'] = get_string('elearning');
	$identify['4'] = get_string('learningplan');
  $name="type";/*name for selectbox costcenter*/
  $functions=$identify;/*Function for selectbox costcenter*/
  $mform->filters($label,$name,$functions);/*Calling Function for selectbox costcenter*/
 /*End OF the Select Box*/
  
	/*Select Box for The Category*/
	if (!is_siteadmin() || !has_capability('local/assign_multiple_departments:manage', $systemcontext)) {
		$label=get_string('category', 'filters');/*Label for selectbox category*/
		$name="category";/*Name for selectbox category*/
		$functions=$mform->get_category_list($costcenter);/*Function for selectbox category*/
		$mform->filters($label,$name,$functions);/*calling function for selectbox category*/
	}else{
		$label=get_string('category', 'filters');/*Label for selectbox category*/
		$name="category";/*Name for selectbox category*/
		$functions=$mform->category_list();/*Function for selectbox category*/
		$mform->filters($label,$name,$functions);/*calling function for selectbox category*/
	}
	/*End OF the Select Box*/
  
	/*Buttons*/
	$mform->buttonsub();
	/*End of the code for Filters with search Buttons Done BY Ravi_369*/
  echo "<h2 class='tmhead2'>".get_string('courses')."</h2>";

//$is_manager = $DB->record_exists_sql("select cp.* from {local_costcenter_permissions} as cp 
//                             JOIN {role_assignments} as ra ON ra.userid=cp.userid and cp.userid=$USER->id
//                             JOIN {role} as r ON r.id=ra.roleid
//                             where r.archetype='manager'");
//if($is_manager){
//	$costcenters = $DB->get_fieldset_select('local_costcenter_permissions','costcenterid','userid='.$USER->id.'');
//	$costcenters_string=implode(',',$costcenters);
//}

	if($deleteid && $confirm && confirm_sesskey()){
		
		$course=$DB->get_record('course',array('id'=>$deleteid));
        delete_course($course, false);
		if($course){
			$DB->delete_records('local_learningplan_courses', array('courseid'=>$course->id));
			$DB->delete_records('local_coursedetails', array('courseid'=>$course->id));
			
		 }
		/***  After deletion of a course, the course details are inserted into local_logs table by Shivani M  ****/
		$course_detail = new stdClass();
		$sql = $DB->get_field('user','firstname', array('id' =>$USER->id));
		$course_detail->userid = $sql;
		$course_detail->courseid = $deleteid;
		$description = get_string('descptn','local_logs',$course_detail);
		$logs = new local_logs();
		$insert_logs = $logs->local_custom_logs('delete', 'course', $description, $deleteid);
		redirect($CFG->wwwroot . '/local/costcenter/courses.php');	
	}
	if ((($hide>0) or ($show>0)) and ($id>0) ) {
		if(($hide>0) and ($id>0)){
			$update="update {course} set visible=1 where id=$id";
			$newlevel=$DB->execute($update);
			redirect($CFG->wwwroot . '/local/costcenter/courses.php'); 
		}
		if(($show>0) and ($id>0)){
			$update="update {course} set visible=0 where id=$id";
			$newlevel=$DB->execute($update);
			redirect($CFG->wwwroot . '/local/costcenter/courses.php');
		}
	}
	
		$coursestable = "<table id='coursesearch'>
										<thead>
											<tr><th></th></tr>
										</thead>
									</table>";
		
		if($mform->is_cancelled()){
    }elseif($data=$mform->get_data()){
			$departments = $data->costcenter;
			$types = $data->type;
			$categories = $data->category;
			
			$coursestable .= html_writer::script("$(document).ready(function() {
											
												var selecteddepartments = [];
												var coursetypes = [];
												var categories = [];
																		
												$('#id_costcenter :selected').each(function(i, selected) {
														selecteddepartments[i] = $(selected).val();
												});
												
												$('#id_type :selected').each(function(i, selected) {
														coursetypes[i] = $(selected).val();
												});
												
												$('#id_category :selected').each(function(i, selected) {
														categories[i] = $(selected).val();
												});
											
																	 
											$('#coursesearch').DataTable({
												'processing': true,
												'serverSide': true,
												ajax:{
                          url :'ajax.php', 
													data: {
																	'department':JSON.stringify(selecteddepartments),
																	'coursetype':JSON.stringify(coursetypes),
																	'category':JSON.stringify(categories),
																},
                        },
												
											});
											$.fn.dataTable.ext.errMode = 'throw';
										});
									");
			
			$collapse = false;
    }else{
			$coursestable .= html_writer::script("$(document).ready(function() {
											$('#coursesearch').DataTable( {
												'processing': true,
												'serverSide': true,
												'ajax': 'ajax.php',
												'dataType': 'json',
											});
											$.fn.dataTable.ext.errMode = 'throw';
										});
									");

			$collapse = true;
		}
	
	if (has_capability('moodle/course:create',$systemcontext) || is_siteadmin()) {
		
		$options = array();
		if (!empty($category->id)) {
			$options['category'] = $category->id;
		} else {
			$options['category'] = $CFG->defaultrequestcategory;
		}
		
		echo html_writer::start_tag('div', array('class'=>'addcoursebutton'));
			$url = new moodle_url('/course/edit.php', array('category'=>$options['category']));
			echo html_writer::link($url, get_string("createnewcourse", "local_costcenter"));
		echo html_writer::end_tag('div');
		
	}


print_collapsible_region_start('', 'batches-form', get_string('filter'),false,$collapse);
	$mform->display();
print_collapsible_region_end();   
	
	echo $coursestable;

echo $output->footer();