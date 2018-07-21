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
 * Version details
 * @package    local
 * @subpackage learningplan
 * @copyright  2016 Syed HameedUllah <hameed@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/enrol/locallib.php');
require_once($CFG->dirroot . '/local/notifications/lib.php');
require_once($CFG->dirroot . '/lib/filelib.php');
class learningplan {
	function create_learning_plan($data){
		global $DB, $USER;
		
        $data->usercreated = $USER->id;
		$data->timecreated = time();
		//print_object($data);
		$DB->insert_record('local_learningplan', $data);
    }
	
	function update_learning_plan($data){
		global $DB, $USER;
		
		$data->usermodified = $USER->id;
		$data->timemodified = time();
		if(!empty($data->id)){
			$DB->update_record('local_learningplan', $data);
		}
    }
	
	function delete_learning_plan($id){
		global $DB, $USER;
		if($id > 0){
			$DB->delete_records('local_learningplan', array('id' => $id));
		}
	}
	
	function learningplan_courses_list($id){
		global $CFG, $DB, $USER;
		$systemcontext = context_system::instance();
		
		if(is_siteadmin() || has_capability('local/assign_multiple_departments:manage', $systemcontext)){
			$accdcostcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
			$sql = 'SELECT cd.courseid as id, c.fullname FROM {course} as c JOIN {local_coursedetails} as cd  ON cd.courseid = c.id WHERE c.id > 1 AND c.visible = 1 and FIND_IN_SET(4,cd.identifiedas)
			AND cd.costcenterid IN('.$accdcostcenter.')
			';
			$courses = $DB->get_records_sql_menu($sql);
		}else{
			$costcenter_sql = 'SELECT ud.costcenterid
									FROM {user} as u
									JOIN {local_userdata} as ud ON ud.userid = u.id
									WHERE u.id='.$USER->id;
									
			$costcenter = $DB->get_record_sql($costcenter_sql);
			$accdcostcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
			$course_sql = 'SELECT cd.courseid as id, c.fullname
									FROM {local_coursedetails} as cd
									JOIN {course} as c ON cd.courseid = c.id
									WHERE c.id > 1 AND c.visible = 1 and FIND_IN_SET(4,cd.identifiedas) AND cd.costcenterid IN('.$costcenter->costcenterid.')';
			
			$courses = $DB->get_records_sql_menu($course_sql);
		}
		return $courses;
	}
	
	function learningplan_users_list(){
		global $CFG, $DB, $USER;
		$systemcontext = context_system::instance();
		
		if(is_siteadmin() || has_capability('local/assign_multiple_departments:manage', $systemcontext)){
			$sql = 'SELECT u.id, CONCAT(u.firstname," ", u.lastname)
						FROM {users} WHERE id > 2 AND visible = 1';
			$courses = $DB->get_records_sql_menu($sql);
		}else{
			$costcenter_sql = 'SELECT ud.costcenterid
									FROM {user} as u
									JOIN {local_userdata} as ud ON ud.userid = u.id
									WHERE u.id='.$USER->id;
			$costcenter = $DB->get_record_sql($costcenter_sql);
			$course_sql = 'SELECT cd.courseid as id, c.fullname
									FROM {local_coursedetails} as cd
									JOIN {course} as c ON cd.courseid = c.id
									WHERE c.id > 1 AND c.visible = 1 AND cd.costcenterid = '.$costcenter->costcenterid;
			$courses = $DB->get_records_sql_menu($course_sql);
		}
		return $courses;
	}
	
	function assign_courses_to_learningplan($data){
		global $CFG, $DB, $USER;
		$DB->insert_record('local_learningplan_courses', $data);
		return 'courses added to learningplan';
	}
   	
	function update_courses_to_learningplan($data){
		global $CFG, $DB, $USER;
		if($data->id > 0){
			$DB->update_record('local_learningplan_courses', $data);
		}
		return 'courses updated to learningplan';
	}
	
