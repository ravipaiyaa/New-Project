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
 * Manual user enrolment UI.
 *
 * @package    enrol_manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
ini_set('memory_limit', '-1');
require('../../config.php');
require_once($CFG->dirroot.'/local/costcenter/courselib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot.'/local/filterclass.php');
require_once($CFG->dirroot.'/local/notifications/lib.php');

global $CFG,$DB,$USER,$PAGE,$OUTPUT,$SESSION;
$enrolid      = required_param('enrolid', PARAM_INT);
$course_id      = optional_param('id', 0,PARAM_INT);
$roleid       = optional_param('roleid', -1, PARAM_INT);


$extendperiod = optional_param('extendperiod', 0, PARAM_INT);
$extendbase   = optional_param('extendbase', 3, PARAM_INT);

$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'manual'), '*', MUST_EXIST);

$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);
$context = context_course::instance($course->id, MUST_EXIST);

require_login($course);
$canenrol = has_capability('enrol/manual:enrol', $context);
$canunenrol = has_capability('enrol/manual:unenrol', $context);

// Note: manage capability not used here because it is used for editing
// of existing enrolments which is not possible here.

if (!$canenrol and !$canunenrol) {
    // No need to invent new error strings here...
    require_capability('enrol/manual:enrol', $context);
    require_capability('enrol/manual:unenrol', $context);
}

/*Department level restrictions */
require_once($CFG->dirroot.'/local/includes.php');
$userlist=new has_user_permission();
	
    $haveaccess=$userlist->access_courses_permission($course_id);
	
	if(!$haveaccess) {
		 redirect($CFG->wwwroot . '/local/error.php?id=2');
	}
/*Department level restrictions */


if ($roleid < 0) {
    $roleid = $instance->roleid;
}
$roles = get_assignable_roles($context);
$roles = array('0'=>get_string('none')) + $roles;

if (!isset($roles[$roleid])) {
    //Weird - security always first!
    $roleid = 0;
}

if (!$enrol_manual = enrol_get_plugin('manual')) {
    throw new coding_exception('Can not instantiate enrol_manual');
}
if (!$enrol_auto = enrol_get_plugin('auto')) {
    throw new coding_exception('Can not instantiate enrol_manual');
}
if (!$enrol_self = enrol_get_plugin('self')) {
    throw new coding_exception('Can not instantiate enrol_manual');
}

$instancename = $enrol_manual->get_instance_name($instance);

$PAGE->set_url('/local/costcenter/courseenrol.php', array('id'=>$course_id,'enrolid'=>$instance->id));
$PAGE->set_pagelayout('fullpage');
$PAGE->set_title($enrol_manual->get_instance_name($instance));
$PAGE->set_heading($course->fullname);
navigation_node::override_active_url(new moodle_url('/local/mass_enroll/mass_enroll.php', array('id'=>$course->id)));
$PAGE->requires->js('/local/teammanager/js/select2.full.js');
$PAGE->requires->js('/local/costcenter/js/enrollfilter.js');
//$PAGE->requires->js('/mod/facetoface/js/filter.js');
$PAGE->requires->css('/local/teammanager/css/select2.min.css');
$systemcontext = context_system::instance();
//Create the user selector objects.
$options = array('enrolid' => $enrolid, 'accesscontext' => $context);
//$filter_form = new filter_form(null,array('enrolid'=>$enrolid,'id'=>$course_id));
 
if(is_siteadmin()){
           $costcenter="";
                 }elseif(!is_siteadmin()  && has_capability('local/assign_multiple_departments:manage', $systemcontext)){
                 $costcenter="";
                 }else{
                 $costcenter=$DB->get_field('local_coursedetails','costcenterid',array('courseid'=>$course_id));
}
$data = data_submitted();

$actionpage = new moodle_url('/local/costcenter/courseenrol.php', array('id'=>$course_id, 'enrolid'=>$instance->id));
$filter_form=new custom_filter($actionpage);
  
$functions = array();

//idnumber
$label=get_string('empnumber', 'local_users');
$name="idnumber";
if(!empty($data)){
    if(!empty($data->idnumber) && is_array($data->idnumber)){
        $functions=$filter_form->get_allusers_employeeids($costcenter,$data->idnumber);
    }
}
$filter_form->filters($label,$name,$functions);

//email
$label=get_string('email', 'local_users');
$name="email";
if(!empty($data)){
    if(!empty($data->email) && is_array($data->email)){
        $functions=$filter_form->get_all_users_emails($costcenter,$data->email);
    }
}
$filter_form->filters($label,$name,$functions);

