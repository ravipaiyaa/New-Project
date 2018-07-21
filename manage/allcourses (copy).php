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
 * @package    blocks
 * @subpackage manage
 * @copyright  2014 Anilkumar.Cheguri <anil@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB, $USER, $CFG, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/blocks/manage/renderer.php');
require_once($CFG->dirroot . '/local/includes.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$PAGE->requires->jquery();

$PAGE->set_url('/local/manage/allcourses.php');
$PAGE->set_title(get_string('e_learning_courses','block_costcenterstructure'));
$PAGE->set_pagelayout('fullpage');
$PAGE->navbar->add(get_string('e_learning_courses','block_costcenterstructure'));
$category = optional_param('category', -1, PARAM_INT);
$type = optional_param('type', 0, PARAM_INT);
$global_search = optional_param('g_search', 0, PARAM_RAW);

$renderer = $PAGE->get_renderer('block_manage');
$includes = new user_course_details();
echo $OUTPUT->header();

$options = array();
$options[-1] = get_string('all');

/*Code For View of Course For admin and Users By Ravi_369*/
if (is_siteadmin()) {
 $sql="select id,name from {course_categories} ";
}else{
 $costcenterid=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
 $id=$DB->get_field('local_costcenter','category',array('id'=>$costcenterid));
 $sql="select id,name from {course_categories} where id=$id or parent=$id";
}
$depart = $DB->get_records_sql($sql);
foreach($depart as $depatement){
  $options[$depatement->id]=$depatement->name;
}
/*End Of Code By Ravi_369*/
$url = new moodle_url('/blocks/manage/allcourses.php',array('type'=>$type));
$sch = new single_select($url,'category',$options,$category, null);
$sch->set_label(get_string('category'));

echo "<h2 class='tmhead2'><i class='fa fa-graduation-cap'></i>".get_string('courses')."</h2>";
echo '<div class="wrapper" >
	  <div class="single_selectbox">
	  <div class="myfilters">'.$OUTPUT->render($sch).'</div>
	  </div>';
    
$enrolled_courses = enrol_get_users_courses($USER->id, true);
if($enrolled_courses){
	$my_enrolledcourses = array();
	foreach($enrolled_courses as $enrolled_course){
		$my_enrolledcourses[] = $enrolled_course->id;
	}
	$mycourses = implode(',', $my_enrolledcourses);
}

// *** for category wise filter
if($category == -1){
	$cat = "";
}else{
	$cat = " and c.category = $category";
}
        
// *** for course type filter
if($type == 0){
	$coursetype = " cd.identifiedas = 1 OR cd.identifiedas = 2 OR cd.identifiedas = 3 OR cd.identifiedas = 4";
}else{
	$coursetype = " cd.identifiedas = $type";
}

$userdata = $DB->get_record('local_userdata', array('userid'=>$USER->id));

$systemcontext = context_system::instance();
/*For admin view of course*/
if(is_siteadmin()) {
	$sql = "select c.* from {course} c
			join {local_coursedetails} cd
			on cd.courseid = c.id
			where c.id > 1 and c.visible = 1 $cat
			and ($coursetype)";
}else{
	$sql = "select c.* from {course} c
			join {local_coursedetails} cd
			on cd.courseid = c.id
			where c.id > 1 and c.visible = 1 AND cd.costcenterid = $userdata->costcenterid $cat and ($coursetype)";

	if($enrolled_courses){
		$sql .= " and c.id NOT IN ($mycourses)";
	}
	
}
/*End of code for users*/
if($global_search){
	$sql .= " and c.fullname LIKE '%$global_search%'";
}
$mycourses = $DB->get_records_sql($sql);

if($mycourses){

	  echo '<div class="box text-shadow">
            
			  <!-- demo -->
			  <div id="demo" class="box jplist">
					
	            <!-- ios button: show/hide panel -->
	            <div class="jplist-ios-button">
		            Filter <i style="vertical-align:middle;" class="caret"></i>
	            </div>
						
	            <!-- panel -->
	            <div class="jplist-panel box panel-top">						
                   
					<div class="custom_page_filtes">
						<!-- filter by title -->
						<div class="text-filter-box">
								
									
							<!--[if lt IE 10]>
							<div class="jplist-label">Filter by Model:</div>
							<![endif]-->
									
									<label>Search</label>
							<input 
								data-path=".model_coursefullname" 
								type="text" 
								value="" 
								placeholder="Search" 
								data-control-type="textbox" 
								data-control-name="model-text-filter" 
								data-control-action="filter"
							/>
						</div>
					</div>
				
                    <div class="custom_pagenos">';
				//  echo '<!-- items per page dropdown -->
				//		<div 
				//		   class="jplist-drop-down" 
				//		   data-control-type="items-per-page-drop-down" 
				//		   data-control-name="paging" 
				//		   data-control-action="paging"
				//		   data-control-animate-to-top="true">
				//				   
				//			<ul>
				//				<li><span data-number="4"> 4 per page </span></li>
				//				<li><span data-number="8" data-default="true"> 8 per page </span></li>
				//				<li><span data-number="16" > 16 per page </span></li>
				//				<li><span data-number="24"> 24 per page </span></li>
				//				<li><span data-number="all"> View All </span></li>
				//			</ul>
				//		</div>
				//	 
				//		<!-- pagination results -->
				//		<div 
				//		   class="jplist-label" 
				//		   data-type="Page {current} of {pages}" 
				//		   data-control-type="pagination-info" 
				//		   data-control-name="paging" 
				//		   data-control-action="paging">
				//		</div>';
								   
				  echo '<!-- pagination -->
						<div 
						   class="jplist-pagination" 
						   data-control-type="pagination" 
						   data-control-name="paging" 
						   data-control-action="paging">
						</div>
                    </div>
	            </div>';
	
	echo '<div class="list box text-shadow">';
  foreach($mycourses as $course){
		 $grid="";
		 $courserecord = $DB->get_record('course', array('id'=>$course->id));
			$course_category = $DB->get_field('course_categories', 'name', array('id'=>$courserecord->category));
		 
		 $coursedetails = $DB->get_record('local_coursedetails', array('courseid'=>$course->id));
 
		 //$coursefileurl = $renderer->get_course_summary_file($course);
		 //$coursefileurl = '';
		 $coursefileurl = $includes->course_summary_files($course);
		 $img = html_writer:: empty_tag('img',array('src'=>$coursefileurl, 'width'=>'100%', 'height'=>'124px'));
		 
		 $coursename = strip_tags($courserecord->fullname);
		 $course_fullname = $coursename;
		 if (strlen($coursename) > 30) { 
			 $coursename = substr($coursename, 0, 30).'...';
			 $course_fullname = $coursename;
		 }
			
			$categoryname = strip_tags($course_category);
		 if (strlen($categoryname) > 30) { 
			 $categoryname = substr($categoryname, 0, 30).'...';
		 }
			   
		 if($courserecord->summary){
			 $summary = $courserecord->summary;
			 $string = strip_tags($summary);
 
			 if (strlen($string) > 55) {
				 // truncate string
				 $stringCut = substr($string, 0, 55);
				 $string = $stringCut.'...'; 
			 }
			 $course_summary = $string;
			  
		 }else{
			 $course_summary = '';
		 }
		 
		 //$viewbtnurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
		 //$viewbutton = html_writer::link($viewbtnurl, get_string('view_details', 'block_manage'), array('class'=>'custom_singlebutton'));               
			 $courseurl = new moodle_url('/blocks/manage/courseinfo.php', array('id'=>$course->id));
			 $courselink = html_writer::link($courseurl, $course_fullname, array('style'=>'color:#000;font-weight: 300;cursor:pointer;', 'title'=>$courserecord->fullname, 'class'=>'available_course_link'));
			 if(!empty($coursedetails->grade)){
				 if($coursedetails->grade == -1){
					 $coursegrade = get_string('all');
				 }else{
					 $coursegrade = $coursedetails->grade;
				 }
			 }else{
				 $coursegrade = get_string('all');
			 }
			 
			 if(!empty($coursedetails->credits)){
				 $coursecredits = $coursedetails->credits;
			 }else{
				 $coursecredits = '---';
			 }
			 if(!empty($coursedetails->enrollstartdate)){
			  $enrollstartdate = date('d/m/Y', $coursedetails->enrollstartdate);
			 }else{
			  $enrollstartdate = 'N/A';
			 }
			 if(!empty($coursedetails->enrollenddate)){
			  $enrollenddate = date('d/m/Y', $coursedetails->enrollenddate);
			 }else{
			  $enrollenddate = 'N/A';
			 }
			 
		 echo'<!-- item -->
					 <div class="list-item">		
										 
						 <div class="top">
							 <p class="model_coursefullname">'.$courserecord->fullname.'</p>
								 <span>'.html_writer::tag('div', $img, array('class'=>'courseimg')).'</span>
							 <div class="detailed_info_container">
							 <p class="model">
								 '.$courselink.'
							 </p>
							 <p class="category_name">
								 '.$categoryname.'
							 </p>
							 <div class="course_info_details_container">
							  <span class="course_info_detail">
							   '.get_string('daystocomplete', 'block_manage').' : '.$coursedetails->coursecompletiondays.'
							  </span>
							  <span class="course_info_detail">
							   '.get_string('enrollstartdate', 'block_manage').' : '.$enrollstartdate.'
							  </span>
							  <span class="course_info_detail">
							   '.get_string('enrollenddate', 'block_manage').' : '.$enrollenddate.'
							  </span>
							 </div>
							 </div>
							</div>
						 <div class="dimensions">
							 <p>
								 <span class="header length-header"></span>
								 <span class="length"><span class="val"></span></span>
							 </p>
							 <p>
								 <span class="header width-header"></span>
								 <span class="width"><span class="val"></span></span>
							 </p>
							 <p>
								 <span class="header weight-header"></span>
								 <span class="weight"><span class="val"></span></span>
							 </p>
						 </div>
					 </div>';
  }
	echo '</div>';
	//<p>'.html_writer:: tag('div', $viewbutton , array('class'=>'course_description')).'</p>
			
	echo'<div class="box jplist-no-results text-shadow align-center">
				<p>No results found</p>
			</div>
					
			<!-- ios button: show/hide panel -->
			<div class="jplist-ios-button">
				Filter <i style="vertical-align:middle;" class="caret"></i>
			</div>
				
			<!-- panel -->
			<div class="jplist-panel box panel-bottom">
						
				<!-- items per page dropdown -->
				<div style="display:none;"
					class="jplist-drop-down" 
					data-control-type="items-per-page-drop-down" 
					data-control-name="paging" 
					data-control-action="paging"
					data-control-animate-to-top="true">
							
					<ul>
						<li><span data-number="4"> 4 per page </span></li>
						<li><span data-number="8" data-default="true"> 8 per page </span></li>
						<li><span data-number="16"> 16 per page </span></li>
						<li><span data-number="24"> 24 per page </span></li>
						<li><span data-number="all"> View All </span></li>
					</ul>
				</div>					
				
				<!-- pagination results -->
				<div style="display:none;"
					class="jplist-label" 
					data-type="{start} - {end} of {all}"
					data-control-type="pagination-info" 
					data-control-name="paging" 
					data-control-action="paging">
				</div>
							
				<!-- pagination -->
				<div 
					class="jplist-pagination" 
					data-control-animate-to-top="true"
					data-control-type="pagination" 
					data-control-name="paging" 
					data-control-action="paging">
				</div>
						
			</div>
			</div>';/*end of wrapper div*/
?>

	<script src="js/jp-list_custom_script.js"></script>
	<!--<style>-->
	<!--	#page-content a{-->
	<!--		color: #8c0a21 !important;-->
	<!--	}-->
	<!--	#region-main h2{-->
	<!--		margin: 78px 0 0 0 !important;-->
	<!--	}-->
	<!--</style>-->