	function delete_courses_to_learningplan($data){
		
		global $CFG, $DB, $USER;
		$get=$DB->get_records('local_learningplan_courses',array('planid'=>$data->planid));
		//print_object($get);exit;
			$DB->delete_records('local_learningplan_courses', array('id' => $data->id, 'planid' => $data->planid, 'courseid' => $data->courseid));
		//}
		$get_coures=$DB->get_records('local_learningplan_courses',array('planid'=>$data->planid));
		$i=0;
		foreach($get_coures as $get){
			
			$data = new stdClass();
			$data->id=$get->id;
			$data->planid = $get->planid;
			$data->courseid = $get->courseid;
			$data->nextsetoperator=$get->nextsetoperator;
			$data->timecreated = time();
			$data->usercreated = $USER->id;
			$data->timemodified = 0;
			$data->usermodified = 0;
			$data->sortorder=$i;
            
			
			$DB->update_record('local_learningplan_courses', $data);
			$i++;
		}
		
		
		
		
	}
	
	function get_learningplan_assigned_courses($planid){
		global $CFG, $DB, $USER;
		if($planid){
		$sql = "SELECT c.*,lc.sortorder,lc.id as lepid,lc.nextsetoperator as next
					FROM {local_learningplan_courses} lc
					JOIN {course} c ON c.id = lc.courseid
					WHERE lc.planid = ".$planid." ORDER BY lc.sortorder ASC" ;
		$courses = $DB->get_records_sql($sql);
		
		}else{
			$courses=false;
		}
		return $courses;
	}
	
	function assign_users_to_learningplan($data){
		global $CFG, $DB, $USER;
		$check=$DB->get_records('local_learningplan_user',array('userid'=>$data->userid,'planid'=>$data->planid));
   if(!$check){
		$user=$DB->insert_record('local_learningplan_user', $data);
		if($user){
			$approvalid=$DB->get_record('local_learningplan_approval',array('planid'=>$data->planid,'userid'=>$data->userid));
			if($approvalid){
			$facetofaceinfo=$DB->get_record('local_learningplan_approval', array('id'=>$approvalid->id));
			$facetofaceinfo->approvestatus=1;           
			$facetofaceinfo->approvedby =$USER->id;          
			$facetofaceinfo->timemodified = time();
			$facetofaceinfo->usermodified = $USER->id;
			$check=$DB->update_record('local_learningplan_approval', $facetofaceinfo);
			}
		    
		}
		//print_object($approvalid);exit;
		}
		/***Code to enrol the users to first course which has sortorder 0 starts here***/
		//$sql="select * from {local_learningplan_courses} where planid=".$data->planid." and sortorder=0";
		//$check=$DB->get_record_sql($sql);
		///** first to check to enrol one course or two course**/
		//if($check->nextsetoperator=='and'){ /*Condition for enrol one course*/
		//	
		//	$sql="select * from {local_learningplan_courses} where planid=".$data->planid." and sortorder=0";
		//    $record=$DB->get_records_sql($sql);
		//}else{ /*Condition for enrol two course*/
		//	 $sql="select * from {local_learningplan_courses} where planid=".$data->planid." and sortorder IN (0,1)";
		//	 $record=$DB->get_records_sql($sql);
		//}
		//
		//foreach($record as $single){
		//	
		//	$enrol_manual = enrol_get_plugin('learningplan');
		//	$sql="select * from {enrol} where courseid=".$single->courseid." and enrol='learningplan'";
		//	$instance=$DB->get_record_sql($sql);
		//			
		//	$roleid=$instance->roleid;
		//	//$timestart=$DB->get_field('local_coursedetails',enrollstartdate,array('courseid'=>$single->courseid));
		//	//$timestart=$DB->get_field('course',startdate,array('id'=>$single->courseid));
		//	$timestart=0;
		//	$timeend=0;
		//	$enrol_manual->enrol_user($instance, $data->userid, $roleid, $timestart, $timeend);
		//	
		//}
		
		return 'courses added to learningplan';
	    
	}
	
