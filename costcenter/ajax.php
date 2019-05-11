<?php
define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
  
global $DB, $PAGE,$USER,$CFG,$OUTPUT;
  
$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('courses'));
$PAGE->set_url('/local/costcenter/ajax.php');
$PAGE->set_title(get_string('courses'));


$requestData = $_REQUEST;

$requestDatacount = array();

$costcenters = json_decode($_REQUEST['department'], true);
$coursetypes = json_decode($_REQUEST['coursetype'], true);
$categories = json_decode($_REQUEST['category'], true);

$selecteddepts = null;
if(isset($_REQUEST['department']) && !empty($costcenters)){
  $selecteddepts = $costcenters;
}

$selectedctypes = null;
if(isset($_REQUEST['coursetype']) && !empty($coursetypes)){
  $selectedctypes = $coursetypes;
}

$selectedcategories = null;
if(isset($_REQUEST['category']) && !empty($categories)){
  $selectedcategories = $categories;
}

$sql = "SELECT c.id, c.category, c.fullname, c.fullname, c.visible, cd.costcenterid, cd.identifiedas,
        cd.enrollstartdate, cd.enrollenddate, cd.coursecompletiondays, cd.usercreated, cd.requestcourseid,
				cd.id as cdid, cd.coursecreator
        FROM {course} c
        JOIN {local_coursedetails} cd ON cd.courseid = c.id
				JOIN {course_categories} ct ON ct.id = c.category
        WHERE c.id > 1 ";

				
	if(!is_siteadmin() && has_capability('local/costcenter_course:enrol',$systemcontext)  ){
		
	        if (!is_siteadmin() && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
				$field_act=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
				  $sql .= " AND cd.costcenterid = {$field_act} ";
				
			} /*Course Manager  */
			else if(!has_capability('local/costcenter:view',$systemcontext)){
			//----query to display courses related to course creator----
			      $sql .= " AND cd.coursecreator= $USER->id ";			
		      }
			
		    else{	
			//----to display all OH related courses----
				$userdata = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
				$sql .= " AND cd.costcenterid = $userdata";	
		}
	}
				
				
// for organisation filter
	if($selecteddepts){
		if(!in_array(-1, $selecteddepts)){
			$organisations = implode(',', $selecteddepts);
			$sql .= " AND cd.costcenterid IN ($organisations) ";
		}
  }
	
//	// for coursetypes filter
//	if($selectedctypes){
//		$ctypes = implode(',', $selectedctypes);
//		if($ctypes){
//				$sql .= " AND cd.identifiedas IN ($ctypes) ";
//		}
//  }

		if($selectedctypes){	
				$j=0;
				$len = count($selectedctypes);
			
			foreach ($selectedctypes as $value){
				if($len >1){
					if($j==0){
						$sql .= " AND (FIND_IN_SET ($value, cd.identifiedas) ";
					}elseif($j == $len-1) {
						$sql.=" OR FIND_IN_SET ($value, cd.identifiedas) ) ";
					}else{
						 $sql.=" OR FIND_IN_SET ($value, cd.identifiedas) ";
					}
						$j++;
				}else{
					$sql .= " AND (FIND_IN_SET ($value, cd.identifiedas) )";
				}
			}
		}
	
	// for category filter
	if($selectedcategories){
		$ccategories = implode(',', $selectedcategories);
		if($ccategories){
				$sql .= " AND c.category IN ($ccategories) ";
		}
  }
	
	// for search with course name
	$search = $requestData['search']['value'];
	if($search){
			$sql .= " AND c.fullname LIKE '%".$search."%'";
	}
	$sql .= " ORDER BY c.id DESC";
	//echo $sql;
$allcourses = $DB->get_records_sql($sql);
$courseslist = array_slice($allcourses, $requestData['start'], $requestData['length']);

$data = array();

