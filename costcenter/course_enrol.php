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
 * List the tool provided 
 *
 * @package   mod
 * @subpackage  face2face
 * @copyright  2015 Rajut 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $DB, $OUTPUT,$USER,$CFG,$PAGE;
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

require_once($CFG->dirroot.'/mod/facetoface/department_job_designation.php');
require_once($CFG->dirroot.'/lib/enrollib.php');

require_once $CFG->dirroot.'/mod/facetoface/lib.php';

$enrolid      = required_param('enrolid', PARAM_INT);
$roleid       = optional_param('roleid', -1, PARAM_INT);


$instance = $DB->get_record('enrol', array('id'=>$enrolid, 'enrol'=>'manual'), '*', MUST_EXIST);
$course = $DB->get_record('course', array('id'=>$instance->courseid), '*', MUST_EXIST);

$context = context_course::instance($course->id, MUST_EXIST);
require_login();
$pageurl = new moodle_url('/local/costcenter/course_enrol.php');
 

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('standard');

$PAGE->set_heading(get_string("manual_enrolment", 'local_costcenter'));
$PAGE->set_title(get_string("manual_enrolment", 'local_costcenter'));


$pagenavurl = new moodle_url('/local/costcenter/courses.php');
	
$PAGE->navbar->ignore_active();

$PAGE->navbar->add('Course', new moodle_url($pagenavurl));

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
/*==Added select js and css for add/remove users to batch files by rajut (date:13-11-2015)=====*/
$PAGE->requires->js('/mod/facetoface/js/select2.full.js');
$PAGE->requires->css('/mod/facetoface/css/select2.min.css');
/*==Added select js and css for add/remove users to batch files by rajut (date:13-11-2015)=====*/
$PAGE->requires->js('/mod/facetoface/js/filter.js');
$PAGE->requires->css('/local/teammanager/css/select2.min.css');

echo $OUTPUT->header();


//<!--rajesh has written this code--> 
$iconimage=html_writer::empty_tag('img', array('src'=>$CFG->wwwroot.'/theme/clean/pix/small/addUser.png','size'=>'15px'));
echo "<h2 class='tmhead2'><div class='iconimage'>".$iconimage."</div>".get_string("manual_enrolment", 'local_costcenter')."</h2>";
  //<!--code end here-->

$data=data_submitted();

if(!empty($data)){
		require_once $CFG->dirroot.'/group/lib.php';

    $fromform=new stdClass();
    if($data->submit_users=="<< Add Users"){
      $add_users=$data->remove_users;

      foreach($add_users as $add_user){
              $manual = enrol_get_plugin('manual');
             $studentrole = $DB->get_record('role', array('shortname'=>'student'));
			 if($instance){
              enroll_users_tocourse($add_user,$instance,$studentrole,$manual);
			 }
      }
    }elseif($data->submit_users=="Remove Users >>"){
      $remove_users=$data->add_users;
 
      
      foreach($remove_users as $remove_user){
		
        $manual = enrol_get_plugin('manual');
        $studentrole = $DB->get_record('role', array('shortname'=>'student'));
        if($instance){
           $timeend='';
		   $manual->unenrol_user($instance,$remove_user, $studentrole->id,time(),$timeend);

        }
      }
    }
}
$actionpage =$CFG->wwwroot.'/local/costcenter/course_enrol.php?enrolid='.$enrolid.'&roleid='.$roleid;
$mform=new department_job_desigantion_form($actionpage);
$agantmform=new agent_job_desigantion_form($actionpage);
if($mform->is_cancelled()){
}
$fromform= new stdClass();
$costcenter = null;
$jobfunction =null;
$designation=null;
$category=null;
$supervisor=0;
$zone=null;
$branch=null;
$role=0;
$level=null;
///
$agentcode=0;
$agentdesignation=null;
$state=null;
$city=null;
$agentbranchcode=0;
$agentsupervisor=0;
if ($fromform = $mform->get_data()) {
  $costcenter = $fromform->costcenter;   
  $jobfunction= $fromform->jobfunction;
  $designation = $fromform->designation;
  $category=$fromform->category;
  $supervisor = $fromform->supervisor;
  $zone = $fromform->zone;
  $branch = $fromform->branch;
  //$role = $fromform->role;
  $role=0;
  $level=$fromform->level;
}