	function update_users_to_learningplan($data){
		global $CFG, $DB, $USER;
		if($data->id > 0){
			$DB->update_record('local_learningplan_user', $data);
		}
		return 'Users updated to learningplan';
	}
	
	function delete_users_to_learningplan($data){
		global $CFG, $DB, $USER;
		//if($data->id > 0){
		    if($data->id){
				
			$DB->delete_records('local_learningplan_user', array('id' => $data->id, 'planid' => $data->planid, 'userid' => $data->userid));
		}else{
			
			$id=$DB->delete_records('local_learningplan_user',array('planid' => $data->planid, 'userid' => $data->userid));
			$DB->delete_records('local_learningplan_user', array('id' => $id, 'planid' => $data->planid, 'userid' => $data->userid));
		}
		$approval=$DB->get_record('local_learningplan_approval',array('planid'=>$data->planid,'userid'=>$data->userid));
		if($approval){
			
	    $approvalid= $approval->id;
        $facetofaceinfo=$DB->get_record('local_learningplan_approval', array('id'=>$approvalid));
        $facetofaceinfo->approvestatus=2;           
        $facetofaceinfo->approvedby =$USER->id;          
        $facetofaceinfo->timemodified = time();
        $facetofaceinfo->usermodified = $USER->id;
        $facetofaceinfo->reject_msg =$submitted_data->text;
		
		$dat=$DB->update_record('local_learningplan_approval', $facetofaceinfo);
		}
		return 'Users deleted from learningplan';
	}
	
	function get_learningplan_assigned_users($planid){
		global $CFG, $DB, $USER;
		
		$sql = "SELECT u.*,lu.completiondate,lu.status,lu.timecreated
					FROM {local_learningplan_user} lu
					JOIN {user} u ON u.id = lu.userid
					WHERE lu.planid = ".$planid." and deleted!=1";
		$users = $DB->get_records_sql($sql);
		
		return $users;
	}
	function notification_for_user_enrol($users,$data){
		
		global $CFG, $DB, $USER;
		 $type="learningplan_enrol";
         $get_ilt=$DB->get_record('local_notification_type',array('shortname'=>$type));
         
		 //if(count($users)>20){
               //$to_userids=array_chunk($users, 20);
			  // print_object($to_userids);exit;
		       foreach($users as $to_userid){
                    $users=implode(',',$to_userid);
				   	$from = $DB->get_record('user', array('id'=>$USER->id));
					$data_infor=$DB->get_record('local_learningplan',array('id'=>$data->planid));
					if($data_infor->learning_type==1){
						$type='core courses';
					}else{
						$type='elective courses';
					}
					//$coursename=$DB->get_records_menu('local_learningplan_courses',array('planid'=>$data->planid),'id','id,courseid');
					//$course=implode(',',$coursename);
					//$sql="select id,fullname from {course} where id IN ($course)";
					//$coursename=$DB->get_records_sql_menu($sql);
					//$course_names=implode(',',$coursename);
					$coursename=$DB->get_records_menu('local_learningplan_courses',array('planid'=>$data->planid),'id','id,courseid');
					if($coursename){
						$course= implode(',',$coursename);
						$sql="select id,fullname from {course} where id IN ($course)";
						$coursename=$DB->get_records_sql_menu($sql);
						$course_names=array();
						foreach($coursename as $course){
							$course_names[]="<span>$course</span><br/>";
						}
						$course_names1=implode('',$course_names);
					}else{
						$course_names1 = 'Not Assigned';
					}
					$department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_infor->costcenter));
					 if($department==''){
                    $department="[ilt_department]";
                    }
					$sql="select id, concat(firstname,' ', lastname) as fullname  from {user} where id=$data_infor->usercreated";   
					$creator=$DB->get_record_sql($sql);
					