foreach($courseslist as $course){
    $row = array();
		
    $course_statistics = $DB->get_record_sql("SELECT count(ue.userid) as enrolled,count(cc.course) as completed
												FROM {user_enrolments} as ue
												JOIN {user} as u ON u.id=ue.userid
												JOIN {enrol} as e ON e.id=ue.enrolid
												RIGHT JOIN {course} as c ON c.id =e.courseid
												LEFT JOIN {course_completions} cc ON cc.course=e.courseid and ue.userid=cc.userid and cc.timecompleted IS NOT NULL
												WHERE c.id=$course->id AND u.deleted = 0
                                group by e.courseid" );
		
    $content = html_writer::start_tag('div', array('class' => 'coursebox'));
		
    $content .= html_writer::start_tag('div', array('class' => 'info'));
	
    $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                                        $course->fullname, array('class' => $course->visible ? '' : 'dimmed'));
    $content .= '<div class="course_container_details">'.html_writer::tag('div', $coursenamelink, array('class' => 'coursename'));
    
    $actionshtml = array();
   // if (has_capability('local/costcenter_course:enrol',$systemcontext) || is_siteadmin()) {
	 /************The above code commented to disable featured course for the CM*********/
      if ((has_capability('local/costcenter_course:enrol',$systemcontext) && has_capability('local/costcenter:view',$systemcontext)) || is_siteadmin()) {   
        
        if($course->requestcourseid == 0){
            $featured_value = 1;
            $html = html_writer::start_tag("span", array("class"=>"featured$course->cdid")); 
            $html .= html_writer::tag('a',
                                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('coloredIcon', 'local_costcenter'),
                                       'title' => 'Featured Courses', 'alt' => 'Featured Courses','onClick' => 'featuredcourses(' . $course->cdid . ','.$featured_value.')', 'class'=>'myFunction','style'=>'width:18px;height:18px;padding: 0px;')),
                                                                             array('href' => 'javascript:void(0)','featured_id' => $course->cdid, 'featured' =>$featured_value, 'sesskey' => sesskey() ));
            $html .= html_writer::end_tag("span");
            $actionshtml[] = $html;
        }else{
            $featured_value = 0;
            $html = html_writer::start_tag("span", array("class"=>"featured$course->cdid"));
            $html .= html_writer::tag('a',
                                html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('colored', 'local_costcenter'),
                               'title' => 'Featured Courses', 'alt' => 'Featured Courses','onClick' => 'featuredcourses(' . $course->cdid . ','.$featured_value.')', 'class'=>'myFunction','style'=>'width:18px;height:18px;padding: 0px;')),
                                                                     array('href' => 'javascript:void(0)','featured_id' => $course->cdid, 'featured' =>$featured_value, 'sesskey' => sesskey() ));
            $html .= html_writer::end_tag("span");
            $actionshtml[] = $html;
        }
    }
        
    if (has_capability('local/costcenter_course:enrol',$systemcontext) || is_siteadmin()) {
		$actionshtml[] = html_writer::link(new moodle_url('/local/mass_enroll/mass_enroll.php',array('id'=>$course->id)),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/groups'), 'title' => 'Bulk enroll', 'alt' => 'Bulk enroll', 'class' => 'iconsmall'))); 
    }
    if (has_capability('local/costcenter_course:enrol',$systemcontext) || is_siteadmin()) {
		$actionshtml[] = html_writer::link(new moodle_url('/enrol/auto/edit.php',array('courseid'=>$course->id)),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/enrolusers'), 'title' => 'Auto enroll', 'alt' => 'Bulk enroll', 'class' => 'iconsmall'))); 
    }
    if(has_capability('local/costcenter_course:enrol',$systemcontext) ||is_siteadmin()){
		$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/courseenrol.php',array('id'=>$course->id,'enrolid'=>$DB->get_field('enrol','id',array('enrol'=>'manual','courseid'=>$course->id)))),html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('enrolusers','enrol'), 'alt' => get_string('enrolusers','enrol'), 'class' => 'iconsmall'))); 
    }
    if(has_capability('local/costcenter_course:delete',$systemcontext) ||is_siteadmin()){
				$deleteiconurl  = $OUTPUT->pix_url('t/delete');
				$deleteicon = html_writer::img($deleteiconurl, get_string('delete'), array('id' => "delete_" . $course->id, 'title' => get_string('delete'),));
				$url1 = new moodle_url('/local/costcenter/courses.php', array('delete' => $course->id,'id'=>$course->id, 'sesskey' => sesskey()));
        $deleteactionshtml = html_writer::link($url1, $deleteicon ,array('id' => 'deleteconfirm' . $course->id. ''));
				
				$deleteactionshtml .= html_writer::script("Y.on('click', M.util.fractal_show_confirm_dialog, '#deleteconfirm$course->id', null, {'message':'" . get_string('delconfirm', 'local_costcenter') . "','callbackargs':{'id':$course->id}});");                
        
				$actionshtml[] = $deleteactionshtml;
    }
    if(has_capability('local/costcenter_course:visibility',$systemcontext) ||is_siteadmin()){
			if($course->visible==0) {
				$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/courses.php', array('id' => $course->id, 'hide' => 1, 'sesskey' => sesskey())),
					html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'),'onmouseover' => "this.src='".$OUTPUT->pix_url('t/show')."'", 'onmouseout' => "this.src='".$OUTPUT->pix_url('t/show')."'",
					'title' => get_string('inactive'), 'alt' => get_string('hide')))); 
			 
			} else {
				$actionshtml[] = html_writer::link(new moodle_url('/local/costcenter/courses.php', array('id' => $course->id, 'show' => 1, 'sesskey' => sesskey())),
				html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'),'onmouseover' => "this.src='".$OUTPUT->pix_url('t/hide')."'", 'onmouseout' => "this.src='".$OUTPUT->pix_url('t/hide')."'", 'title' => get_string('active'),
												'alt' => get_string('show'), 'class' => 'iconsmall')));
						
			}
        $actionshtml[] = html_writer::link(new moodle_url('/course/edit.php', array('id' => $course->id, 'returnto' => 'catmanage')),
                        html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/settings'),'title' => get_string('edit'),
                                                            'alt' => get_string('show'), 'class' => 'iconsmall')));
    }

    if($course->coursecreator){
        $coursecreator = $DB->get_record_sql("select * from {user} where id = $course->coursecreator");
        $creator = fullname($coursecreator);
    }else{
        $creator = 'N/A';
    }
        
    // for get course type
    $coursetypename = array();
    $coursetype = explode(',',$course->identifiedas);
    foreach($coursetype as $course_type){
        if($course_type == 1){
            $coursetypename[] = get_string('mooc');
        }elseif($course_type == 2){
            $coursetypename[] = get_string('ilt');
        }elseif($course_type == 3){
            $coursetypename[] = get_string('elearning');
        }else{
            $coursetypename[] = get_string('learningplan');
        }
    }
    
    if($course->enrollstartdate==0){
        $enrolstartdate="N/A";
    }else{
        $enrolstartdate=date('d M Y',$course->enrollstartdate);
    }
    if($course->enrollenddate==0){
        $enrollenddate="N/A";
    }else{
        $enrollenddate=date('d M Y',$course->enrollenddate);
    }
    
    $completiondays = (!empty($course->coursecompletiondays) ? $course->coursecompletiondays : 'N/A');
    
    
    $couse_catname = $DB->get_field('course_categories', 'name', array('id'=>$course->category));
    $content .= "<table class='crseinfo'>
                      <tr>
                          <td>Category<b>: ".$couse_catname."</b></td>
                          <td>Type<b>: ".implode(',',$coursetypename)."</b></td>
                          <td>Enrolled<b>: ".$course_statistics->enrolled."</b></td>
                          <td>Completed<b>: ".$course_statistics->completed."</b></td>
                      </tr>
                      <tr>
                        <td>Enroll Start Date<b>: ".$enrolstartdate."</b></td>
                        <td>Enroll End Date<b>: ".$enrollenddate."</b></td>
                        <td>Completion Days<b>: ".$completiondays."</b></td>
                        <td>Course Creator<b>: ".$creator."</b></td>
                      </tr>
                </table>
                </div>";
    
    $content .= '<div class="action_container">'.html_writer::div(join('', $actionshtml), 'course-item-actions item-actions');
    $content .= '<div style="float: right;margin-top: 20px;margin-right: 20px;">
                    <a class="knowmore" href="'.$CFG->wwwroot.'/course/view.php?id='.$course->id.'">View Content</a>
                </div>';
    $content .= '</div>';
                
    $content .= html_writer::end_tag('div'); // .info
		
		$content .= html_writer::end_tag('div'); // .coursebox
		
		$row[] = $content;                                                                                                                                                                                                                                                                             
    $data[] = $row;
}

$iTotal = count( $allcourses ); 
$iFilteredTotal = $iTotal;

//$output = array(
//		"draw" => intval($requestData['draw']),
//		"recordsTotal" => $iTotal,
//		"recordsFiltered" => $iFilteredTotal,
//		"data" => $data
//	);
$output = array(
		"sEcho" => intval($requestData['sEcho']),
		"iTotalRecords" => $iTotal,
		"iTotalDisplayRecords" => $iFilteredTotal,
		"aaData" => $data
	);
echo json_encode($output);
 ?>
