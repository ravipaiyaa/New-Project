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
 * Auxiliary manual user enrolment lib, the main purpose is to lower memory requirements...
 *
 * @package    enrol_manual
 * @copyright  2010 Petr Skoda {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/selector/lib.php');
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');

/**
 * Enrol candidates.
 */
class custom_enrol_manual_potential_participant extends user_selector_base {
	protected $costcenter;
    protected $enrolid;
    protected $designation;
    protected $band;
	protected $department;
	protected $subdepartment;
	protected $subsubdepartment;
    protected $email;
    protected $idnumber;
	// added by anil
	protected $name;
	protected $uname;
	protected $course_id;
    protected $id;
    public function __construct($name, $options, $costcenter,$designation,$band,$department,$subdepartment,$subsubdepartment,$email,$idnumber,$uname,$id, $course_id) {
		$this->costcenter = $costcenter;
        $this->designation = $designation;
        $this->band = $band;
        $this->email = $email;
        $this->idnumber = $idnumber;
		//added by anil
		$this->uname = $uname;
		$this->id=$id;
		
		$this->department=$department;
		$this->subdepartment=$subdepartment;
		$this->subsubdepartment=$subsubdepartment;
        $this->enrolid  = $options['enrolid'];
		$this->course_id = $course_id;
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB,$USER;
      
        $systemcontext = context_system::instance();
        if(!is_siteadmin()  && !has_capability('local/assign_multiple_departments:manage', $systemcontext)){
			
              /***********This below code was written to work on ACADEMY search in BULK ENROL**********/
			if($this->id==1){
			$this->id =$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id)) ;
			}else{
			$this->id = $this->id;
			}
			/*if we get any issues please revoke the previous instance code and change by changing code*/
			/****************End of code************/
			$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >2 AND u.deleted=0 AND u.suspended=0 AND ud.costcenterid=$this->id";
						
			$email=$this->email;
			
	 	   if(!empty($email) && $email !== null){
				
					if(!empty($email) && $email=="'-1'"){
						
					$all_emails = $DB->get_records_sql_menu("SELECT distinct(email) as email_key, email as email_value FROM {user}");
					if($all_emails){
					$mails = implode('","',$all_emails);
					$userdata_sql.= ' AND u.email IN("'.$mails.'")'; 
					}
					}elseif(!empty($email) && $email !== "'-1'"){
						
					$userdata_sql.=" AND u.email IN({$email})";
			    }
		    
            }
			
			        $name=explode(",",$this->uname);
			        $nam1=implode("','",$name);
		   if(!empty($nam1) && $nam1!=='null' && $nam1 !=="-1"){
			    	$userdata_sql .=" AND u.firstname in('$nam1')";
	         }
			
			if(!empty($this->department) && $this->department!=='null'&& $this->department!== "'-1'"){
					
					$userdata_sql .=" AND ud.department in($this->department)";
			}else{
				
			}
			if(!empty($this->subdepartment) && $this->subdepartment!=='null'&& $this->subdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.subdepartment in($this->subdepartment)";
			}else{
				
			}
			if(!empty($this->subsubdepartment) && $this->subsubdepartment!=='null'&& $this->subsubdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.sub_sub_department in($this->subsubdepartment)";
			}else{
				
			}
			if(!empty($this->idnumber) && $this->idnumber!=='null'&& $this->idnumber!== "'-1'"){
					$userdata_sql .=" AND u.idnumber in($this->idnumber)";
			}else{
				
			}
			if(!empty($this->band) && $this->band!=='null' && $this->band!== "'-1'"){
			    	$userdata_sql .=" AND ud.band in($this->band)";
			}
				
			if(!empty($this->designation) && $this->designation!=='null' && $this->designation!=="'-1'"){
				    $userdata_sql .=" AND designation in($this->designation)";
			}
			
			
			$users_list = $DB->get_fieldset_sql($userdata_sql);
		        $enrolled_sql = "SELECT u.id FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id)
				 JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$this->course_id)
                ";
				
				$enrolled_users_list = $DB->get_fieldset_sql($enrolled_sql);
//print_object($enrolled_users_list);	
//print_object($users_list);	
				$users_list = array_diff($users_list, $enrolled_users_list);
//print_object($users_list);	
				 $users_list = implode(',',$users_list);
			 
			
			
        }else{
			/***********This below code was written to work on ACADEMY search in BULK ENROL**********/
			//if($this->id==1 || $this->id==''){
			//$string="ud.costcenterid>$this->id";
			//}else{
			//$string="ud.costcenterid=$this->id";
			//}
			//
			/*if we get any issues please revoke the previous instance code and change by changing code*/
			/****************End of code************/
			$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 ";
			//$userdata_sql = "select userid from {local_userdata} where costcenterid=$this->id";
			
			if(!empty($this->costcenter) && $this->costcenter!=='null'&& $this->costcenter!== "'-1'"){
				$userdata_sql .= " AND ud.costcenterid IN($this->costcenter)";
			}
			
			if(!empty($this->department) && $this->department!=='null'&& $this->department!== "'-1'"){
					
					$userdata_sql .=" AND ud.department in($this->department)";
			}else{
				
			}
			if(!empty($this->subdepartment) && $this->subdepartment!=='null'&& $this->subdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.subdepartment in($this->subdepartment)";
			}else{
				
			}
			if(!empty($this->subsubdepartment) && $this->subsubdepartment!=='null'&& $this->subsubdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.sub_sub_department in($this->subsubdepartment)";
			}else{
				
			}
			$email=$this->email;
	 	    if(!empty($email) && $email !== null){
					
					if(!empty($email) && $email=="'-1'"){
					$all_emails = $DB->get_records_sql_menu("SELECT distinct(email) as email_key, email as email_value FROM {user}");
					if($all_emails){
					$mails = implode('","',$all_emails);
					$userdata_sql.= ' AND u.email IN("'.$mails.'")'; 
					}
					}elseif(!empty($email) && $email !== "'-1'"){
					$userdata_sql.=" AND u.email IN({$email})";
					}
		    
              }
			   
			  
			  $name=explode(",",$this->uname);
			  $nam1=implode("','",$name);
			  
			  if(!empty($nam1) && $nam1!=='null' && $nam1 !=="-1"){
				$userdata_sql .=" AND u.firstname in('$nam1')";
	        }
			
			if(!empty($this->idnumber) && $this->idnumber!=='null'&& $this->idnumber!== "'-1'"){
				
				$userdata_sql .=" AND u.idnumber in($this->idnumber)";
			}else{
				
			}
			
			if(!empty($this->band) && $this->band!=='null' && $this->band!== "'-1'"){
				$userdata_sql .=" AND ud.band in($this->band)";
			}
				
			if(!empty($this->designation) && $this->designation!=='null' && $this->designation!=="'-1'"){
				$userdata_sql .=" AND designation in($this->designation)";
			}
			
			$users_list = $DB->get_fieldset_sql($userdata_sql);
	
			if(isset($batch_members))
			$users_list = array_intersect($users_list,$batch_members);
			//array_push($users_list,$USER->id);
	        $enrolled_sql = "SELECT u.id FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id)
				 JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$this->course_id)
                ";
			$enrolled_users_list = $DB->get_fieldset_sql($enrolled_sql);
			$users_list = array_diff($users_list, $enrolled_users_list);
			$users_list = implode(',',$users_list);
        }
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 LEFT JOIN {user_enrolments} ue ON (ue.userid = u.id AND ue.enrolid= :enrolid)				  
                WHERE $wherecondition 
                      AND ue.id IS NULL ";
					  
				
					  //print_object($enrolled_users_list);
				//$users_list = array_diff($users_list, $enrolled_users_list);
		
		
        if(!empty($users_list))
        $sql .= ' AND u.id in('.$users_list.')';
        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating() && (!empty($users_list))) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > 500) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        if(empty($users_list))
        $availableusers = array();
        else
        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));
		
        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('enrolcandidatesmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolcandidates', 'enrol');
        }
        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/manual/locallib.php';
        return $options;
	
    }
	
}