					$data_details=$DB->get_record('local_coursedetails',array('courseid'=>$single->id));
					//$department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_details->costcenterid));
					$dataobj= new stdClass();
					$dataobj->lep_name=$data_infor->name;
					$dataobj->lep_course=$course_names1;
					$dataobj->course_code=$data_infor->shortname;
					$dataobj->lep_startdate=date('d M Y',$data_infor->startdate);
					$dataobj->lep_enddate=date('d M Y',$data_infor->enddate);
					$dataobj->lep_creator=$creator->fullname;
					//$dataobj->lep_department=$department;
					$dataobj->lep_type=$type;
					$dataobj->lep_enroluser_username="[lep_enroluser_username]";
					$dataobj->lep_enroluseremail="[lep_enroluseremail]";
					$url = new moodle_url($CFG->wwwroot.'/local/learningplan/view.php',array('id'=>$data->planid,'couid'=>$data->planid));
                    $dataobj->lep_link = html_writer::link($url, $data_infor->name, array());
					$touserid=$to_userid;
					$fromuserid=2;
					$notifications_lib = new notifications();
					$emailtype='learningplan_enrol';
					$planid=$data->planid;			
					$notifications_lib->send_email_notification($emailtype, $dataobj, $touserid, $fromuserid,$batchid=0,$planid);
				 }
			  return true;
			// }/*else{
				
