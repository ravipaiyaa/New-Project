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
 * @subpackage School
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
require_once($CFG->dirroot.'/local/costcenter/renderer.php');
require_once($CFG->dirroot.'/local/lib.php');
require_once($CFG->dirroot.'/local/assignroles/assign_form.php');
require_once($CFG->dirroot.'/local/assignroles/lib.php');
//require_once('assign_form.php');
$id = optional_param('id', 0, PARAM_INT);
global $DB,$OUTPUT,$CFG, $PAGE;
/* ---First level of checing--- */
require_login();

$systemcontext = context_system::instance();
/*changes by hameed on 09/11/2016 starts*/
$PAGE->requires->jquery();
$PAGE->requires->jquery('ui');
$PAGE->requires->jquery('ui-css');

?>
<!--<script type="text/javascript" language="javascript" src="jquery.js"></script>-->
<!--<script src="http://code.jquery.com/jquery-1.9.1.js"></script>-->
<!--<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>-->
<!--<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />-->
<!--/*changes by hameed on 09/11/2016 ends*/-->
<?php

/* ---get the admin layout--- */
$PAGE->requires->js('/local/assignroles/js/select2.full.js');
$PAGE->requires->css('/local/assignroles/css/select2.min.css');
$PAGE->requires->css('/local/assignroles/css/styles.css');
$PAGE->requires->js('/local/assignroles/js/custom_script.js');
$PAGE->requires->js('/local/costcenter/js/view.js');
$PAGE->requires->css('/local/costcenter/css/view.css');
$userid = optional_param('userid', 0, PARAM_INT);
$deptid = optional_param('deptid', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check whether the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
/* ---second level of checking--- */

$PAGE->set_url('/local/costcenter/costcenterview.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('costcenter', 'local_costcenter'));
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('viewcostcenter', 'local_costcenter'),new moodle_url('/local/costcenter/costcenterview.php', array('id' => $id)));
echo $OUTPUT->header();
/* ---Get the records from the database--- */

if (!$depart = $DB->get_record('local_costcenter', array('id' => $id))) {
    print_error('invalidschoolid');
}

echo $OUTPUT->heading($depart->fullname);
echo $OUTPUT->box($depart->description);
if (is_siteadmin() || has_capability('local/assign_multiple_departments:manage', $systemcontext)) {
           /*This query executed when the admin or capablity is allowed*/
           $sql = "SELECT distinct(s.id),s.* FROM {local_costcenter} s where parentid=0 ORDER BY s.sortorder";
        }else if(has_capability('local/costcenter:manage',$systemcontext) AND ! has_capability('local/assign_multiple_departments:manage', $systemcontext)){
           /*This query executed when the Manger Login*/
           $costcenter=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
           $sql="SELECT distinct(s.id),s.* FROM {local_costcenter} s   where id=$costcenter ";
        }
        $costcenters = $DB->get_records_sql($sql);
        if (!is_siteadmin() && empty($costcenters)) {
            print_error('notassignedcostcenter', 'local_costcenter');
        }
    $sqla="select id,id as id_val from {local_costcenter} where parentid='".$id."'";
        $s_id=$DB->get_records_sql_menu($sqla);
        $department = count($s_id);
        if($department > 0){
            $department = $department;
        }else{
            $department = 'N/A';
        }
        
        $dept_id=implode(',',$s_id);
        if($dept_id){
             $sql="select id,id as id_val from {local_costcenter} where parentid IN($dept_id);";
             $subsubid=$DB->get_records_sql_menu($sql);
             $subdepartment=count($subsubid);
             if($subdepartment > 0){
                $subdepartment = $subdepartment;
             }else{
                $subdepartment = 'N/A';
             }
        
        $sub_sub_id=implode(',',$subsubid);
        if($sub_sub_id){
             $sql="select id,id as id_val from {local_costcenter} where parentid IN($sub_sub_id);";
             $sub_sub_id=$DB->get_records_sql_menu($sql);
             $subsubdepartment=count($sub_sub_id);
             if($subsubdepartment > 0){
                $subsubdepartment = $subsubdepartment;
             }else{
                $subsubdepartment = 'N/A';
             }
            }
        }
       $sql6="select id from {local_coursedetails} where costcenterid='".$id."'";
       $courseid3=$DB->get_records_sql_menu($sql6);
       $coursecount=count($courseid3); 
       
       $sql3="select cd.id from {local_coursedetails} cd JOIN {course} c ON cd.courseid = c.id
              where cd.costcenterid='".$id."' AND c.visibleold=1";
       $courseid1=$DB->get_records_sql_menu($sql3);
       $activecoursecount=count($courseid1);
       
       $sql4="select cd.id from {local_coursedetails} cd JOIN {course} c ON cd.courseid = c.id
              where cd.costcenterid='".$id."' AND c.visibleold=0";
       $courseid2=$DB->get_records_sql_menu($sql4);
       $inactivecoursecount=count($courseid2);
       
       $sql5="select id from {local_userdata} where costcenterid='".$id."'";
       $courseid=$DB->get_records_sql_menu($sql5);
       $usercount=count($courseid);
       
       $sql1="select ud.id from {local_userdata} ud  JOIN {user} u ON ud.userid=u.id
              where ud.costcenterid='".$id."' AND u.suspended=0";
       $course_id=$DB->get_records_sql_menu($sql1);
       $activeusercount=count($course_id);
       
       $sql2="select ud.id from {local_userdata} ud  JOIN {user} u ON ud.userid=u.id
              where ud.costcenterid='".$id."' AND u.suspended=1";
       $course_id1=$DB->get_records_sql_menu($sql2);
       $inactiveusercount=count($course_id1);
       if($department > 0){
        $dept_count_link = $department;
    }else{
        $dept_count_link = $department;
    }
        $output .= "<div class='content_dept'>
                    <div class='content_dept_sub'>
                        <span class='user_content'>Departments : $dept_count_link</span>   
                        <span class='user_content'>Sub Departments : <span class='count_numbers'>$subdepartment</span></span>
                        <span class='user_content'>Sub Sub Departments : <span class='count_numbers'>$subsubdepartment</span></span>
                        </div>
                            <div class='usermiddle'>
                               <span class='user'>Total-Users : <span class='count_numbers'>$usercount</span></span>
                                <div class='course_content'>
                                    <span class='total'>Active : $activecoursecount</span>
                                    <span class='total'>Inactive : $inactivecoursecount</span>
                                </div>
                                <span class='user'>Total-Courses : <span class='count_numbers'>$coursecount</span></span>
                                <div class='course_content'>
                                    <span class='total'>Active : $activeusercount</span>
                                    <span class='total'>Inactive :$inactiveusercount</span>
                                </div>
                            </div>
                            </div>";
                    echo $output;
//$form = new assignrole_form(null,array('deptid'=>$deptid));


//echo get_string('programsanddepartments','local_collegestructure');
//echo $OUTPUT->heading($school->fullname);
$programs = $DB->get_records('local_costcenter', array('parentid' =>$id));
    $role_name = $DB->get_field('role','id',array('shortname'=>'organisationhead'));
    

//if (!$departments = $DB->get_records_sql("SELECT * FROM {local_department} WHERE visible=1 AND schoolid = $id ORDER by visible DESC")) {
//    $departments = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $id GROUP BY sd.deptid ORDER BY d.visible DESC");
//}
//print_object($programs);

echo '<div id="firstpane" class="menu_list programs_list">';
//print_object($deptid);
foreach ($programs as $program) {
    $url =$CFG->wwwroot.'/local/costcenter/costcenterview.php?id='.$id;
    $form = new assignrole_form($url,array('deptid'=>$program->id,'roleid'=>$role_name, 'deptusers'=>1));
    //print_object($record);
    $curriculums = $DB->get_records('local_costcenter', array('parentid' =>$program->id));
    //if(empty($curriculums)){
    //    continue;
    //}
    // print_object($curriculums);
    // $cur_info = empty($curriculums) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('curriculum', 'local_curriculum') . 's)</span>' : '';
    //$level = $program->programlevel == 1 ? 'Undergraduate' : 'Graduate';
    //$visible = $program->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
    // print_object($program->fullname);
    $department_actions = '';
    if (has_capability('local/costcenter:manage', $systemcontext)) {
        $department_actions .= html_writer::link(new moodle_url('/local/costcenter/index.php', array('id' => $program->id, 'sesskey' => sesskey())),
                                 html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
       // $action .= html_writer::link(new moodle_url('#', array('id' => 'demo'.$program->id, 'sesskey' => sesskey())),
                                // html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
  
     $department_actions .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/assignroles'), 'title' => get_string('assignroles'), 'alt' => get_string('assignroles'), 'class' => 'iconsmall',
                                                               'id'=>'accordion', 'onclick'=>'dept_show_form("dept", '.$program->id.')', 'class'=>'accordian_trigger iconsmall','data-toggle'=>'collapse', 'data-target'=>"#demo$program->id"));
    }
    
    echo '<p class="menu_head menu_program"><b>' . get_string('department', 'local_costcenter') . ' : </b>' . $program->fullname.'<span style="float: right;">'.$department_actions.'</span></p>';

     echo '<div class="menu_body">';
 
   echo'
    <div class="assign_form assign_form_dept_'.$program->id.'">';
  $form->display();
  echo '</div>';
  
   
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
  
//  echo'<div id="demo'.$program->id.'" class="collapse">';
//$form->display();
//
//echo '</div>';
    foreach ($curriculums as $curriculum) {
        $form = new assignrole_form($url,array('deptid'=>$curriculum->id,'roleid'=>$role_name, 'deptusers'=>1));
        //print_object($curriculum->id);
       // $visible = $curriculum->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
        if ($curriculum->id) {
            $plans = $DB->get_records('local_costcenter', array('parentid' => $curriculum->id), 'visible DESC');
            //print_object($plan);
            $plan_info = empty($plans) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cuplan', 'local_curriculum') . 's)</span>' : '';
            $curriculum_actions = '';
            if (has_capability('local/costcenter:manage', $systemcontext)) {
                $curriculum_actions .= html_writer::link(new moodle_url('/local/costcenter/index.php', array('id' => $curriculum->id, 'sesskey' => sesskey())),
                                         html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
                //$curriculum_actions .= html_writer::link(new moodle_url('/local/costcenter/index.php', array('id' => $curriculum->id, 'sesskey' => sesskey())),
                //                         html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
               
                $curriculum_actions .= html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/assignroles'), 'title' => get_string('assignroles'), 'alt' => get_string('assignroles'), 'class' => 'iconsmall',
                                                               'id'=>'accordion', 'onclick'=>'dept_show_form("subdept", '.$curriculum->id.')','data-toggle'=>'collapse', 'class'=>'accordian_trigger iconsmall', 'data-target'=>"#demo$curriculum->id"));
            }
            echo '<p class="menu_head menu_curriculum"><b>' . get_string('subdepartment', 'local_costcenter') . ' : </b>' . $curriculum->fullname . '<span style="float: right;">'.$curriculum_actions.'</span> </p>';
            echo '<div class="menu_body">';
            
            echo' 
            <div class="assign_form assign_form_subdept_'.$curriculum->id.'">';
          $form->display();
          echo '</div>';
            
            echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
            foreach ($plans as $plan) {
               // print_object($plan->fullname);
               // $visible = $plan->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
                $plan_courses = $DB->get_records('local_costcenter', array('parentid' => $plan->id));
                //$course_info = empty($plan_courses) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
                $plan_actions = '';
                if (has_capability('local/costcenter:manage', $systemcontext)) {
                    $plan_actions .= html_writer::link(new moodle_url('/local/costcenter/index.php', array('id' => $plan->id, 'sesskey' => sesskey())),
                                             html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
                    //$plan_actions .= html_writer::link(new moodle_url('/local/costcenter/index.php', array('id' => $curriculum->id, 'sesskey' => sesskey())),
                    //                         html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
                }
                echo '<p class="menu_head menu_plan" ><b>' . get_string('subsubdepartment', 'local_costcenter') . ' : </b>' . $plan->fullname . $course_info . $visible . '<span style="float: right;">'.$plan_actions.'</span> </p>';
                echo '<div class="menu_body">';
                echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
                //foreach ($plan_courses as $plan_course) {
                //    //list_from_courses($plan_course->courseid);
                //}
              
                echo "</div>"; //.firstpane
                echo "</div>"; //.menu_body

            }
            echo "</div>"; //.firstpane
            echo "</div>"; //.menu_body
        } else {
            //$cur_courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $curriculumid, 'planid' => 0));
            //$course_info = empty($cur_courses) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
            //echo '<p class="menu_head menu_curriculum"><b>' . get_string('curriculum', 'local_curriculum') . ': </b>' . $curriculum->fullname . ' - ' . get_string('cuplan', 'local_cobaltcourses') . ' Not Enabled ' . $course_info . $visible . '</p>';
            //echo '<div class="menu_body">';
            //echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
            //foreach ($cur_courses as $cur_course) {
            //    list_from_courses($cur_course->courseid);
            //}
            //echo "</div>"; //.firstpane
            //echo "</div>"; //.menu_body
        }
       
    }
    
    
    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body
}
echo '</div>'; //.menu_list


