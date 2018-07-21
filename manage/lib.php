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
 * The comments block helper functions and callbacks
 *
 * @package   local
 * @copyright 2016 Anilkumar <anil.k@eabyas.in>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

    defined('MOODLE_INTERNAL') || die();

    /**
     * @param object $coursedetails 
     */
    function disable_course_enroll($coursedetails) {
        global $DB,$USER;
        $current_date = strtotime(date("d M Y"));
		
		
        $usercostcenter=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id));
		/**********This code is been added to view or enrol the acdeamy created course***********/
		$acdcostcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
		/****End of the code Done Ravi_369***/
        if($coursedetails->costcenterid!=$acdcostcenter){/******This is the condition to check acd course Ravi_369***/
        if($coursedetails->costcenterid != $usercostcenter)
			return false;
		}
		
		
		if($coursedetails->enrollstartdate!=0){
			//echo "hi";
		    $start_date     = strtotime(date('d M Y', $coursedetails->enrollstartdate));
		}else{
			//echo "oye";
			$start_date=0;
		}
		
		if($coursedetails->enrollenddate!=0){
			//echo "hii";
		    $end_date     = strtotime(date('d M Y', $coursedetails->enrollenddate));
		}else{
			//echo "oyeee";	
			$end_date=0;
		}
		//print_object($start_date);
		//print_object($end_date);
		$result= false;			  
		if(($start_date <= $current_date) && ($end_date >= $current_date)){
            $result = true;
        }elseif(($start_date <= $current_date) && ($end_date=='0')){
			$result = true;
		}elseif($start_date=='0' && $end_date >=$current_date){
			$result = true;
		}elseif($start_date=='0' && $end_date=='0'){
			$result = true;
		}
        return $result;
    }
	function disable_course_enroll_msg($coursedetails) {
        global $DB;
        $current_date = strtotime(date("d M Y"));
		//$start_date   = strtotime(date('d M Y', $coursedetails->enrollstartdate));
		if($coursedetails->enrollstartdate!=0){
		    $start_date     = strtotime(date('d M Y', $coursedetails->enrollstartdate));
		}else{
			$start_date=0;
		}
		
		if($coursedetails->enrollenddate!=0){
		    $end_date     = strtotime(date('d M Y', $coursedetails->enrollenddate));
		}else{
			$end_date=0;
		}
	
		$result= false;			  
		if(($start_date <= $current_date) && ($end_date >= $current_date)){
			
            $result = true;
        }
		
        return $result;
    }
	function disable_course_enroll_msgwait($coursedetails) {
		
        global $DB;
        $current_date = strtotime(date("d M Y"));
		//$start_date   = strtotime(date('d M Y', $coursedetails->enrollstartdate));
		if($coursedetails->enrollstartdate!=0){
		    $start_date     = strtotime(date('d M Y', $coursedetails->enrollstartdate));
		}else{
			$start_date=0;
		}
		
		if($coursedetails->enrollenddate!=0){
		    $end_date     = strtotime(date('d M Y', $coursedetails->enrollenddate));
		}else{
			$end_date=0;
		}
	
				  
		if(($start_date >= $current_date) && ($end_date >= $current_date)){
			
            $result= true;	
        }
		
        return $result;
    }
	function disable_course_enrol_enrol($coursedetails){
		 global $DB,$USER;
			$sql="select enrol,id from {enrol} where courseid=$coursedetails->courseid";
            $get_data=$DB->get_records_sql_menu($sql);
            $data=implode(',',$get_data);
        
        /********This below query is used to check the user already enroled to course with other enrolments methods******/
            $sql="select id from {user_enrolments} where enrolid IN($data) and userid=$USER->id";
            $check=$DB->record_exists_sql($sql) ;
			$data=true;
			if($check){
				$data=false;
			}
			return $data;
	}
	function learning_plan_information($uid) {
        global $CFG, $DB, $PAGE, $OUTPUT, $USER;
        $table = new html_table();
		$table->id = "lp_plan_info";
        $table->head = array('');
        $table->attributes = array('class' => 'lp_display generaltable lp_newclass');
        $table->width = '100%';
        $is_manager = $DB->record_exists_sql("select cp.* from {local_costcenter_permissions} as cp 
                             JOIN {role_assignments} as ra ON ra.userid=cp.userid and cp.userid=$USER->id
                             JOIN {role} as r ON r.id=ra.roleid
                             where r.archetype='manager'");
        $costcenter = new costcenter();
        //$costcenterlist = $costcenter->get_assignedcostcenters();
        //$costcenterlist = $costcenter->get_costcenter_parent($costcenterlist, $selected = array(), $inctop = false, $all = false);
        //$costcenteridin = implode(',', array_keys($costcenterlist));
        //
        $sql = "SELECT ll.* 
				FROM mdl_learning_learningplan AS ll
				JOIN mdl_learning_user_learningplan AS ul ON ul.lp_id = ll.id
				JOIN mdl_user AS u ON u.id = ul.u_id";
				
			// FA8 Niranjan Commented for costcenter
//        if ($is_manager) {
//         $costcenterid = $DB->get_field('local_costcenter_permissions','costcenterid',array('userid'=>$USER->id));
//		    $sql .=" WHERE costcenter =$costcenterid";
//        }elseif (!is_siteadmin())
		
        $sql .= " WHERE u.id =$uid";
        $rs = $DB->get_records_sql($sql, array(), null, null);
		if($rs){
          $data = array();
          foreach ($rs as $log) {
			$completion_status = learning_plan_completions($log->id,$userid = NULL);
			$total_credits = learning_plan_completions_credits($log->id);
			
			if ($completion_status >= $log->credit_points)
			
			 $status = get_string('status_completed','block_learning_plan');
			else
			$status = get_string('status_not_completed','block_learning_plan');
            $row = array();
            $buttons = array();
            $add_training = get_string('add_training', 'block_learning_plan');
            $assign_learningplan_user = get_string('assign_learningplan_user', 'block_learning_plan');
            $courselist = $DB->get_fieldset_sql("select lp.t_id from {learning_plan_training} as lp INNER JOIN {course} as c ON lp.t_id=c.id where lp.lp_id=$log->id group by lp.t_id");
           $courses = implode(',',$courselist);
		  if(!empty($courses))
		  $DB->execute('delete from {learning_plan_training} where  lp_id='.$log->id.' AND t_id not in('.$courses.')');
            $courses_count = $DB->count_records_sql("select count('t_id') from {learning_plan_training} WHERE lp_id=$log->id");
            $users_count = $DB->count_records_sql("select count('u_id') from {learning_user_learningplan} WHERE lp_id=$log->id");
            $completed_lp_count = completed_learningplan_count($log->id);
			$grades = 'Grade:'.$log->grade;
			$career_track = 'Career Track:'.$log->career_track;
            $courses_count_link = 'Courses: ' . $courses_count . '';
            $users_count_link = 'Users: ' . $users_count . '';
			$completed_lp_count_link = html_writer::link('javascript:void(0)', 'Completed employes: ' . count($completed_lp_count) . '', array('id' => 'clpemp' . $log->id . '', 'onclick' => 'assign_manager(' . $log->id . ',"dialogclpemp")'));
            $PAGE->requires->event_handler('#deleteconfirm' . $log->id . '', 'click', 'M.util.tmahendra_show_confirm_dialog', array('message' => get_string('plan_delete', 'block_learning_plan'), 'callbackargs' => array('id' => $log->id, 'extraparams' => '&rem=remove&delete=' . $log->id . '&viewpage=1')));
			//           	$lpdates ='<span class="batchdatess" style ="align-left;">';
			//			//$lpdates.= '<b>'.$log->description.' </b>';
			//			//$lpdates = '<span class="span12 desktop-first-column" style="padding: 0px 10px;">'.$log->description.' </span>';
			//			
			//			$lpdates ='<span class="lp_batchdatess" style ="align-left;border: none;">';
			//			$lpdates.= '<div>Career Track: '."<b>$log->career_track</b>".'&nbsp&nbsp&nbsp';
			//			$lpdates.= 'Grade: '."<b>$log->grade</b>".'&nbsp&nbsp&nbsp</div>';
			//			//$lpdates .= '<div>Goal: '.$log->credit_points.' </b>&nbsp&nbsp</div>';
			//			$lpdates.= '<div>';
			//			if(!empty($total_credits))
			//				$lpdates.= 'Total Credits:  '."<b>$total_credits</b>".'&nbsp&nbsp&nbsp';
			//			else
			//				$lpdates.= 'Total Credits:  '. "<b>0</b>".' &nbsp&nbsp&nbsp';
			//			$lpdates.= 'Require Credit Points: '."<b>$log->credit_points</b>".' </b>&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp</div>';
			//			$lpdates.= '<div>Type: '."<b>$log->learning_type</b>".' &nbsp&nbsp&nbsp';
			//			$lpdates.= 'Status:  '."<b>$status</b>".'&nbsp&nbsp</div>';
			//			
			//			//$completion = $DB->record_exists('course_completions', array('course'=>$log->courseid,'userid'=>$USER->id));
			//			//if($completion){
			//			//	$lpdates .= get_string('status_completed','block_learning_plan');
			//			//}else{
			//			//	$lpdates .= get_string('status_not_completed','block_learning_plan');
			//			//}
			//			//$lpdates.= '<b>Elgibility Grade :'.$log->grade.' </b>';
			//			//$lpdates.= '<b>Career Track :'.$log->career_track.' </b>';
			//			$lpdates .='</span>';
			//$status_display='<span class="span12" style ="align-right;">';
			//$status_display .= '<b>Status: '.$status.' </b>';
			//$status_display .='</span>';
			
				if(!empty($total_credits)){
				$tcredits = $total_credits;
			}else{
				$tcredits = 0;
			}
			
			$lpdates= "<table class = 'lp_batchdatess'>
						<tbody>
						<tr>
						<td><i>Career Track</i><b><i> : ".$log->career_track."</i></b></td>
						<td><i>Grade</i><b><i> : ".$log->grade."</i></b></td>
						</tr>
						<tr>
						<td><i>Total Credits</i><b><i> : ".$tcredits."</i></b>
						<td><i>Require Credit Points</i><b><i> : ".$log->credit_points."</i></b></td>
						</tr>
						<tr>
						<td><i>Type</i><b><i> : ".$log->learning_type."</i></b></td>
						<td><i>Status</i><b><i> : ".$status."</i></b></td>
						</tr>
						</tbody>
						</table>";
						
						
			$innercontent = '<div id="'. $log->id .'" class="toogleplhide"><span class="lp_class_inner" ><div id="demo' . $log->id . '">
                            <ul>
                            <li><a href="' . $CFG->wwwroot . '/blocks/learning_plan/ajax.php?page=5&lp=' . $log->id . '">' . $courses_count_link . '</a></li>
                            </ul>
                           </div></span></div>';
            $costcenter_name = $DB->get_field('local_costcenter','fullname',array('id'=>$log->costcenter));
			$innercontent .= html_writer::script('$(function() {
                                    $( "#demo' . $log->id . '" ).tabs({
                                    beforeLoad: function( event, ui ) {
                                    ui.jqXHR.fail(function() {
                                                ui.panel.html(
                                                            "Couldn\'t load this tab. We\'ll try to fix this as soon as possible. " +
                                                             "If this wouldn\'t be a demo." );
                                                });
                                    ui.panel.html("<center><img src=\"' . $CFG->wwwroot . '/blocks/learning_plan/images/loading.gif\" /></center>")
                                    },
                                    collapsible: true,
                                    active: false
                                    });});');
			if(is_siteadmin())
			$costcenterinfo = '<span class="lp_ccinfo">'.get_string("pluginname","local_costcenter").':<b>'.$costcenter_name.'</b></span>';
            else
			$costcenterinfo ='';
			
			$row[] = '<div id="pl_'. $log->id .'" class="pl_newtoggle"  /*onclick="Show_Div('.$log->id.')"*/><h5 id="lp_heading" class="span12"><span id="arrow'. $log->id .'" class="test lpdownarrow"></span>' . format_string($log->learning_plan, false).'</h5>'.$lpdates.'<span id="lp_actions">'.'</span></div>'.$innercontent;
			
				//            if(is_siteadmin()){
				//				$row[] = $costcenter_name;
				//			}
			$table->data[] = new html_table_row($row);
		    }
			//echo html_writer::script('$(document).ready(function(){
			//	$("#lp_plan_info").DataTable();
			//	 $.fn.dataTable.ext.errMode = "throw";
			//	});
			//	');
			$table = html_writer:: table($table);
			return $table;
        
		}else{
			return false;
		}
    }