//				foreach($users as $user){
//				
//					$users=$user;
//						
//					$from = $DB->get_record('user', array('id'=>$USER->id));
//					$data_infor=$DB->get_record('local_learningplan',array('id'=>$data->planid));
//					
//					$coursename=$DB->get_records_menu('local_learningplan_courses',array('planid'=>$data->planid),'id','id,courseid');
//					//$course=implode(',',$coursename);
//					//
//					//$sql="select id,fullname from {course} where id IN ($course)";
//					//$coursename=$DB->get_records_sql_menu($sql);
//					//$course_names=implode(',',$coursename);
//					if($coursename){
//						$course=implode(',',$coursename);
//						$sql="select id,fullname from {course} where id IN ($course)";
//						$coursename=$DB->get_records_sql_menu($sql);
//						$course_names=implode(',',$coursename);
//					}else{
//						$course_names = "Not Assigned";
//					}
//					$data_details=$DB->get_record('local_coursedetails',array('courseid'=>$single->id));
//					//$department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_details->costcenterid));
//					$department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_infor->costcenter));
//					 if($department==''){
//                    $department="[ilt_department]";
//                    }
//					$sql="select id, concat(firstname,' ', lastname) as fullname  from {user} where id=$data_infor->usercreated";   
//					$creator=$DB->get_record_sql($sql);
//					if($data_infor->learning_type==1){
//						$type='core courses';
//					}else{
//						$type='elective courses';
//					}
//				    
//					$dataobj= new stdClass();
//					$dataobj->lep_name=$data_infor->name;
//					$dataobj->lep_course=$course_names;
//					$dataobj->course_code=$data_infor->shortname;
//					$dataobj->lep_startdate=date('d M Y',$data_infor->startdate);
//					$dataobj->lep_enddate=date('d M Y',$data_infor->enddate);
//					$dataobj->lep_creator=$creator->fullname;
//					$dataobj->lep_department=$department;
//					$dataobj->lep_enroluser_username="[lep_enroluser_username]";
//					$dataobj->lep_enroluseremail="[lep_enroluseremail]";
//					
//					$dataobj->lep_type=$type;
//					$touserid=$users;
//					$fromuserid=2;
//					
//					$notifications_lib = new notifications();
//						
//					$emailtype='learningplan_enrol';
//						
//					$notifications_lib->send_email_notification($emailtype, $dataobj, $touserid, $fromuserid);*/
				
				
				
			 
		//print_object($users);exit;
	}
	function get_previous_course_status($planid, $sortorder,$courseid){
		global $CFG, $DB, $USER;

		$sql = "SELECT * FROM {local_learningplan_courses} lc WHERE lc.planid=".$planid." AND lc.sortorder=$sortorder";
		$records = $DB->get_record_sql($sql);
		
		if(!empty($records)){
				$result="course_disabled";
			
		return $records;
	       
		}
		//if(!empty($records)){
		//	foreach($records as $rec){
		//		if($rec->sortorder > 0){
		//			//$next_record = $DB->get_record_sql('SELECT nextsetoperator FROM {local_learninplan_courses}
		//				//							   WHERE sortorder < '.$rec->sortorder.);
		//		}
		//	}
		//}
	}
	function get_completed_lep_users($courseid,$planid){
    global $CFG,$DB,$USER;
	//print_object($courseid);
	//print_object($USER->id);
	$sql="select id from {course_completions} where course=$courseid and userid=$USER->id and timecompleted!=''";
	$get_course=$DB->get_record_sql($sql);
    
	return $get_course;
	
	
	}
	public function check_courses_assigned_target_audience($user,$planid){
		global $DB,$USER;
		   $users=$DB->get_record('local_userdata',array('userid'=>$USER->id));
			 $us=$users->band;
			 $array=explode(',',$us);
			 $list=implode("','",$array);
			//print_object($users);
			/*********changed IN to Find_in_set in query for issues 1258********/
			$sql='SELECT ud.* FROM {local_learningplan} AS ud WHERE
			ud.id='.$planid.' AND (case when ud.costcenter IS NOT NULL then FIND_IN_SET('.$users->costcenterid.',ud.costcenter) else ud.costcenter is NULL END)
			AND (case when ud.department IS NOT NULL THEN FIND_IN_SET('.$users->department.',ud.department) else ud.department is NULL END)
			AND (case when ud.subdepartment IS NOT NULL THEN FIND_IN_SET('.$users->subdepartment.',ud.subdepartment) else ud.subdepartment is NULL END)
			AND (case when ud.subsubdepartment IS NOT NULL  THEN FIND_IN_SET('.$users->sub_sub_department.',ud.subsubdepartment) else ud.subsubdepartment is NULL END)
			AND (case when ud.band IS NOT NULL  THEN FIND_IN_SET("'.$list.'",ud.band) else ud.band is NULL END)';
			//if($users->costcenterid){
			//$sql .=' ud.costcenter IN ('.$users->costcenterid.')  ';
			//}else{
			//$sql .=' ud.costcenter!=""  ';
			//}
		
		//if($users->department!=''){
		//	$sql .='ud.department IN ('.$users->department.') AND ';
		//}else{
		//	$sql.='ud.department!="" AND ' ;
		//}
		//if($users->subdepartment!=''){
		//	$sql .='ud.subdepartment IN ('.$users->subdepartment.') AND ';
		//}else{
		//	$sql.='ud.subdepartment!="" AND ';
		//}
		//if($users->sub_sub_department!=''){
		//	$sql .='ud.subsubdepartment IN('.$users->sub_sub_department.') AND ';
		//}else{
		//	$sql.='ud.subsubdepartment!="" AND ';
		//}
		//if($users->band!=''){
		//	
		//	$sql .="ud.band IN('$list')";
		//}else{
		//	$sql .='ud.band!=""  ';
		//}
			//FIND_IN_SET('.$data->costcenterid.',l.costcenter) AND
			//FIND_IN_SET("'.$data->band.'",l.band) AND
			//FIND_IN_SET('.$data->department.',l.department) AND
			//FIND_IN_SET('.$data->sub_sub_department.',l.subsubdepartment) AND
			//FIND_IN_SET('.$data->subdepartment.',l.subdepartment )
			//AND l.id > 0 ORDER BY l.timemodified DESC';                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                           
			//echo $sql;
			$learning_plans = $DB->get_records_sql($sql);
			
			if($learning_plans){
				return true;
			}else{
				return false;
			}
	}
	public function to_enrol_users_check_completion($planid,$users){
		global $DB,$USER;
		//    $planid1=$DB->get_record('local_learningplan_users',array('user'=>$USER->id));
		//	print_object($planid1);exit;
			$sql="select llc.*,cc.* from {local_learningplan_courses} as llc
			JOIN {course_completions} as cc ON 	cc.course=llc.courseid
		
			where llc.planid=".$planid." and cc.userid=$users and cc.timecompleted!='NULL' order by llc.id desc limit 1";
			echo $sql;
			//print_object($planid);
			//print_object($planid);
			$check=$DB->get_record_sql($sql);
			
			if($check){
						$sort=$check->sortorder+1;	
						$sql="select * from {local_learningplan_courses} where planid=".$planid." and sortorder =$sort";
						$record=$DB->get_record_sql($sql);
						
						if($record){
							
								$enrol_manual = enrol_get_plugin('learningplan');
								$sql="select * from {enrol} where courseid=".$record->courseid." and enrol='learningplan'";
								
								
								$instance=$DB->get_record_sql($sql);
							if($instance){ 
								$roleid=$instance->roleid;
								$timestart=0;
								$timeend=0;
								$enrol_manual->enrol_user($instance, $users, $roleid, $timestart, $timeend);
								}
						}
						
						}else{
			
							}
	}
	public function complete_the_lep($planid,$user){
			
			global $DB,$USER;
			//print_object($planid);
			//print_object($user);
			if($planid){
			$sql="select llc.courseid as id, llc.courseid from {local_learningplan_courses} as llc join {local_learningplan_user} as llu
			on llc.planid=llu.planid where llc.planid=$planid and llc.nextsetoperator='and' and llu.userid=$user ";
			//echo $sql;
			$courses=$DB->get_records_sql_menu($sql);
			$check=array();
			$completed=array();
			$optional_completed=array();
			//print_object($courses);
			if($courses){
				foreach($courses as $course){
				
								$sql="select id from {course_completions} where course=".$course." and userid= $user and timecompleted!='NULL'";
								//echo $sql;
								$check=$DB->get_record_sql($sql);
								//print_object($check);
								if($check){
								$completed[]=1;
								}else{
								$completed[]=0;
								}		
								
				}
			}else{
				
				$sql="select llc.courseid as id, llc.courseid from {local_learningplan_courses} as llc join {local_learningplan_user} as llu
				on llc.planid=llu.planid where llc.planid=$planid  and  llu.userid=$user ";
				echo $sql;
				$courses=$DB->get_records_sql_menu($sql);
				print_object($courses);
				foreach($courses as $course){
				$sql="select id from {course_completions} where course=".$course." and userid= $user and timecompleted!='NULL'";
				echo $sql;
				$check=$DB->get_record_sql($sql);
				print_object($check);
				if($check){
				$optional_completed[]=1;
				}else{
				$optional_completed[]=0;
				}		
					}
			}
			print_object($optional_completed);
		if($completed){
			if (in_array("0", $completed)){
			
			
			}else{
			
			$date=time();
			$sql="select * from {local_learningplan_user} where planid=$planid and userid=$user";
			$id=$DB->get_record_sql($sql);
			
				if($id){
					$condition=$DB->get_field('local_learningplan_user','id',array('id'=>$id->id,'status'=>1));
				print_object($condition);
				if(empty($condition)){
				$sql="UPDATE {local_learningplan_user} SET completiondate='$date' where id=".$id->id."";
				$data=$DB->execute($sql);
			
				$sql_1="UPDATE {local_learningplan_user} SET status='1' where id=".$id->id."";
				$data_1=$DB->execute($sql_1); 
			
				$emailtype="learningplan_completion";
				$status="Completed";
				
				$this->to_send_request_notification($id,$emailtype,$status,$planid);
					}
				}
			}
			}
		  if($optional_completed){
			if (in_array("1", $optional_completed)){
				echo "hi";
				$date=time();
				$sql="select * from {local_learningplan_user} where planid=$planid and userid=$user";
				$id=$DB->get_record_sql($sql);
				
				if($id){
				$condition=$DB->get_field('local_learningplan_user','id',array('id'=>$id->id,'status'=>1));
				
				if(empty($condition)){
					
				$sql="UPDATE {local_learningplan_user} SET completiondate='$date' where id=".$id->id."";
				$data=$DB->execute($sql);
				
				$sql_1="UPDATE {local_learningplan_user} SET status='1' where id=".$id->id."";
				$data_1=$DB->execute($sql_1); 
				print_object($data_1);
				$emailtype="learningplan_completion";
				$status="Completed";
				
				$this->to_send_request_notification($id,$emailtype,$status,$planid);
				}
				}
			
			}else{
			
			
			}
			}
			
			}
		}