/******************** Enrolled users List.*************************** */
class custom_enrol_manual_current_participant extends user_selector_base {
    protected $costcenter;
	protected $enrolid;
    protected $designation;
    protected $band;
	protected $department;
	protected $subdepartment;
	protected $subsubdepartment;
    protected $email;
    protected $idnumber;
	//added by anil
	protected $name;
	protected $uname;
	protected $course_id;
	protected $id;
	
    public function __construct($name,$options,$costcenter,$designation,$band,$department,$subdepartment,$subsubdepartment,$email,$idnumber,$uname,$course_id,$id) {
        $this->costcenter = $costcenter;
		$this->designation = $designation;
        $this->band = $band;
        $this->email = $email;
        $this->idnumber = $idnumber;
		//added by anil
		$this->uname = $uname;
		$this->id = $id;
		$this->department=$department;
		$this->subdepartment=$subdepartment;
		$this->subsubdepartment=$subsubdepartment;
        $this->course_id = $course_id;
        $this->enrolid  = $options['enrolid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB,$USER;
		$systemcontext = context_system::instance();
        if(!is_siteadmin()  && !has_capability('local/assign_multiple_departments:manage', $systemcontext)){
			
            /***********This below code was written to work on ACADEMY search in BULK ENROL**********/
			if($this->id==1){
			$this->id =$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id)) ;
			}else{
			$this->id = $this->id;
			}
			/*if we get any issues please revoke the previous instance code and change by changing code*/
			/****************End of code************/
			//$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >2 AND u.deleted=0 AND u.suspended=0 AND ud.costcenterid=$this->id";
			$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >2 AND u.deleted=0 AND u.suspended=0 AND ud.costcenterid=$this->id";			
			$email=$this->email;
			
	 	   if(!empty($email) && $email !== null){
				
					if(!empty($email) && $email=="'-1'"){
						
					$all_emails = $DB->get_records_sql_menu("SELECT distinct(email) as email_key, email as email_value FROM {user}");
					if($all_emails){
					$mails = implode('","',$all_emails);
					$userdata_sql.= ' AND u.email IN("'.$mails.'")'; 
					}
					}elseif(!empty($email) && $email !== "'-1'"){
						
					$userdata_sql.=" AND u.email IN({$email})";
			    }
		    
              }
			
			$name=explode(",",$this->uname);
			$nam1=implode("','",$name);
			if(!empty($nam1) && $nam1!=='null' && $nam1 !=="-1"){
			    	$userdata_sql .=" AND u.firstname in('$nam1')";
	        }
			
			if(!empty($this->department) && $this->department!=='null'&& $this->department!== "'-1'"){
					
					$userdata_sql .=" AND ud.department in($this->department)";
			}else{
				
			}
			if(!empty($this->subdepartment) && $this->subdepartment!=='null'&& $this->subdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.subdepartment in($this->subdepartment)";
			}else{
				
			}
			if(!empty($this->subsubdepartment) && $this->subsubdepartment!=='null'&& $this->subsubdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.sub_sub_department in($this->subsubdepartment)";
			}else{
				
			}
			if(!empty($this->idnumber) && $this->idnumber!=='null'&& $this->idnumber!== "'-1'"){
					$userdata_sql .=" AND u.idnumber in ($this->idnumber)";
			}else{
				
			}
			if(!empty($this->band) && $this->band!=='null' && $this->band!== "'-1'"){
			    	$userdata_sql .=" AND ud.band in($this->band)";
			}
				
			if(!empty($this->designation) && $this->designation!=='null' && $this->designation!=="'-1'"){
				    $userdata_sql .=" AND designation in($this->designation)";
			}
			
			
			$users_list = $DB->get_fieldset_sql($userdata_sql);
//		        $enrolled_sql = "SELECT u.id FROM {user} u
//                 JOIN {user_enrolments} ue ON (ue.userid = u.id)
//				 JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$this->course_id)
//                ";
//				
//				$enrolled_users_list = $DB->get_fieldset_sql($enrolled_sql);
////print_object($enrolled_users_list);	
////print_object($users_list);	
//				$users_list = array_diff($users_list, $enrolled_users_list);
//print_object($users_list);	
				 $users_list = implode(',',$users_list);
			 
			
			
        
//        if(!is_siteadmin()){
//			/*  $costcenter = new costcenter();
//			  $costcenterlist = $costcenter->get_assignedcostcenters();
//			  $costcenterlist = $costcenter->get_costcenter_parent($costcenterlist, $selected = array(), $inctop = false, $all = false);
//			  $costcenteridin = implode(',', array_keys($costcenterlist));
//			  */
//			 // $userdata_sql = "select userid from {local_userdata} where costcenterid in($costcenteridin)";
//			 $userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 AND ud.costcenterid='".$this->id."'";
//			 //$userdata_sql = "select userid from {local_userdata} where id>0";
//			  //if(!empty($this->skillset)&&$this->skillset!=null)
//			  //$userdata_sql .=" AND skillset='$this->skillset'";
//			  //if(!empty($this->position)&&$this->position!=null)
//			  //$userdata_sql .=" AND position='$this->position'";
//	  
//			  $users_list = $DB->get_fieldset_sql($userdata_sql);
//	  
//			  $users_list = implode(',',$users_list);
        }else{
		  
			/***********This below code was written to work on ACADEMY search in BULK ENROL**********/
			//if($this->id==1 || $this->id==''){
			//$string="ud.costcenterid>$this->id";
			//}else{
			//$string="ud.costcenterid=$this->id";
			//}
			
			/*if we get any issues please revoke the previous instance code and change by changing code*/
			/****************End of code************/
			$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 ";
			//$userdata_sql = "select userid from {local_userdata} where costcenterid=$this->id";
			
			if(!empty($this->costcenter) && $this->costcenter!=='null'&& $this->costcenter!== "'-1'"){
				$userdata_sql .= " AND ud.costcenterid IN($this->costcenter)";
			}
			
			if(!empty($this->department) && $this->department!=='null'&& $this->department!== "'-1'"){
					
					$userdata_sql .=" AND ud.department in($this->department)";
			}else{
				
			}
			if(!empty($this->subdepartment) && $this->subdepartment!=='null'&& $this->subdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.subdepartment in($this->subdepartment)";
			}else{
				
			}
			if(!empty($this->subsubdepartment) && $this->subsubdepartment!=='null'&& $this->subsubdepartment!== "'-1'"){
					
					$userdata_sql .=" AND ud.sub_sub_department in($this->subsubdepartment)";
			}else{
				
			}
			$email=$this->email;
	 	    if(!empty($email) && $email !== null){
					
					if(!empty($email) && $email=="'-1'"){
					$all_emails = $DB->get_records_sql_menu("SELECT distinct(email) as email_key, email as email_value FROM {user}");
					if($all_emails){
					$mails = implode('","',$all_emails);
					$userdata_sql.= ' AND u.email IN("'.$mails.'")'; 
					}
					}elseif(!empty($email) && $email !== "'-1'"){
					$userdata_sql.=" AND u.email IN({$email})";
					}
		    
              }
			   
			  
			  $name=explode(",",$this->uname);
			  $nam1=implode("','",$name);
			  
			  if(!empty($nam1) && $nam1!=='null' && $nam1 !=="-1"){
				$userdata_sql .=" AND u.firstname in('$nam1')";
	        }
			
			if(!empty($this->idnumber) && $this->idnumber!=='null'&& $this->idnumber!== "'-1'"){
				
				$userdata_sql .=" AND u.idnumber in($this->idnumber)";
			}else{
				
			}
			
			if(!empty($this->band) && $this->band!=='null' && $this->band!== "'-1'"){
				$userdata_sql .=" AND ud.band in($this->band)";
			}
				
			if(!empty($this->designation) && $this->designation!=='null' && $this->designation!=="'-1'"){
				$userdata_sql .=" AND designation in($this->designation)";
			}
		
				
				
				
				
				//$userdata_sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 AND ud.costcenterid='".$this->id."'";
				$users_list = $DB->get_fieldset_sql($userdata_sql);
		    	
				$users_list = implode(',',$users_list);  
        }
		