<?php
}else{
	echo html_writer::tag('div', get_string('nocourses', 'block_manage'), array('class'=>'emptydata'));
}

echo $OUTPUT->footer();
?>

	<link href="css/jplist.core.min.css" rel="stylesheet" type="text/css" />
	<link href="css/jplist.filter-toggle-bundle.min.css" rel="stylesheet" type="text/css" />
	<link href="css/jplist.pagination-bundle.min.css" rel="stylesheet" type="text/css" />
	<link href="css/jplist.textbox-filter.min.css" rel="stylesheet" type="text/css" />
	<link href="css/jplist.views-control.min.css" rel="stylesheet" type="text/css" />
	<link href="css/font-awesome.css" rel="stylesheet" />
	<!--Jpagination required js files-->
	<script src="js/jplist.core.min.js"></script>
	<script src="js/jplist.filter-dropdown-bundle.min.js"></script>
	<script src="js/jplist.filter-toggle-bundle.min.js"></script>
	<script src="js/jplist.history-bundle.min.js"></script>
	<script src="js/jplist.jquery-ui-bundle.min.js"></script>
	<script src="js/jplist.pagination-bundle.min.js"></script>
	<script src="js/jplist.sort-bundle.min.js"></script>
	<script src="js/jplist.textbox-filter.min.js"></script>
	<script src="js/jplist.list-grid-view.min.js"></script>