//costcenter
if (is_siteadmin($USER) || has_capability('local/assign_multiple_departments:manage', $systemcontext)) {
    $label=get_string('organization', 'local_users');
    $name="costcenter";
    if(!empty($data)){
        if(!empty($data->costcenter) && is_array($data->costcenter)){
            $functions=$filter_form->get_allcostcenters($data->costcenter);
        }
    }
    $filter_form->filters($label,$name,$functions);
}

//band
$label=get_string('band', 'local_users');
$name="band";
if(!empty($data)){
    if(!empty($data->band) && is_array($data->band)){
        $functions=$filter_form->get_allband_users($costcenter,$data->band);
    }
}
$filter_form->filters($label,$name,$functions);

//department
$label=get_string('departments', 'local_users');
$name="department";
if(!empty($data)){
    if(!empty($data->department) && is_array($data->department)){
        $functions=$filter_form->get_alldepartments($costcenter,$data->department);
    }
}
$filter_form->filters($label,$name,$functions);

//subdepartment
$label=get_string('sub_departments', 'local_users');
$name="subdepartment";
if(!empty($data)){
    if(!empty($data->subdepartment) && is_array($data->subdepartment)){
        $functions=$filter_form->get_allsubdepartments($costcenter,$data->subdepartment);
    }
}
$filter_form->filters($label,$name,$functions);

$label=get_string('sub-sub-departments', 'local_users');
$name="sub_sub_department";
if(!empty($data)){
    if(!empty($data->sub_sub_department) && is_array($data->sub_sub_department)){
        $functions=$filter_form->get_allsub_sub_departments($costcenter,$data->sub_sub_department);
    }
}
$filter_form->filters($label,$name,$functions);
  
$label=get_string('designation', 'local_users');
$name="designation";
if(!empty($data)){
    if(!empty($data->designation) && is_array($data->designation)){
        $functions = $filter_form->get_employeedesignation($costcenter,$data->designation);
    }
}
$filter_form->filters($label,$name,$functions);

$name="enrolid";
$value=$enrolid;
$filter_form->hidden($name,$value);
  
$name="id";
$value=$course_id;
$filter_form->hidden($name,$value);

$filter_form->buttonsub();

//for get_data from filters
//$data = $filter_form->get_data();
        
$SESSION->costcenterenrol = array();
if($filter_form->is_cancelled()){
    redirect($PAGE->url);    
}

if(!empty($data->costcenter)){
    $implodecostcenters = implode(',', $data->costcenter);
    if($implodecostcenters){
        $filtercostcenters = $DB->get_records_sql("SELECT id,fullname FROM {local_costcenter} WHERE id IN ($implodecostcenters) AND parentid IN(0,1)");
        foreach($filtercostcenters as $value) {
            $data->department[$value->id] = "'".$value->fullname."'";
        }
        $costcenter = implode(',',$data->costcenter);
        $SESSION->costcenterenrol['department'] = $department;
    }
}else{
    $costcenter = '';
}
   
if(!empty($data->department)){
    $implodedepartments = implode(',', $data->department);
    if($implodedepartments){
        $filterdepartments = $DB->get_records_sql("SELECT id,fullname FROM {local_costcenter} WHERE id IN ($implodedepartments)");
        foreach($filterdepartments as $value) {
            $data->department[$value->id] = "'".$value->fullname."'";
        }
        $department = implode(',',$data->department);
        $SESSION->costcenterenrol['department'] = $department;
    }
}else{
    $department = '';
}
    
if(!empty( $data->subdepartment)){
    $implodedepartments = implode(',', $data->subdepartment);
    if($implodedepartments){
        $filterdepartments = $DB->get_records_sql("SELECT id,fullname FROM {local_costcenter} where id IN ($implodedepartments)");
        foreach($filterdepartments as $value) {
            $data->subdepartment[$value->id] = "'".$value->fullname."'";
        }
        $subdepartment = implode(',',$data->subdepartment);
        $SESSION->costcenterenrol['subdepartment'] = $subdepartment;
    }
}else{
    $subdepartment = '';
}
    
if(!empty( $data->sub_sub_department)){
    $implodedepartments = implode(',', $data->sub_sub_department);
    if($implodedepartments){
        $filterdepartments = $DB->get_records_sql("SELECT id,fullname FROM {local_costcenter} where id IN ($implodedepartments)");
        foreach($filterdepartments as $value) {
            $data->sub_sub_department[$value->id] = "'".$value->fullname."'";
        }
        $subsubdepartment = implode(',',$data->sub_sub_department);
        $SESSION->costcenterenrol['sub_sub_department'] = $subsubdepartment;
    }
}else{
    $subsubdepartment = '';
}
     
