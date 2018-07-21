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
/* Learning Plan Block
 * This plugin serves as a database and plan for all learning activities in the organziation, 
 * where such activities are organized for a more structured learning program.
 * @package local
 * @sub package learning plan
 * @author: Syed HameedUllah
 * @copyright  Copyrights © 2016
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/mod/facetoface/lib.php');
require_once ("$CFG->dirroot/local/costcenter/targetaudience/targetaudience.php");

//$PAGE->requires->js('/mod/facetoface/js/custom.js');
$PAGE->requires->js('/local/costcenter/targetaudience/targetaudience.js');


//$PAGE->requires->js('/local/learningplan/js/custom.js');
// Add Learning Plans.
class learningplan_form extends moodleform {

    public function definition() {
        global $USER,$DB;
        $mform = $this->_form;
		
        $id = $this->_customdata['id'];
		$org = $this->_customdata['costcenterid'];
		$dept = $this->_customdata['department'];
		$sub_dept = $this->_customdata['subdepartment'];
		$sub_sub_dept = $this->_customdata['sub_sub_department'];
        if (!isset($errors)){
            $errors = array();
        }
		
        $mform->addElement('text', 'name', get_string('learning_plan_name', 'local_learningplan'));
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);
		
		$mform->addElement('text', 'shortname', get_string('shortname'), 'maxlength="100" size="20"');
		if($id < 0 || empty($id)){
		$mform->addRule('shortname', get_string('missing_plan_shortname', 'local_learningplan'), 'required', null, 'client');
		}
		if($id > 0){
			$mform->disabledIf('shortname');
		}
        $mform->setType('shortname', PARAM_TEXT);
		
		$options = array();
		$options[null] = 'Select';
		$options['1'] = 'Core Courses';
		$options['2'] = 'Elective Courses';
	    $mform->addElement('select', 'learning_type', get_string('learning_plan_type', 'local_learningplan'), $options);
        $mform->addRule('learning_type', null, 'required', null, 'client');
        $mform->setType('learning_type', PARAM_TEXT);
		
		$choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('visible'), $choices);
        $mform->setDefault('visible', 1);
		
		$needapprovals=array();
        $needapprovals[] = $mform->createElement('radio', 'needapproval','', 'Yes',1);
        $needapprovals[] = $mform->createElement('radio', 'needapproval','', 'No',0);
        $mform->addGroup($needapprovals, 'needapproval','Need Approval', array(' '), false);  
		
		
        $attributes = array('rows' => '8', 'cols' => '40');
        //$mform->addElement('textarea', 'description', get_string('description'), $attributes);
		$mform->addElement('editor','description', get_string('description'), null, $attributes);
        $mform->setType('description', PARAM_RAW);
        //$mform->setType('description', PARAM_TEXT);
		
	    $mform->addElement('text', 'credits', get_string('credits','local_learningplan'));
        $mform->addRule('credits', null, 'required', null, 'client');
		$mform->addRule('credits', get_string('numeric','local_learningplan'), 'numeric', null, 'client');
        $mform->setType('credits', PARAM_INT);
		
//		$mform->addElement('text', 'location', get_string('planlocation','local_learningplan'));
//      $mform->setType('location', PARAM_TEXT);
		/*commented By Ravi_369*/        
		//$mform->addElement('date_selector', 'startdate', get_string('planstartdate', 'local_learningplan'),array('optional'=>false));
        
		//$mform->addElement('date_selector', 'enddate', get_string('planenddate','local_learningplan'),array('optional'=>false));
		
		$attributes = array('rows' => '8', 'cols' => '40');
        //$mform->addElement('textarea', 'objective', get_string('planobjective', 'local_learningplan'), $attributes);
		$mform->addElement('editor','objective', get_string('planobjective', 'local_learningplan'), null, $attributes);
        $mform->setType('objective', PARAM_RAW);
        //$mform->setType('objective', PARAM_TEXT);
		
		$systemcontext = context_system::instance();
		if (is_siteadmin($USER->id) || has_capability('local/assign_multiple_departments:manage', $systemcontext)) {
			$sql = "select id,fullname from {local_costcenter} where visible =1 and parentid IN(0,1)";
			$costcenters = $DB->get_records_sql($sql);
        } else {
			
        }
		
		$mform->addElement('filemanager', 'summaryfile', 'Learning path summary file', null,array('maxbytes' => $maxbytes, 'accepted_types' => ['.jpg','.jpeg','.png','.gif']));
		//if($id <= 0){
		//	
		//	$mform->addElement('header', 'target', get_string('target_audience','local_learningplan'));
		//	if(is_siteadmin()){
		//		if($data=data_submitted()){
		//			
		//			if($data->cancel=='Cancel'){
		//			$return_url = new moodle_url('/local/learningplan/index.php');
		//			redirect($return_url);
		//			}
		//
		//			$options_dept=array();
		//			$subdepartment_list=implode(',',$data->department);
		//			$sql="select id,fullname from {local_costcenter} where id IN($subdepartment_list)";
		//			$sub_dep=$DB->get_records_sql($sql);
		//			foreach($sub_dep as $depart){
		//				$options_dept[$depart->id]=$depart->fullname;
		//			}
		//		
		//			
		//			$options_sub_dept=array();
		//			$subdepartment_list=implode(',',$data->subdepartment);
		//			$sql="select id,fullname from {local_costcenter} where id IN($subdepartment_list)";
		//			$sub_dep=$DB->get_records_sql($sql);
		//			foreach($sub_dep as $depart){
		//				$options_sub_dept[$depart->id]=$depart->fullname;
		//			}
		//			 
		//			$options_sub_sub_dept=array();
		//			$subdepartment_list=implode(',',$data->sub_sub_department);
		//			$sql="select id,fullname from {local_costcenter} where id IN($subdepartment_list)";
		//			$sub_dep=$DB->get_records_sql($sql);
		//			foreach($sub_dep as $depart){
		//				$options_sub_sub_dept[$depart->id]=$depart->fullname;
		//			}
		//		$training_bands = get_training_bands();
		//		
		//		}
		//		
		//		$array=array();
		//		foreach ($costcenters as $scl) {
		//			$key = $scl->id;
		//			$value = $scl->fullname;
		//			$array[$scl->id]=$scl->fullname;
		//		}
		//		$select = $mform->addElement('select', 'costcenterid', get_string('organization', 'local_users'), $array);
		//		$mform->addRule('costcenterid', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		//$displaylist=array(null=>'--Select Department');
		//		$select=$mform->addElement('select', 'department', get_string('department'),$options_dept);
		//		$mform->addRule('department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		//$displaylist=array(null=>'--Select Sub Department--');
		//		$select=$mform->addElement('select', 'subdepartment', get_string('subdepartment','local_users'), $options_sub_dept);
		//		$mform->addRule('subdepartment', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		//$displaylist=array(null=>'--Select Sub Sub Department--');
		//		$select=$mform->addElement('select', 'sub_sub_department', get_string('sub_sub_department','local_users'), $options_sub_sub_dept);
		//		$mform->addRule('sub_sub_department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		
		//	    $displaylist=array(null=>'--Select--');
		//		$selectband = $mform->addElement('select', 'bands',get_string('employeeband', 'facetoface'), $training_bands, array('class'=>'assign_training_at','data-placeholder'=>'--Select--', 'size'=>40, 'width' => '100%'));
		//		$mform->addRule('bands', null, 'required', null, 'client');
		//		$selectband->setMultiple(true);
		//	}else{
		//		$user_dept=$DB->get_field('local_userdata','costcenterid', array('userid'=>$USER->id));
		//				
		//		//$select=$mform->addElement('select', 'costcenterid',get_string('costcenter'),$user_deptl,$disablededit,array('data-placeholder'=>'--Select costcenter--'));
		//		$mform->addElement('hidden', 'costcenterid', null);
		//		$mform->setType('costcenterid', PARAM_ALPHANUM);
		//		$mform->setConstant('costcenterid', $user_dept);
		//		$department = find_departments_list($user_dept);
		//		foreach($department as $depart){
		//			$options_dept[$depart->id]=$depart->fullname;
		//		}
		//		
		//		
		//		$displaylist=array(null=>'--Select Department--');
		//		//$disp=$displaylist+$options_dept;
		//		$select=$mform->addElement('select', 'department', get_string('department'),$options_dept);
		//		$mform->addRule('department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		if($data=data_submitted()){
		//			//print_object($data);exit;
		//			if($data->cancel=='Cancel'){
		//			$return_url = new moodle_url('/local/learningplan/index.php');
		//			redirect($return_url);
		//			}
		//			$options_sub_dept=array();
		//			$subdepartment_list=implode(',',$data->subdepartment);
		//			
		//			$sql="select id,fullname from {local_costcenter} where id IN($subdepartment_list)";
		//			$sub_dep=$DB->get_records_sql($sql);
		//			foreach($sub_dep as $depart){
		//			$options_sub_dept[$depart->id]=$depart->fullname;
		//			}
		//		     
		//			$options_sub_sub_dept=array();
		//			$subdepartment_list=implode(',',$data->sub_sub_department);
		//			$sql="select id,fullname from {local_costcenter} where id IN($subdepartment_list)";
		//			$sub_dep=$DB->get_records_sql($sql);
		//			foreach($sub_dep as $depart){
		//			$options_sub_sub_dept[$depart->id]=$depart->fullname;
		//			}
		//		
		//		
		//		}
		//		//$displaylist=array(null=>'--Select Sub Department--');
		//		$select=$mform->addElement('select', 'subdepartment', get_string('subdepartment','local_users'), $options_sub_dept);
		//		$mform->addRule('subdepartment', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		//$displaylist=array(null=>'--Select Sub Sub Department--');
		//		$select=$mform->addElement('select', 'sub_sub_department', get_string('sub_sub_department','local_users'), $options_sub_sub_dept);
		//		$mform->addRule('sub_sub_department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		
		//		$training_bands = get_training_bands();
		//		$selectband = $mform->addElement('select', 'bands',get_string('employeeband', 'facetoface'), $training_bands, array('class'=>'assign_training_at','data-placeholder'=>'--Select--', 'size'=>40, 'width' => '100%'));
		//		$mform->addRule('bands', null, 'required', null, 'client');
		//		$selectband->setMultiple(true);
		//	}
		//}else{
		//	$mform->addElement('header', 'target', get_string('target_audience','local_learningplan'));
		//	$set_data = $DB->get_record('local_learningplan', array('id' => $id));
		//	//print_object($set_data);
		//		$mform->addElement('hidden', 'id');
		//		$mform->setType('id', PARAM_INT);
		//		$mform->setConstant('id', $set_data->id);
		//	if(is_siteadmin()){
		//		$depart=$DB->get_records('local_costcenter');
		//		$options=array(null=>'--Select Departments--');
		//		foreach($depart as $depatement){
		//			$options[$depatement->id]=$depatement->fullname;
		//		}
		//		$select = $mform->addElement('select', 'costcenterid', get_string('costcenter'),$options,array('data-placeholder'=>'--Select costcenter--'));
		//		$mform->addRule('costcenterid', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		$select->setSelected(''.$set_data->costcenter.'');
		//		
		//		//$user_dept = $set_data->costcenter;
		//		
		//		$department = find_departments_list($set_data->costcenter, NULL);
		//		$options_dept = array();
		//		foreach($department as $depart){
		//			$options_dept[$depart->id]=$depart->fullname;
		//		}
		//		$select = $mform->addElement('select', 'department', get_string('department'), $options_dept);
		//		$mform->addRule('department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		$select->setSelected(''.$set_data->department.'');
		//		//$costcenter=$DB->get_field('local_costcenter','category',array('id'=>$get_coursedetails->costcenterid));
		//		$subdepartment = find_subdepartments_list($set_data->department, NULL);
		//		//$options_sub_dept_null=array(null=>'--SELECT--');
		//		foreach($subdepartment as $depart){
		//			$options_sub_dept[$depart->id]=$depart->fullname;
		//		}
		//		$select=$mform->addElement('select', 'subdepartment', get_string('subdepartment','local_users'), $options_sub_dept);
		//		$mform->addRule('subdepartment', null, 'required', null, 'client');
		//		$select->setSelected(''.$set_data->subdepartment.'');//
		//		$select->setMultiple(true);
		//		$subsubdepartment=find_subsubdepartments_list($set_data->subdepartment, '');
		//		foreach($subsubdepartment as $depart){
		//			$options_sub_sub_dept[$depart->id]=$depart->fullname;
		//		}
		//		
		//		$select=$mform->addElement('select', 'sub_sub_department', get_string('sub_sub_department','local_users'), $options_sub_sub_dept);
		//		$mform->addRule('sub_sub_department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		$select->setSelected(''.$set_data->subsubdepartment.'');
		//		
		//		$training_bands = get_training_bands();
		//		$selectband = $mform->addElement('select', 'bands',get_string('employeeband', 'facetoface'), $training_bands, array('class'=>'assign_training_at','data-placeholder'=>'--Select--', 'size'=>40, 'width' => '100%'));
		//		$mform->addRule('bands', null, 'required', null, 'client');
		//		$selectband->setSelected(''.$set_data->band.'');
		//		$selectband->setMultiple(true);
		//	}else{
		//		$user_dept=$DB->get_field('local_userdata','costcenterid', array('userid'=>$USER->id));
		//		
		//		//$select=$mform->addElement('select', 'costcenterid',get_string('costcenter'),$user_deptl,$disablededit,array('data-placeholder'=>'--Select costcenter--'));
		//		$mform->addElement('hidden', 'costcenterid', null);
		//		$mform->setType('costcenterid', PARAM_ALPHANUM);
		//		$mform->setConstant('costcenterid', $user_dept);
		//		
		//		$department = find_departments_list($user_dept, '');
		//		$options_dept = array();
		//		foreach($department as $depart){
		//			$options_dept[$depart->id]=$depart->fullname;
		//		}
		//		$select = $mform->addElement('select', 'department', get_string('department'), $options_dept);
		//		$mform->addRule('department', null, 'required', null, 'client');
		//		$select->setSelected(''.$set_data->department.'');
		//		$select->setMultiple(true);
		//		//$costcenter=$DB->get_field('local_costcenter','category',array('id'=>$get_coursedetails->costcenterid));
		//		
		//		$subdepartment = find_subdepartments_list($set_data->department, NULL);
		//		//$options_sub_dept_null=array(null=>'--SELECT--');
		//		foreach($subdepartment as $depart){
		//			$options_sub_dept[$depart->id]=$depart->fullname;
		//		}
		//		$select=$mform->addElement('select', 'subdepartment', get_string('subdepartment','local_users'), $options_sub_dept);
		//		$mform->addRule('subdepartment', null, 'required', null, 'client');
		//		$select->setSelected(''.$set_data->subdepartment.'');//
		//		$select->setMultiple(true);
		//		
		//		
		//		$subsubdepartment=find_subsubdepartments_list($set_data->subdepartment, '');
		//		foreach($subsubdepartment as $depart){
		//			$options_sub_sub_dept[$depart->id]=$depart->fullname;
		//		}
		//		
		//		$select=$mform->addElement('select', 'sub_sub_department', get_string('sub_sub_department','local_users'), $options_sub_sub_dept);
		//		$mform->addRule('sub_sub_department', null, 'required', null, 'client');
		//		$select->setMultiple(true);
		//		$select->setSelected(''.$set_data->subsubdepartment.'');
		//		
		//		
		//		$training_bands = get_training_bands();
		//		$selectband = $mform->addElement('select', 'bands',get_string('employeeband', 'facetoface'), $training_bands, array('class'=>'assign_training_at','data-placeholder'=>'--Select--', 'size'=>40, 'width' => '100%'));
		//		$mform->addRule('bands', null, 'required', null, 'client');
		//		$selectband->setMultiple(true);
		//	}
		//}
		$mform->addElement('header', 'targetaudience', get_string('targetaudience', 'facetoface'));
        $target_audience = new targetaudience();
		if($id>0){
			
			
            $lep=$DB->get_record('local_learningplan',array('id'=>$id));
			
            $update =true;
            $organizationid =$lep->costcenter;
            
            $existband =$lep->band;     
            $existdepartment =$lep->department;
            $existsubdepartment =$lep->subdepartment;
            $exist_sub_subdepartment=$lep->subsubdepartment;
			
        }else{
            $update =false;
            $organizationid =null;
          //  $existlocation=null;
            $existband=null;
            $existdepartment=null;
            $existsubdepartment=null;
            $exist_sub_subdepartment=null;
        }
        
        $target_audience->get_organizationfield($mform,'costcenter', array('departmentid','training_at','bands','subdepartmentid'));
        
      //  $target_audience->get_employee_locationfield($mform, 'training_at', $update, $organizationid, $existlocation,'organizationid');      
        
        $target_audience->get_employeeband_field($mform, 'bands', $update, $organizationid, $existband,'organizationid');
        
        $target_audience->get_department_field($mform, 'department', $update, $organizationid, $existdepartment, false, array('subdepartmentid'));      
        $target_audience->get_subdepartment_field($mform, 'subdepartment', $update, $existdepartment, $existsubdepartment);

        $target_audience->get_sub_subdepartment_field($mform, 'subsubdepartment', $update, $existdepartment, $existsubdepartment, $exist_sub_subdepartment );
       
        
        $target_audience->requriedjs();
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
		$this->add_action_buttons();
       //$buttonarray = array();
       // $classarray = array('class' => 'form-submit');
       // //if ($returnto !== 0) {
       // //    $buttonarray[] = &$mform->createElement('submit', 'saveandreturn', get_string('savechangesandreturn'), $classarray);
       // //}
       // $buttonarray[] = &$mform->createElement('submit', 'saveanddisplay', get_string('savechangesanddisplay'), $classarray);
       // $buttonarray[] = &$mform->createElement('cancel');
       // $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
       // $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
       
        $errors = array();
		global $DB;
	    $errors = parent::validation($data, $files);
		if($data['enddate'] < $data['startdate']){
	        $errors['enddate'] = get_string('startdategreaterenddate','local_learningplan');
		}
        if ($lplan = $DB->get_record('local_learningplan', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
         
		    if (($data['id']=='0') || $lplan->id != $data['id']) {
				 
                 $errors['shortname'] = get_string('unameexists','local_learningplan');
				 
            }
        
		}
	
		return $errors;
    }
 //private function get_employeeband_field($mform, $usercontext,$facetofaceids){
 //       
 //        //---------------employee bands------------------------------------
 //       if(is_siteadmin() || has_capability('local/assign_multiple_departments:manage', $usercontext)){           
 //         //--- if facetofaceids it goes for updation part, based on selected organization employee location will be displayed 
 //           if(isset($facetofaceids)){
 //               if($facetofaceids->organizationid){
 //                   $training_bands = get_training_bands($facetofaceids->organizationid);                       
 //               }
 //               else if($facetofaceids->bands){
 //                  $bands=explode(',',$facetofaceids->bands);
 //                   foreach($bands as $key=>$value){                        
 //                       $training_bands[$value]=$value;
 //                   }  
 //               }
 //               else
 //               $training_bands = array();              
 //           }
 //           else
 //           $training_bands = array();
 //       }
 //       else{
 //           $training_bands = get_training_bands(); 
 //       }
 //       
 //       
 //       $selectband = $mform->addElement('select', 'bands',get_string('employeeband', 'facetoface'), $training_bands, array('class'=>'assign_training_at','data-placeholder'=>'--Select--', 'size'=>40, 'width' => '100%'));
 //       $selectband->setMultiple(true);
 //       //// ----only at the time of creation----
 //       //if(!isset($facetofaceids)){
 //       //   $mform->getElement('bands')->setSelected($training_bands);
 //       //}     
 //      
 //       
 //   } // end of employee band function
}