//
//echo '<div id="firstpane" class="menu_list departments_list">';
//foreach ($departments as $department) {
//    $courses = $DB->get_records('local_cobaltcourses', array('departmentid' => $department->id), 'visible DESC');
//    $course_info = empty($courses) ? ' <span style="float: right;color:#FA5D08;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
//    $inst_list = $DB->get_records_sql('select ld.* from {local_dept_instructor} ld, {user} u where  ld.departmentid='.$department->id.' and ld.instructorid=u.id and u.deleted=0');
//    $inst_info = empty($inst_list) ? ' <span style="float: right;color:#FA5D08;">(No ' . get_string('instructor', 'local_clclasses') . 's Assigned)</span>' : '';
//    echo '<p class="menu_head menu_department"><b>' . get_string('department', 'local_departments') . ': </b>' . $department->fullname . $course_info . $visible . '</p>';
//    echo '<div class="menu_body">';
//    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
//    echo '<p class="menu_head menu_instlist"><b>' . get_string('instructor', 'local_clclasses') . 's </b>' . $inst_info . '</p>';
//    echo '<div class="menu_body">';
//    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
//    foreach ($inst_list as $inst) {
//        $user = $DB->get_record('user', array('id' => $inst->instructorid));
//        echo '<p class="menu_head menu_inst"><b>' . get_string('instructor', 'local_clclasses') . ': </b>' . fullname($user) . '</p>';
//    }
//    echo "</div>"; //.firstpane
//    echo "</div>"; //.menu_body
//
//    echo '<p class="menu_head menu_courselist"><b>' . get_string('cobaltcourses', 'local_cobaltcourses') . ' </b>' . $course_info . '</p>';
//    echo '<div class="menu_body">';
//    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
//    foreach ($courses as $course) {
//        list_from_courses($course->id);
//    }
//    echo "</div>"; //.firstpane
//    echo "</div>"; //.menu_body
//
//    echo "</div>"; //.firstpane
//    echo "</div>"; //.menu_body
//}
echo '</div>';

if($from_form = $form->get_data()){
    //print_object($from_form);
    $data = data_submitted();
     //print_object($data);exit;
     
    $record = new stdClass();
    $record->userid         = implode(',',$data->users);
    $record->costcenterid = $from_form->deptid;
    $record->roleid = $role_name;
    $record->timecreated = time();
    $lastinsertid = $DB->insert_record('local_costcenter_permissions', $record);
    redirect($CFG->wwwroot.'/local/costcenter/costcenterview.php?id='.$id);
    }


echo $OUTPUT->footer();
