<?php
require_once '../../config.php';
global $CFG,$DB,$OUTPUT,$PAGE,$USER;

require_once($CFG->dirroot.'/course/renderer.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('courses'));
$PAGE->set_url('/local/costcenter/courses.php');
$PAGE->set_heading(get_string('courses'));
$PAGE->set_title(get_string('courses'));
$PAGE->navbar->add(get_string('pluginname', 'local_costcenter'), new moodle_url('/local/costcenter/index.php'));
$PAGE->navbar->add(get_string('courses'));
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->js('/mod/facetoface/custom.js');
$PAGE->requires->js('/mod/facetoface/js/custom.js');
$output = $PAGE->get_renderer('core','course');
$userid=$USER->id;
$dept_name= optional_param('name',1, PARAM_RAW);
$requestData= $_REQUEST;
//print_object($requestData);
$requestDatacount=array();
if ( $requestData['sSearch'] != "" ){
	$requestDatacount['sSearch']=$requestData['sSearch'] ;
}
	/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
	 * Easy set variables
	 */
	
	/* Array of database columns which should be read and sent back to DataTables. Use a space where
	 * you want to insert a non-database field (for example a counter or static image)
	 */
	$aColumns = array( '');
	
	/* Indexed column (used for fast and accurate table cardinality) */
	$sIndexColumn = "id";
	
	/* DB table to use */
	$sTable = "ajax";

$courses=department_wise_course($id=0,$dep=-1,$requestData,'',$course_type=1,$dept_name);
$f2f_records_count =department_wise_course($id=0,$dep=-1,$requestDatacount,'',$course_type=1,$dept_name,$course_total=1);
//$courses=manage_courses($courses1,$output,$dept_name);
//$f2f_records_count=array();
//print_object($f2f_records_count);
//$f2f_records_count =view_session_face2face_sql($userid,$sessiontype,$requestDatacount,$starttime,$endtime,$batchid);
//$f2f_records =view_session_face2face_sql($userid,$sessiontype,$requestData,$starttime,$endtime,$batchid);
//print_object($f2f_records);

$coursecount = 0;
$i=0;
$out='';
$data=array();
//print_object($courses);
foreach($courses as $course){
	$row=array();
	$courses_courses = $DB->get_records_sql("SELECT * FROM {local_batch_courses} where courseid=$course->id");
	$courses_request = $DB->get_records_sql("SELECT *  FROM {enrol} WHERE `courseid` = $course->id and enrol='apply'");


   if($dept_name==4){
	$course_statistics=new stdClass();
	$course_statistics->enrolled=0;
	$course_statistics->completed=0;
   }else{
    $course_statistics = $DB->get_record_sql("SELECT count(ue.userid) as enrolled,count(cc.course) as completed
                                              FROM {user_enrolments} as ue 
                                              JOIN {enrol} as e ON e.id=ue.enrolid
                                        RIGHT JOIN {course} as c ON c.id =e.courseid
                                         LEFT JOIN {course_completions} cc ON cc.course=e.courseid and ue.userid=cc.userid and cc.timecompleted  IS NOT NULL 
                                             WHERE c.id=$course->id 
                                          group by e.courseid");
   }
    $chelper = new coursecat_helper;
        // .coursebox
                   $coursecount ++;
            $classes = ($coursecount%2) ? 'odd' : 'even';
            if ($coursecount == 1) {
                $classes .= ' first';
            }
            if ($coursecount >= count($courses)) {
                $classes .= ' last';
            }
            
          $classes = trim('coursebox clearfix ');
        if ($chelper->get_show_courses() >= 30) {
            $nametag = 'h3';
        } else {
            $classes .= ' collapsed';
            $nametag = 'div';
        }
        if($i%2==0)
        $classes .= ' even';
        else
        $classes .=' odd';
        $content = html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => 1,
        ));

        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content .= html_writer::start_tag('div', array('class' => 'info'));
            
        // course name
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                                            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::tag($nametag, $coursenamelink, array('class' => 'coursename'));        
        $category = coursecat::get($course->category);
         //echo  $renderer->course_listitem_actions($category,$course);
        $actions = \core_course\management\helper::get_course_listitem_actions($category, $course);
        if (empty($actions)) {
            return '';
        }
        $actionshtml = array();
        
        if($dept_name==2 && !empty($courses_courses) && empty($courses_request)){
		
	    }else{
			if($course->visibleold==1){
				$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/upload_employees/upload.php',array('courseid'=>$course->id,'name'=>$dept_name)),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/groups'), 'title' => 'Bulk enroll', 'alt' => 'Bulk enroll', 'class' => 'iconsmall'))); 
				$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/course_enrol.php',array('id'=>$course->id,'enrolid'=>$DB->get_field('enrol','id',array('enrol'=>'manual','courseid'=>$course->id)))),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('enrolusers','enrol'), 'alt' => get_string('enrolusers','enrol'), 'class' => 'iconsmall'))); 
			}
		}
		
        foreach ($actions as $action) {
            $action['attributes']['role'] = 'button';
            if($action['icon']->attributes['title']=== 'Edit' || $action['icon']->attributes['title']=== 'Delete'){
				 if($dept_name==2 && !empty($courses_courses) && empty($courses_request) && $action['icon']->attributes['title']=== 'Delete'){
				 
				 }else{
					 $actionshtml[] = $output->action_icon($action['url'], $action['icon'], null, $action['attributes']);
				 }
				
			}
           
        }
         //$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/course_enrol.php',array('id'=>$course->id,'enrolid'=>$DB->get_field('enrol','id',array('enrol'=>'manual','courseid'=>$course->id)))),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('enrolusers','enrol'), 'alt' => get_string('enrolusers','enrol'), 'class' => 'iconsmall'))); 