if(!empty( $data->band)){
    //$implodebands = implode(',', $data->band);
    //if($implodebands){
    //    $filterbands = $DB->get_records_sql("SELECT id,band FROM {local_userdata} where band!='' AND id IN ($implodebands)");
        foreach($data->band as $key => $value) {
            $data->band[$key] = "'".$value."'";
        }
        $band = implode(',',$data->band);
        $SESSION->costcenterenrol['band'] = $band;
    //}
}else{
    $band = '';
}
    
//for department filter
if(!empty($data->email)){
    $implodeemails = implode(',', $data->email);
    if($implodeemails){
        $filteremails = $DB->get_records_sql("SELECT id,email FROM {user} WHERE id IN ($implodeemails) ");
        foreach($filteremails as $value) {
            $data->email[$value->id] = "'".$value->email."'";
        }
        $email = implode(',',$data->email);
        $SESSION->costcenterenrol['email'] = $email;
    }
}else{
    $email = '';
}
    
if(!empty( $data->idnumber)){
    //$implodeidnumber = implode(',', $data->idnumber);
    //if($implodeidnumber){
        //$filterids = $DB->get_records_sql("SELECT idnumber FROM {user} WHERE idnumber IN ($implodeidnumber) ");
        foreach($data->idnumber as $key => $value) {
            $data->idnumber[$key] = "'".$value."'";
        }
        $idnumber = implode(',',$data->idnumber);
        $SESSION->costcenterenrol['idnumber'] = $idnumber;
    //}
}else{
    $idnumber = '';
}
// for grade filter
if(!empty( $data->name)){
    foreach($data->grade as $key => $value) {
        $data->name[$key] = "'".$value."'";
    }
    $uname = implode(',',$data->name);
    $SESSION->costcenterenrol['name'] = $uname;
}else{
    $uname = '';
}
    
// for designation filter
if(!empty( $data->designation)){
    $implodedesignations = implode(',', $data->designation);
    if($implodedesignations){
        $filterdesignations = $DB->get_records_sql("SELECT id,designation FROM {local_userdata} where id IN ($implodedesignations)");
        foreach($filterdesignations as $value) {
            $data->designation[$value->id] = "'".$value->designation."'";
        }
        $designation = implode(',',$data->designation);
        $SESSION->costcenterenrol['designation'] = $designation;
    }
}else{
    $designation = '';
}
    
if(!empty( $data->id)){
    $id=$DB->get_field('local_coursedetails','costcenterid',array('courseid'=>$data->id));
}
    
$id=$DB->get_field('local_coursedetails','costcenterid',array('courseid'=>$course_id));
if($id==1){
    if(!is_siteadmin()){
        $id=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id)) ;
    }
}

$potentialuserselector = new custom_enrol_manual_potential_participant('addselect', $options, $costcenter,$designation,$band,$department,$subdepartment,$subsubdepartment,$email,$idnumber,$uname,$id,$course_id);
$currentuserselector = new custom_enrol_manual_current_participant('removeselect', $options, $costcenter,$designation,$band,$department,$subdepartment,$subsubdepartment,$email,$idnumber,$uname,$course_id,$id);

// Build the list of options for the enrolment period dropdown.
$unlimitedperiod = get_string('unlimited');
$periodmenu = array();
for ($i=1; $i<=365; $i++) {
    $seconds = $i * 86400;
    $periodmenu[$seconds] = get_string('numdays', '', $i);
}
// Work out the apropriate default setting.
if ($extendperiod) {
    $defaultperiod = $extendperiod;
} else {
    $defaultperiod = $instance->enrolperiod;
}

// Build the list of options for the starting from dropdown.
$timeformat = get_string('strftimedatefullshort');
$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

// Enrolment start.
$basemenu = array();
if ($course->startdate > 0) {
    $basemenu[2] = get_string('coursestart') . ' (' . userdate($course->startdate, $timeformat) . ')';
}
$basemenu[3] = get_string('today') . ' (' . userdate($today, $timeformat) . ')' ;