     /****************The Below code is for listing the enrolled user in text box from enrol,userenrolments table*****************/
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['enrolid'] = $this->enrolid;
       
        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';
      
        $sql = " FROM {user} u
                 JOIN {user_enrolments} ue ON (ue.userid = u.id)
				 JOIN {enrol} e ON (e.id = ue.enrolid and e.courseid=$this->course_id)
                WHERE  $wherecondition ";
        if(!empty($users_list))
        $sql .=" AND u.id in($users_list)";
        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > 500) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }
		
        if(empty($users_list) && !is_siteadmin())  
        $availableusers = array();
        else
         $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));
        
        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('enrolledusersmatching', 'enrol', $search);
        } else {
            $groupname = get_string('enrolledusers', 'enrol');
        }
 
        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['enrolid'] = $this->enrolid;
        $options['file']    = 'enrol/manual/locallib.php';
        return $options;
    }
}

/**
 * A bulk operation for the manual enrolment plugin to edit selected users.
 *
 * @copyright 2011 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_enrol_manual_editselectedusers_operation extends enrol_bulk_enrolment_operation {

    /**
     * Returns the title to display for this bulk operation.
     *
     * @return string
     */
    public function get_title() {
        return get_string('editselectedusers', 'enrol_manual');
    }

    /**
     * Returns the identifier for this bulk operation. This is the key used when the plugin
     * returns an array containing all of the bulk operations it supports.
     */
    public function get_identifier() {
        return 'editselectedusers';
    }

    /**
     * Processes the bulk operation request for the given userids with the provided properties.
     *
     * @param course_enrolment_manager $manager
     * @param array $userids
     * @param stdClass $properties The data returned by the form.
     */
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB, $USER;

        if (!has_capability("enrol/manual:manage", $manager->get_context())) {
            return false;
        }

        // Get all of the user enrolment id's.
        $ueids = array();
        $instances = array();
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $ueids[] = $enrolment->id;
                if (!array_key_exists($enrolment->id, $instances)) {
                    $instances[$enrolment->id] = $enrolment;
                }
            }
        }

        // Check that each instance is manageable by the current user.
        foreach ($instances as $instance) {
            if (!$this->plugin->allow_manage($instance)) {
                return false;
            }
        }

        // Collect the known properties.
        $status = $properties->status;
        $timestart = $properties->timestart;
        $timeend = $properties->timeend;

        list($ueidsql, $params) = $DB->get_in_or_equal($ueids, SQL_PARAMS_NAMED);

        $updatesql = array();
        if ($status == ENROL_USER_ACTIVE || $status == ENROL_USER_SUSPENDED) {
            $updatesql[] = 'status = :status';
            $params['status'] = (int)$status;
        }
        if (!empty($timestart)) {
            $updatesql[] = 'timestart = :timestart';
            $params['timestart'] = (int)$timestart;
        }
        if (!empty($timeend)) {
            $updatesql[] = 'timeend = :timeend';
            $params['timeend'] = (int)$timeend;
        }
        if (empty($updatesql)) {
            return true;
        }

        // Update the modifierid.
        $updatesql[] = 'modifierid = :modifierid';
        $params['modifierid'] = (int)$USER->id;

        // Update the time modified.
        $updatesql[] = 'timemodified = :timemodified';
        $params['timemodified'] = time();

        // Build the SQL statement.
        $updatesql = join(', ', $updatesql);
        $sql = "UPDATE {user_enrolments}
                   SET $updatesql
                 WHERE id $ueidsql";

        if ($DB->execute($sql, $params)) {
            foreach ($users as $user) {
                foreach ($user->enrolments as $enrolment) {
                    $enrolment->courseid  = $enrolment->enrolmentinstance->courseid;
                    $enrolment->enrol     = 'manual';
                    // Trigger event.
                    $event = \core\event\user_enrolment_updated::create(
                            array(
                                'objectid' => $enrolment->id,
                                'courseid' => $enrolment->courseid,
                                'context' => context_course::instance($enrolment->courseid),
                                'relateduserid' => $user->id,
                                'other' => array('enrol' => 'manual')
                                )
                            );
                    $event->trigger();
                }
            }
            return true;
        }

        return false;
    }

    /**
     * Returns a enrol_bulk_enrolment_operation extension form to be used
     * in collecting required information for this operation to be processed.
     *
     * @param string|moodle_url|null $defaultaction
     * @param mixed $defaultcustomdata
     * @return enrol_manual_editselectedusers_form
     */
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/bulkchangeforms.php');
        return new enrol_manual_editselectedusers_form($defaultaction, $defaultcustomdata);
    }
}