public function to_enrol_users($planid,$userid,$course_enrol){
	
	global $DB,$USER;
	
	    $sql="select * from {local_learningplan_courses} where planid=$planid and courseid=$course_enrol";
		$record=$DB->get_record_sql($sql);
					
		foreach($record as $single){
			
			$enrol_manual = enrol_get_plugin('learningplan');
			$sql="select * from {enrol} where courseid=".$course_enrol." and enrol='learningplan'";
			$instance=$DB->get_record_sql($sql);
			if($instance){		
			$roleid=$instance->roleid;
			$timestart=$DB->get_field('course','startdate',array('id'=>$course_enrol));
			$timeend=0;
			$enrol_manual->enrol_user($instance, $userid, $roleid, $timestart, $timeend);
			}else{
			 echo "Please contact the admin and enrol the course";	
			}
		}
		//return true;
		$plan_url = new moodle_url('/course/view.php', array('id' => $course_enrol));
        redirect($plan_url);	
}
public function to_send_request_notification($data,$emailtype,$status,$planid){
	
	global $DB,$USER;
					
				   	$from = "2";
					$data_infor=$DB->get_record('local_learningplan',array('id'=>$data->planid));
					$completion_date=$DB->get_field('local_learningplan_user','completiondate',array('userid'=>$data->userid,'planid'=>$data->planid));
					
					$coursename=$DB->get_records_menu('local_learningplan_courses',array('planid'=>$data->planid),'id','id,courseid');
					if($coursename){
						$course= implode(',',$coursename);
						$sql="select id,fullname from {course} where id IN ($course)";
						$coursename=$DB->get_records_sql_menu($sql);
						$course_names=array();
						foreach($coursename as $course){
						$course_names[]="<span>$course</span><br/>";
						}
						$course_names1=implode('',$course_names);
					}else{
						$course_names1="course still not assigned";
					}
					if($data_infor->learning_type==1){
						$type='core courses';
					}else{
						$type='elective courses';
					}
					$department=$DB->get_field('local_costcenter','fullname',array('id'=>$data_infor->costcenter));
					 if($department==''){
                    //$department="[ilt_department]";
                    }
					$sql="select id, concat(firstname,' ', lastname) as fullname  from {user} where id=$data_infor->usercreated";   
					$creator=$DB->get_record_sql($sql);
                   
					
					$dataobj= new stdClass();
					$dataobj->lep_name=$data_infor->name;
					$dataobj->lep_course=$course_names1;
					$dataobj->course_code=$data_infor->shortname;
					$dataobj->lep_startdate=date('d M Y',$data_infor->startdate);
					$dataobj->lep_enddate=date('d M Y',$data_infor->enddate);
					
					$dataobj->lep_enroluser_username=$DB->get_field('user','username',array('id'=>$data->userid));
					$dataobj->lep_enroluseremail=$DB->get_field('user','email',array('id'=>$data->userid));
					$dataobj->lep_status=$status;
					//$dataobj->lep_department=$department;
					$dataobj->lep_creator=$creator->fullname;
					//print_object($emailtype);
					if($emailtype=='learningplan_enrol' || $emailtype=='lep_nomination' || $emailtype=='learningplan_completion' || $emailtype=='lep_approvaled'){
					$url = new moodle_url($CFG->wwwroot.'/local/learningplan/view.php',array('id'=>$data->planid,'couid'=>$data->planid));
                    $dataobj->lep_link = html_writer::link($url, $data_infor->name, array());
					//print_object($dataobj->course_link);exit;
					}
					if($emailtype=='lep_approval_request' || $emailtype=='lep_rejected'){
					$url = new moodle_url($CFG->wwwroot.'/local/learningplan/view.php',array('id'=>$data->planid));
                    $dataobj->lep_link = html_writer::link($url, $data_infor->name, array());
					
					$reject=$DB->get_field('local_learningplan_approval','reject_msg',array('planid'=>$data->planid,'userid'=>$data->userid));
					$dataobj->lep_rejectmsg=$reject;
					}
			
					$dataobj->lep_completiondate=date('d M Y',$completion_date);
					$dataobj->lep_type=$type;
					$touserid=$data->userid;
					$fromuserid=2;
					$notifications_lib = new notifications();
					$emailtype=$emailtype;
					//print_object($dataobj);exit;
					$planid=$data->planid;
					$notifications_lib->send_email_notification($emailtype, $dataobj, $touserid, $fromuserid,$batchid=0,$planid);
}
public function to_send_notification_for_completed($data,$emailtype,$status){
	
}
public function lep_complete_courses($planid,$userid){
	
	global $DB,$USER;
		$sql="select llc.courseid, llc.planid from {local_learningplan_courses} as llc where llc.planid=$planid ";
		//echo $sql;
		$courses=$DB->get_records_sql($sql);
		
		return $courses;
}

	public function get_learningplan_summeryfile($learningplanid){
		global $DB, $CFG;
		
		$lplan_itemid = $DB->get_field('local_learningplan', 'summaryfile', array('id'=>$learningplanid));
		$fileurl = null;
		if($lplan_itemid){
			$filedata = $DB->get_record_sql("SELECT * FROM {files} WHERE itemid = $lplan_itemid AND filename != '.'");
			
			$fileurl = $CFG->wwwroot."/draftfile.php/$filedata->contextid/$filedata->component/$filedata->filearea/$filedata->itemid/$filedata->filename";
			print_object($fileurl);
		}
		return $fileurl;
	}
	
	/**
     * Returns url/path of the learningplan summaryfile if exists, else false
     *
	 * @param int $lpanid, local_learningplan id
     */
	function get_learningplansummaryfile($lpanid){
	
        global $CFG, $DB, $USER;
		
		$imgurl = false;
		
        $fileitemid = $DB->get_field('local_learningplan', 'summaryfile', array('id'=>$lpanid));
		
		if(!empty($fileitemid)){
 			$sql = "SELECT * FROM {files} WHERE itemid = $fileitemid AND filename != '.' ORDER BY id DESC LIMIT 1";
			$filerecord = $DB->get_record_sql($sql);
		}	
			if($filerecord!=''){
				
			$imgurl = file_encode_url($CFG->wwwroot."/pluginfile.php", '/' . $filerecord->contextid . '/' . $filerecord->component . '/' .$filerecord->filearea .'/'.$filerecord->itemid. $filerecord->filepath. $filerecord->filename);
			}
		//}else{
			if(empty($imgurl)){
			
			$dir = $CFG->wwwroot.'/local/costcenter/pix/course_images/image3.jpg';
			for($i=1; $i<=10; $i++) {
				$image_name = $dir;
				$imgurl = $image_name;
				break;
			}

		//}
		}
		
		return $imgurl;
	}
	
	/**
     * Returns function for get learnigplan courses count
     *
	 * @param int $planid, local_learningplan id
	 * @param text $mandatory optional, and/or
     */
	function learningplancourses_count($planid, $mandatory = null){
		global $DB;
		
		$sql = "SELECT COUNT(lc.id)
					FROM {local_learningplan_courses} lc
					JOIN {course} c ON c.id = lc.courseid
					WHERE lc.planid = ".$planid." " ;
					
		if($mandatory == 'and'){
			$sql .= "AND lc.nextsetoperator = 'and' ";
		}elseif($mandatory == 'or'){
			$sql .= "AND lc.nextsetoperator = 'or' ";
		}
		
		$coursescount = $DB->count_records_sql($sql);
		
		return $coursescount;
	}
}