//Process add and removes.
if ($canenrol && optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstoassign = $potentialuserselector->get_selected_users();
    if (!empty($userstoassign)) {
        foreach($userstoassign as $adduser) {
            /**********
            This code has been commented by Ravi_369 to make course open
            when the startdate is greater than or equal current date and added startdate = course startdate********/
            
            //switch($extendbase) {
            //    case 2:
            //        $timestart = $course->startdate;
            //        break;
            //    //case 3:
            //    //default:
            //    //    $timestart = $today;
            //    //    break;
            //}
            //
            //if ($extendperiod <= 0) {
            //    $timeend = 0;
            //} else {
            //    $timeend = $timestart + $extendperiod;
            //}
            
            /*****Here time startdate is course startdate ****/
            $timestart = $course->startdate;
            $timeend = 0;
            if($timestart==''){
                $timestart=0;
            }
        
            $enrol_manual->enrol_user($instance, $adduser->id, $roleid, $timestart, $timeend);
            /**********************Code Added By Ravi_369******************/
            $data=$DB->get_record('course',array('id'=>$course_id));
            $data_details=$DB->get_record('local_coursedetails',array('courseid'=>$course_id));
            $department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_details->costcenterid));
            $dataobj= new stdClass();
            $dataobj->course_title=$data->fullname;
            if($data_details->enrollstartdate && $data_details->enrollenddate){
                $dataobj->course_enrolstartdate=date('d M Y',$data_details->enrollstartdate);
                $dataobj->course_enrolenddate=date('d M Y',$data_details->enrollenddate);
            }else{
                $dataobj->course_enrolstartdate="N/A";
                $dataobj->course_enrolenddate="N/A";
            }
            if($data_details->coursecompletiondays){
                $dataobj->course_completiondays=$data_details->coursecompletiondays;
            }else{
                $dataobj->course_completiondays="N/A"; 
            }
            $dataobj->course_department=$department;
            if($data->summary){
                $dataobj->course_description=$data->summary;
            }else{
                $dataobj->course_description="N/A";
            }
            $url = new moodle_url($CFG->wwwroot.'/course/view.php',array('id'=>$data->id));
            $dataobj->course_link = html_writer::link($url, $data->fullname, array());
            $dataobj->course_url=$dataobj->course_link;
            $course_imgurl = get_course_summary_file($data);
            $dataobj->course_image= html_writer::img($course_imgurl, $data->fullname,array());
            $dataobj->enroluser_fullname="[ilt_enroluserfulname]";
            $dataobj->enroluser_email=$adduser->email;
            if($data_details->coursecreator!='' && $data_details->coursecreator!=0){
                $sql="select id, concat(firstname,' ', lastname) as fullname  from {user} where id=$data_details->coursecreator";   
                $creator=$DB->get_record_sql($sql);
                $dataobj->course_creator=$creator->fullname;
            }else{
                $dataobj->course_creator="N/A";
            }
            $touserid=$adduser->id;
            $fromuserid=2;
            $notifications_lib = new notifications();
            $emailtype='course_enrol';
            $notifications_lib->send_email_notification_course($emailtype, $dataobj, $touserid, $fromuserid,$course_id); 
             /**********************Code Ended By Ravi_369******************/
        }
        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();
        //TODO: log
    }
}

/*****************Code For Deleting the User or unenrolling the users By Ravi_369******************/

if ($canunenrol && optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
    $userstounassign = $currentuserselector->get_selected_users();
    if (!empty($userstounassign)) {
        foreach($userstounassign as $removeuser) {
            if($instance->enrol=='manual'){
                $manual=$enrol_manual->unenrol_user($instance, $removeuser->id);
            }
            $enroll="select * from {user_enrolments} ue join {enrol} e  where ue.enrolid=e.id and e.courseid=$course_id and ue.userid='".$removeuser->id."'";
            $data_auto=$DB->get_record_sql($enroll);
            
            if($data_auto->enrol=='auto'){
                $auto=$enrol_auto->unenrol_user($data_auto, $removeuser->id);
            }
            $enroll="select * from {user_enrolments} ue join {enrol} e  where ue.enrolid=e.id and e.courseid=$course_id and ue.userid='".$removeuser->id."'";
            $data_self=$DB->get_record_sql($enroll);
            
            if($data_self->enrol=='self'){
                $auto=$enrol_self->unenrol_user($data_self, $removeuser->id); 
            }
        }
        $potentialuserselector->invalidate_selected_users();
        $currentuserselector->invalidate_selected_users();
        //TODO: log
    }
}
/***************************End Of the Code For Deleting or for Unenroll of the users****************************/

$PAGE->requires->jquery();
$renderer = $PAGE->get_renderer('local_costcenter');
echo $OUTPUT->header();

//added by Raghuvaran
    echo $renderer->course_enroll($course_id);
//echo html_writer::link(new moodle_url('/local/costcenter/courses.php'),'Back',array('id'=>'back_tp_course'));

//echo $OUTPUT->heading($instancename);

//echo html_writer::link(new moodle_url('/local/mass_enroll/mass_enroll.php',array('id'=>$instance->courseid)),'Bulk enrollment',array('id'=>'bulk_enrollment'));