//        $actionshtml[] =html_writer:: tag('a','<span class=knowmore style="float:right;">Continue</span>',array('href'=>$CFG->wwwroot.'/mod/facetoface/attendence.php', 'id'=>'deleteconfirm'.$course->id));
//		 $PAGE->requires->event_handler('#deleteconfirm'.$course->id, 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => get_string('attendance_info','facetoface',$course->fullname), 'callbackargs' => array('id'=>$course->id),'extraparams'=>array('s' =>$course->id))); 
		
		
		
		 $content .= html_writer::span(join('', $actionshtml), 'course-item-actions item-actions');         
        
         //$actionshtml[] =html_writer:: tag('a','<span class=knowmore style="float:right;">Continue</span>',array('href'=>$CFG->wwwroot.'/local/costcenter/courses.php?visable='.$course->id.'', 'id'=>"deleteconfirm$course->id")).html_writer::script("Y.on('click','M.util.tmahendra_show_confirm_dialog',#deleteconfirm$course->id, null, {'message':'" . get_string('attendance_info','facetoface',$course->fullname) . "','callbackargs':{'visable':$course->id,'extraparams':'&rem=remove&s=$course->id'}});");
		  //if($dept_name==1){
			if($course->visibleold==1){
				  $actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/courses.php',array('invisable'=>$course->id,'name'=>$dept_name)),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => 'Hide', 'alt' => 'Hide', 'class' => 'iconsmall'))); 
			}elseif($course->visibleold==0){
				 $actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/courses.php',array('visable'=>$course->id,'name'=>$dept_name)),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => 'Show', 'alt' => 'Show', 'class' => 'iconsmall'))); 
			}
		  //}
        
         $content .= "<ul id='courseview-info'>
         <li>".get_string('pluginname','local_costcenter').": ". $DB->get_field('local_costcenter','fullname',array('id'=>$course->costcenter))."</li>
         <li>Enrolled: $course_statistics->enrolled</li><li>Completed: $course_statistics->completed</li></ul>";
        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.        
        
        $content .= html_writer::start_tag('div', array('class' => 'moreinfo'));
        if ($chelper->get_show_courses() < 20) {
            if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
                $url = new moodle_url('/course/info.php', array('id' => $course->id));
                $image = html_writer::empty_tag('img', array('src' => $output->pix_url('i/info'),
                    'alt' => 'summary'));
                $content .= html_writer::link($url, $image, array('title' => 'summary'));
                // Make sure JS file to expand course content is included.

            // We must only load this module once.
            //$PAGE->requires->yui_module('moodle-course-categoryexpander',
            //        'Y.Moodle.course.categoryexpander.init');

            }
        }
       
        $content .= html_writer::end_tag('div'); // .moreinfo

        // print enrolmenticons
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pix_icon) {
                $content .= $output->render($pix_icon);
            }
            $content .= html_writer::end_tag('div'); // .enrolmenticons
        }
        
        $content .= html_writer::end_tag('div'); // .info

        $content .= html_writer::start_tag('div', array('class' => 'content'));

        if ($chelper->get_show_courses() == 30) {
            require_once($CFG->libdir. '/coursecatlib.php');
            if ($cat = coursecat::get($course->category, IGNORE_MISSING)) {
                $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                $content .= get_string('category').': '.
                        html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                                $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                $content .= html_writer::end_tag('div'); // .coursecat
            }
        }
        
        $content .= html_writer::end_tag('div'); // .content
        
        $content .= html_writer::end_tag('div'); // .coursebox
        
         $i++;
           $batchinfo='<table class="generaltable" id="batch_info_table_all">';
           $batchinfo.='<thead style="display: none;"><tr>
                       <th></th>
                       <th></th>
                       <th></th>
                       ';
          $batchinfo.='</tr></thead><tbody>';
                   
          $batchinfo.='<tr>';
          $batchinfo.='<td style="width:30%;padding: 18px;"><h5 id="f2f_heading">'.$coursename .'</h5></td>
                        <td style="width:17%;padding: 18px;"><span style="font-style:italic;">'.get_string('pluginname','local_costcenter').' : <span style="color:#857171;">&nbsp'.$DB->get_field('local_costcenter','fullname',array('id'=>$course->costcenter)).'</span></span></span></td>
                        <td style="width:15%;padding: 18px;"><span style="font-style:italic;">Enrolled: <span style="color:#857171;">&nbsp'.$course_statistics->enrolled.'</span></span></span></td>
                        <td style="width:15%;padding: 18px;"><span style="font-style:italic;">Completed: <span style="color:#857171;">&nbsp'.$course_statistics->completed.'</span></span></span></td>
                        <td style="width:20%;text-align: right;padding: 18px;"><span style="font-style:italic;">'.implode(' ',$actionshtml).'</td>
                        <td style="width:5%;padding: 18px;"><span style="font-style:italic;"><a href='.$CFG->wwwroot.'/course/view.php?id='.$course->id.'>View </a></td>
                        </tr></tbody></table>';
        $row[]=$batchinfo;
		$data[]=$row;
}
//if ( $requestData['sSearch'] != "" ){
//	$iTotal = count($data);
//}else{
//   $iTotal = count($f2f_records_count); 
//}
 $iTotal = count($f2f_records_count); 
$iFilteredTotal = $iTotal;  // when there is no search parameter then total number rows = total number filtered rows.

$output = array(
		"sEcho" => intval($requestData['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => $data
	);
//print_object($aColumns);
echo json_encode($output);
 ?>