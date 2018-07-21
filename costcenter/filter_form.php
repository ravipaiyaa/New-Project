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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Cost center
 * @copyright  2015 Naveen<naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
//require_once($CFG->dirroot . '/local/lib.php');
//require_once($CFG->dirroot . '/local/users/lib.php');
class filter_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB;
        $mform = $this->_form;
        $enrol = $this->_customdata['enrolid'];
		$id= $this->_customdata['id'];
        if(is_siteadmin())
        $costcenterid = $DB->get_field_sql('select costcenterid from {local_coursedetails} as cd join  {enrol} as e ON cd.courseid = e.courseid where e.id='.$enrol.'');
        
        if(!is_siteadmin())
        $costcenterid = $DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
        
        $sql = 'select distinct(lp.id) as position_key,lp.fullname as position_value from {local_positions} as lp JOIN {local_userdata} as ud ON lp.id=ud.position where ud.position!=""';
       
	   // *********** commented by anil ********* //
//		$position_list = $DB->get_records_sql_menu($sql);
//        $mform->addElement('select', 'position', get_string('positions', 'local_costcenter'),  $position_list, array('multiple' => 'multiple','class'=>'filter_drop','data-placeholder'=>'--Select Position--'));
//        $mform->setType('position', PARAM_RAW);
//
//        $batch_list = $DB->get_records_sql_menu('select id as name_key,name as name_value from {cohort} where id in(select batchid from {local_costcenter_batch} )');
//        $mform->addElement('select', 'batch', get_string('batch', 'local_costcenter'),  $batch_list, array('multiple' => 'multiple','class'=>'filter_drop','data-placeholder'=>'--Select Batch--'));
//        $mform->setType('batch', PARAM_INT);
		
		
		//for department filter
        
        
       $costcenter=$DB->get_field('local_coursedetails','costcenterid',array('courseid'=>$id));
        //print_object($costcenter);exit;
		//$select_costcenter = $mform->addElement('select','department','Departments',get_alldepartments(),array('class'=>'costcenter','data-placeholder'=>'--Select Sub Department--'));
        //$mform->setType('departments',PARAM_RAW);
        //$select_costcenter->setMultiple(true);
        //
        // $select_costcenter = $mform->addElement('select','subdepartment','SubDepartments',get_allsubdepartments(),array('class'=>'costcenter','data-placeholder'=>'--Select Sub Sub Department--'));
        //$mform->setType('subdepartment',PARAM_RAW);
        //$select_costcenter->setMultiple(true);
        //$mform->setType('idnumber',PARAM_RAW);
        //$select_designation->setMultiple(true);
		$select_designation =$mform->addElement('select','idnumber','Employee ID',get_employeeids($costcenter),array('class'=>'idnumber','data-placeholder'=>'--Select Employee ID--'));
        $mform->setType('idnumber',PARAM_RAW);
        $select_designation->setMultiple(true);
		
		$select_designation =$mform->addElement('select','name','Name',get_employeename($costcenter),array('class'=>'idnumber','data-placeholder'=>'--Select Employee Name--'));
        $mform->setType('name',PARAM_RAW);
        $select_designation->setMultiple(true);
//		
//		$select_designation =$mform->addElement('select','designation','Designation',get_employeedesignation(),array('class'=>'idnumber','data-placeholder'=>'--Select Employee ID--'));
//        $mform->setType('designation',PARAM_RAW);
//        $select_designation->setMultiple(true);
		
		$select_designation =$mform->addElement('select','email','Email ID',get_all_users_emails_enroll($costcenter),array('class'=>'email','data-placeholder'=>'--Select Email ID--'));
        $mform->setType('email',PARAM_RAW);
        $select_designation->setMultiple(true);
		
		$select_costcenter = $mform->addElement('select','band','Band',get_allband($costcenter),array('class'=>'costcenter','data-placeholder'=>'--Select Band--'));
        $mform->setType('band',PARAM_RAW);
        $select_costcenter->setMultiple(true);
		
        
        
        
		 $select_costcenter = $mform->addElement('select','department','Departments',get_all_departments($costcenter),array('class'=>'costcenter','data-placeholder'=>'--Select Department--'));
        $mform->setType('departments',PARAM_RAW);
        $select_costcenter->setMultiple(true);
        //
         $select_costcenter = $mform->addElement('select','subdepartment','Sub Departments',get_all_subdepartments($costcenter),array('class'=>'costcenter','data-placeholder'=>'--Select Sub Department--'));
        $mform->setType('subdepartment',PARAM_RAW);
        $select_costcenter->setMultiple(true);
		
		   $select_costcenter = $mform->addElement('select','subsubdepartment','Sub Sub Departments',get_all_sub_sub_departments($costcenter),array('class'=>'costcenter','data-placeholder'=>'--Select Sub Sub Department--'));
        $mform->setType('subdepartment',PARAM_RAW);
        $select_costcenter->setMultiple(true);
		
		$skillset_list = $DB->get_records_sql_menu('select distinct(designation) as designation_key,designation as designation_value from {local_userdata} as ud where designation!="" and ud.costcenterid='.$costcenter.'');
        $mform->addElement('select', 'designation', get_string('designation', 'local_users'),  $skillset_list, array('multiple' => 'multiple','class'=>'filter_drop','data-placeholder'=>'--Select Designation--'));
        $mform->setType('designation', PARAM_RAW);
        //$mform->addElement('select', 'ou_name', get_string('ou_name', 'local_users'), get_all_ounames(), array('multiple' => 'multiple','class'=>'filter_drop','data-placeholder'=>'--Select Ou Name--'));
        //$mform->setType('ou_name', PARAM_RAW);

        $mform->addElement('hidden','enrolid');
        $mform->setType('enrolid',PARAM_INT);
		
        $mform->setDefault('enrolid',$enrol);
		
		$mform->addElement('hidden','id');
        $mform->setType('id',PARAM_INT);
		
        $mform->setDefault('id',$id);
		
        $this->add_action_buttons('true', 'Filter');
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