$addenabled = $canenrol ? '' : 'disabled="disabled"';
$removeenabled = $canunenrol ? '' : 'disabled="disabled"';
if(!empty($data->idnumber) || !empty($data->email) || !empty($data->costcenter) || !empty($data->band) || !empty($data->department) ||
   !empty($data->subdepartment) || !empty($data->sub_sub_department) || !empty($data->designation)){
    $collapse = false;
}else{
    $collapse = true;
}

print_collapsible_region_start('', 'batches-form', 'FILTER',false,$collapse);
$filter_form->display();
print_collapsible_region_end();
?>
<form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
  <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />

  <table summary="" class="roleassigntable generaltable generalbox boxaligncenter" cellspacing="0">
    <tr>
      <td id="existingcell">
          <div class="course_enroll_label_btns">
			   <span class="course_enroll_label_btns_span">Enrolled users</span>
			   <div style="float: left;">
				   <input type="button" id="select_remove_all" name="select_remove_all" value="Select all">
				   <input type="button" id="select_remove_none" name="select_remove_none" value="Select none">
			   </div>
		  </div>
          <?php $currentuserselector->display() ?>
      </td>
      <td id="buttonscell">
          <div id="addcontrols">
              <input name="add" <?php echo $addenabled; ?> id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.get_string('add'); ?>" title="<?php print_string('add'); ?>" /><br />
             <input type="hidden" name="roelid" value="5" />
<!--              <div class="enroloptions">

              <p><label for="menuroleid"><?php //print_string('assignrole', 'enrol_manual') ?></label><br />
              <?php //echo html_writer::select($roles, 'roleid', $roleid, false); ?></p>

              <p><label for="menuextendperiod"><?php //print_string('enrolperiod', 'enrol') ?></label><br />
              <?php //echo html_writer::select($periodmenu, 'extendperiod', $defaultperiod, $unlimitedperiod); ?></p>

              <p><label for="menuextendbase"><?php //print_string('startingfrom') ?></label><br />
              <?php //echo html_writer::select($basemenu, 'extendbase', $extendbase, false); ?></p>

              </div>-->
          </div>

          <div id="removecontrols">
              <input name="remove" id="remove" <?php echo $removeenabled; ?> type="submit" value="<?php echo get_string('remove').'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php print_string('remove'); ?>" />
          </div>
      </td>
      <td id="potentialcell">
		  <div class="course_enroll_label_right_btns">
			   <span class="course_enroll_label_btns_span">Not enrolled users</span>
			   <div style="float: left;">
				   <input type="button" id="select_add_all" name="select_add_all" value="Select all">
				   <input type="button" id="select_add_none" name="select_add_none" value="Select none">
			   </div>
		  </div>
          <?php $potentialuserselector->display() ?>
      </td>
    </tr>
  </table>
</div>
<div id="continue" style="float:none;text-align:center;"> 
             <input style="font-size: 12px;line-height: 18px;"type="button" onclick="redirect();" value="Finish">
          </div>
</form>
<?php
echo "
<script type='text/javascript'>

function redirect() {
	window.location = '".$CFG->wwwroot."/course/view.php?id=$course_id';

}

</script>
";

echo $OUTPUT->footer();
?>
<style type="text/css">
    .singleselect label {
    display: inline !important;
}
</style>
<script type="text/javascript">
    $('#select_add_all').click(function() {
        $('#addselect option').prop('selected', true);
    });
    $('#select_remove_all').click(function() {
        $('#removeselect option').prop('selected', true);
    });
    $('#select_add_none').click(function() {
        $('#addselect option').prop('selected', false);
    });
    $('#select_remove_none').click(function() {
        $('#removeselect option').prop('selected', false);
    });
        // $(".filter_drop").select2({
        // placeholder: "Select a State",
        //            allowClear: true
        //});
    //$("#region-main #id_designation").select2({
    //
    //});
    //$("#region-main #id_subsubdepartment").select2({
    //
    //});
    $("#region-main #id_ou_name").select2({
    
    });
    $("#region-main #id_batch").select2({
    
    });
    $("#region-main #id_position").select2({
    
    });
    
    //added by anil
    // for location filter
    $("#region-main #id_location").select2({
    
    });
    //$("#id_costcenter").select2({
    //    
    //});
    // for department filter
    //$("#region-main #id_department").select2({
    //
    //});
    // for grade filter
    $("#region-main #id_grade").select2({
    
    });
    courseenrolfilter(<?php echo $course_id?>,'course');
</script>