/**
 * A bulk operation for the manual enrolment plugin to delete selected users enrolments.
 *
 * @copyright 2011 Sam Hemelryk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class custom_enrol_manual_deleteselectedusers_operation extends enrol_bulk_enrolment_operation {

    /**
     * Returns the title to display for this bulk operation.
     *
     * @return string
     */
    public function get_identifier() {
        return 'deleteselectedusers';
    }

    /**
     * Returns the identifier for this bulk operation. This is the key used when the plugin
     * returns an array containing all of the bulk operations it supports.
     *
     * @return string
     */
    public function get_title() {
        return get_string('deleteselectedusers', 'enrol_manual');
    }

    /**
     * Returns a enrol_bulk_enrolment_operation extension form to be used
     * in collecting required information for this operation to be processed.
     *
     * @param string|moodle_url|null $defaultaction
     * @param mixed $defaultcustomdata
     * @return enrol_manual_editselectedusers_form
     */
    public function get_form($defaultaction = null, $defaultcustomdata = null) {
        global $CFG;
        require_once($CFG->dirroot.'/enrol/manual/bulkchangeforms.php');
        if (!array($defaultcustomdata)) {
            $defaultcustomdata = array();
        }
        $defaultcustomdata['title'] = $this->get_title();
        $defaultcustomdata['message'] = get_string('confirmbulkdeleteenrolment', 'enrol_manual');
        $defaultcustomdata['button'] = get_string('unenrolusers', 'enrol_manual');
        return new enrol_manual_deleteselectedusers_form($defaultaction, $defaultcustomdata);
    }

    /**
     * Processes the bulk operation request for the given userids with the provided properties.
     *
     * @global moodle_database $DB
     * @param course_enrolment_manager $manager
     * @param array $userids
     * @param stdClass $properties The data returned by the form.
     */
    public function process(course_enrolment_manager $manager, array $users, stdClass $properties) {
        global $DB;

        if (!has_capability("enrol/manual:unenrol", $manager->get_context())) {
            return false;
        }
        foreach ($users as $user) {
            foreach ($user->enrolments as $enrolment) {
                $plugin = $enrolment->enrolmentplugin;
                $instance = $enrolment->enrolmentinstance;
                if ($plugin->allow_unenrol_user($instance, $enrolment)) {
                    $plugin->unenrol_user($instance, $user->id);
                }
            }
        }
        return true;
    }
}