if ($filteragent = $agantmform->get_data()) {
		//print_object($filteragent);
  $agentcode = $filteragent->agentcode; 
  $agentdesignation = $filteragent->designation;
  $state = $filteragent->state;
  $agentbranchcode=$filteragent->agentbranchcode;
  $agentsupervisor=$filteragent->supervisor;
  $city=$filteragent->city;
 
}
$userdepartment=$DB->get_record('local_userdata',array('userid'=>$USER->id));
$costcenterfullname=$DB->get_record('local_costcenter',array('id'=>$userdepartment->costcenterid));
$userroleshortname = $DB->get_record_sql("SELECT a.shortname FROM {role} a,{role_assignments} b,{context} c WHERE a.id=b.roleid and b.userid=$USER->id and b.contextid=c.id and c.contextlevel=10");
if((!is_null($costcenter) ||!is_null($jobfunction) ||!is_null($designation) || $supervisor!=0 || !empty($zone) || !empty($branch) || !empty($category) || $role!=0 || !empty($level)) && $mform->is_submitted()){
  $collapse =0;
}else{
  $collapse=1;
}
if((!is_null($agentdesignation) ||!is_null($city) ||!is_null($state) || $agentsupervisor!=0 || $agentbranchcode!=0 || $agentcode!=0) && $agantmform->is_submitted()){
  $collapse_agent =0;
}else{
  $collapse_agent=1;
}

print_collapsible_region_start('', 'department_job_desigantion-filter', get_string('employeesearch','facetoface'),false,$collapse);
$mform->display();
print_collapsible_region_end();

print_collapsible_region_start('', 'agent_job_desigantion-filter', get_string('agentsearch','facetoface'),false,$collapse_agent);
$agantmform->display();
print_collapsible_region_end();



if(!is_null($costcenter) ||!is_null($jobfunction) ||!is_null($designation) ||$supervisor!=0 || !empty($zone) || !empty($branch) || !empty($category) || $role!=0 || !empty($level)){
$select_to_users=select_to_users_course_enrol($enrolid,$costcenter,$jobfunction,$designation,$supervisor,$zone,$branch,$role,$category,$level,$USER->id);
$select_from_users=select_from_users_course_enrol($enrolid,$costcenter,$jobfunction,$designation,$supervisor,$zone,$branch,$role,$category,$level,$USER->id);
}
else if(!is_null($agentdesignation) ||!is_null($city) ||!is_null($state) || $agentsupervisor!=0 || $agentbranchcode!=0 || $agentcode!=0){

$select_to_users=select_to_agents_course_enrol($checklist='newusers',$enrolid,$agentcode,$agentdesignation,$state,$agentbranchcode,$agentsupervisor,$city);
$select_from_users=select_to_agents_course_enrol($checklist='enrolledusers',$enrolid,$agentcode,$agentdesignation,$state,$agentbranchcode,$agentsupervisor,$city);

}	
else{
$select_to_users=select_to_users_course_enrol($enrolid,$costcenter,$jobfunction,$designation,$supervisor,$zone,$branch,$role,$category,$level,$USER->id);
$select_from_users=select_from_users_course_enrol($enrolid,$costcenter,$jobfunction,$designation,$supervisor,$zone,$branch,$role,$category,$level,$USER->id);
}
?>