/**
 * Migrates all enrolments of the given plugin to enrol_manual plugin,
 * this is used for example during plugin uninstallation.
 *
 * NOTE: this function does not trigger role and enrolment related events.
 *
 * @param string $enrol  The enrolment method.
 */
function custom_enrol_manual_migrate_plugin_enrolments($enrol) {
    global $DB;

    if ($enrol === 'manual') {
        // We can not migrate to self.
        return;
    }

    $manualplugin = enrol_get_plugin('manual');

    $params = array('enrol'=>$enrol);
    $sql = "SELECT e.id, e.courseid, e.status, MIN(me.id) AS mid, COUNT(ue.id) AS cu
              FROM {enrol} e
              JOIN {user_enrolments} ue ON (ue.enrolid = e.id)
              JOIN {course} c ON (c.id = e.courseid)
         LEFT JOIN {enrol} me ON (me.courseid = e.courseid AND me.enrol='manual')
             WHERE e.enrol = :enrol
          GROUP BY e.id, e.courseid, e.status
          ORDER BY e.id";
    $rs = $DB->get_recordset_sql($sql, $params);

    foreach($rs as $e) {
        $minstance = false;
        if (!$e->mid) {
            // Manual instance does not exist yet, add a new one.
            $course = $DB->get_record('course', array('id'=>$e->courseid), '*', MUST_EXIST);
            if ($minstance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'))) {
                // Already created by previous iteration.
                $e->mid = $minstance->id;
            } else if ($e->mid = $manualplugin->add_default_instance($course)) {
                $minstance = $DB->get_record('enrol', array('id'=>$e->mid));
                if ($e->status != ENROL_INSTANCE_ENABLED) {
                    $DB->set_field('enrol', 'status', ENROL_INSTANCE_DISABLED, array('id'=>$e->mid));
                    $minstance->status = ENROL_INSTANCE_DISABLED;
                }
            }
        } else {
            $minstance = $DB->get_record('enrol', array('id'=>$e->mid));
        }

        if (!$minstance) {
            // This should never happen unless adding of default instance fails unexpectedly.
            debugging('Failed to find manual enrolment instance', DEBUG_DEVELOPER);
            continue;
        }

        // First delete potential role duplicates.
        $params = array('id'=>$e->id, 'component'=>'enrol_'.$enrol, 'empty'=>'');
        $sql = "SELECT ra.id
                  FROM {role_assignments} ra
                  JOIN {role_assignments} mra ON (mra.contextid = ra.contextid AND mra.userid = ra.userid AND mra.roleid = ra.roleid AND mra.component = :empty AND mra.itemid = 0)
                 WHERE ra.component = :component AND ra.itemid = :id";
        $ras = $DB->get_records_sql($sql, $params);
        $ras = array_keys($ras);
        $DB->delete_records_list('role_assignments', 'id', $ras);
        unset($ras);

        // Migrate roles.
        $sql = "UPDATE {role_assignments}
                   SET itemid = 0, component = :empty
                 WHERE itemid = :id AND component = :component";
        $params = array('empty'=>'', 'id'=>$e->id, 'component'=>'enrol_'.$enrol);
        $DB->execute($sql, $params);

        // Delete potential enrol duplicates.
        $params = array('id'=>$e->id, 'mid'=>$e->mid);
        $sql = "SELECT ue.id
                  FROM {user_enrolments} ue
                  JOIN {user_enrolments} mue ON (mue.userid = ue.userid AND mue.enrolid = :mid)
                 WHERE ue.enrolid = :id";
        $ues = $DB->get_records_sql($sql, $params);
        $ues = array_keys($ues);
		
        $DB->delete_records_list('user_enrolments', 'id', $ues);
        unset($ues);

        // Migrate to manual enrol instance.
        $params = array('id'=>$e->id, 'mid'=>$e->mid);
        if ($e->status != ENROL_INSTANCE_ENABLED and $minstance->status == ENROL_INSTANCE_ENABLED) {
            $status = ", status = :disabled";
            $params['disabled'] = ENROL_USER_SUSPENDED;
        } else {
            $status = "";
        }
        $sql = "UPDATE {user_enrolments}
                   SET enrolid = :mid $status
                 WHERE enrolid = :id";
        $DB->execute($sql, $params);
    }
    $rs->close();
}


 /*Get uploaded course summary uploaded file
     * @param $course is an obj Moodle course
     * @return course summary file(img) src url if exists else return default course img url
     * */
    function get_course_summary_file($course){  
        global $DB, $CFG, $OUTPUT;
        if ($course instanceof stdClass) {
            require_once($CFG->libdir . '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        
        // set default course image
        $url = $OUTPUT->pix_url('/course_images/courseimg', 'local_costcenter');
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            if($isimage)
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
            $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
        }
        return $url;
    }