<div class="user_courses">
<form  method="post" name="form_name" id="form_id" class="form_class" >
  <fieldset>
 <ul class="button_ul">

   <li style="padding:18px;"><label>Search</label>
      <input id="textbox" type="text"/>
  </li>
 <li><input type="button" id="select_remove" name="select_all" value="Select All">
 <input type="button" id="remove_select" name="remove_all" value="Remove All">
 </li>
 
    <li><select name="add_users[]" id="select-from" multiple size="15">
      <?php
       echo '<optgroup label="Selected member list ('.count($select_from_users).') "></optgroup>';
      if(!empty($select_from_users)){
        
          foreach($select_from_users as $select_from_user){
            //if($select_from_user->id!=$USER->id){ 
             echo "<option   value=$select_from_user->id>$select_from_user->firstname $select_from_user->lastname ($select_from_user->idnumber)</option>";
			//}
		 }
      }else{
         echo '<optgroup label="None"></optgroup>';
      }
     ?>
    </select></li>
  </ul>
 
    <ul class="button_ul">
    <li><input type="submit" name="submit_users" value="<?php echo get_string("add_users", 'mod_facetoface') ?>" id="btn_add" ></li>                    
    <li><input type="submit" name="submit_users" value="<?php echo get_string("remove_users", 'mod_facetoface') ?>" id="btn_remove"></li>
    </ul>
    
    <ul class="button_ul">
    <li><input type="button" id="select_add" name="select_all" value="Select All">
    <input type="button" id="add_select" name="remove_all" value="Remove All">
    </li>
    <li><select name="remove_users[]" id="select-to" multiple size="15">
      <?php
      echo '<optgroup label="Available member list ('.count($select_to_users).') "></optgroup>';
      if(!empty($select_to_users)){
      
        foreach($select_to_users as $select_to_user){	
           //if($select_to_user->id!=$USER->id){ 
              echo "<option  value=$select_to_user->id>$select_to_user->firstname $select_to_user->lastname ($select_to_user->idnumber)</option>";
		   //}
	   }
     }else{
       echo '<optgroup label="None"></optgroup>';
     }
    ?>
     </select></li>
  </ul>
 
  </fieldset>
</form>
</div>
<script>
//*=========================add select=================*//  
$('#btn_add').prop('disabled', true);
  $('#select-to').on('change', function() {
     if(this.value!=''){
      $('#btn_add').prop('disabled', false);
      $('#btn_remove').prop('disabled', true);
     }else{
      $('#btn_add').prop('disabled', true);
    }
})
$('#select_add').click(function() {
         $('#select-to option').prop('selected', true);
          $('#btn_remove').prop('disabled', true);
         $('#btn_add').prop('disabled', false);
    });
$('#add_select').click(function() {
         $('#select-to option').prop('selected',false);
         $('#btn_remove').prop('disabled', true);
         $('#btn_add').prop('disabled', true);
    }); 
//$("#btn_add").click(function() {
// $("#form_id").trigger('submit');
//});

//*=========================remove select=================*//
$('#btn_remove').prop('disabled', true);
  $('#select-from').on('change', function() {
     if(this.value!=''){
      $('#btn_remove').prop('disabled', false);
      $('#btn_add').prop('disabled', true);
     }else{
      $('#btn_remove').prop('disabled', true);
    }
})
$('#select_remove').click(function() {
         $('#select-from option').prop('selected', true);
         $('#btn_add').prop('disabled', true);
         $('#btn_remove').prop('disabled', false);
    });
$('#remove_select').click(function() {
         $('#select-from option').prop('selected', false);
         $('#btn_add').prop('disabled', true);
         $('#btn_remove').prop('disabled', true);
    });
//$("#btn_remove").click(function() {
// $("#form_id").trigger('submit');
//});
    jQuery.fn.filterByText = function(textbox, selectSingleMatch) {
        return this.each(function() {
            var select = this;
            var options = [];
            $(select).find('option').each(function() {
                options.push({value: $(this).val(), text: $(this).text()});
            });
            $(select).data('options', options);
            $(textbox).bind('change keyup', function() {
                var options = $(select).empty().data('options');
                var search = $(this).val().trim();
                var regex = new RegExp(search,"gi");
              
                $.each(options, function(i) {
                    var option = options[i];
                    if(option.text.match(regex) != null) {
                        $(select).append(
                           $('<option>').text(option.text).val(option.value)
                        );
                    }
                });
                if (selectSingleMatch ==true && $(select).children().length ==0) {
                    $(select).children().get(0).selected = true;
                }
            });            
        });
    };

    $(function() {
        $('#select-to').filterByText($('#textbox'), false);
         $('#select-from').filterByText($('#textbox'), false);
      
    });

</script>

<a href="<?php echo $CFG->wwwroot.'/local/costcenter/courses.php';?>"><span class='knowmore'">Continue</span></a>
<?php
    // $url=$CFG->wwwroot.'/mod/facetoface/view_sessions.php?sessiontype='.$sessiontype;
    //echo $OUTPUT->single_button($url,'Continue', '', array('class'=>'knowmore'));
echo $OUTPUT->footer();
?>
