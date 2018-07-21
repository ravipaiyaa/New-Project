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
 *
 * @package local
 * @subpackage learningplan
 * @copyright  2016 Syed HameedUllah <hameed@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class local_learningplan_renderer extends plugin_renderer_base {
	public function all_learningplans($condtion){
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $systemcontext = context_system::instance();
        
	if(is_siteadmin()){
		/********This is for siteadmin to manage LEP*******/
			$sql="SELECT l.* FROM {local_learningplan} AS l WHERE l.id > 0 ORDER BY l.id DESC";
			$learning_plans = $DB->get_records_sql($sql);
		}elseif(has_capability('local/learningplan:create',$systemcontext) &&
		        has_capability('local/learningplan:manage',$systemcontext) && !has_capability('local/assign_multiple_departments:manage', $systemcontext)){
				/********This is for OH to manage LEP*******/
			$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
			$data=$DB->get_record('local_userdata',array('userid'=>$USER->id));
			$sql="SELECT l.* FROM {local_learningplan} AS l WHERE FIND_IN_SET(".$data->costcenterid.",l.costcenter) AND  !FIND_IN_SET(".$costcenter.",l.costcenter) ORDER BY l.id DESC";
			$learning_plans_depwise = $DB->get_records_sql($sql);
			
			$learning_plans=$learning_plans_depwise;
		}elseif(has_capability('local/learningplan:create',$systemcontext) && has_capability('local/assign_multiple_departments:manage', $systemcontext) && has_capability('local/learningplan:manage',$systemcontext)){
				/********This is for academy head to manage LEP*******/
			$costcenter=$DB->get_field('local_costcenter','id',array('shortname'=>'ACD'));
			$sql="SELECT l.* FROM {local_learningplan} AS l WHERE FIND_IN_SET(".$costcenter.",l.costcenter) AND
			l.id > 0  ORDER BY l.id DESC";
			
			$learning_plans_depwise = $DB->get_records_sql($sql);
			
			
			$learning_plans=$learning_plans_depwise;
		}else{
			$data=$DB->get_record('local_userdata',array('userid'=>$USER->id));
			
			$sql='SELECT * FROM {local_learningplan} AS l WHERE
			FIND_IN_SET('.$data->costcenterid.',l.costcenter) AND
			FIND_IN_SET("'.$data->band.'",l.band) AND
			FIND_IN_SET('.$data->department.',l.department) AND
			FIND_IN_SET('.$data->sub_sub_department.',l.subsubdepartment) AND
			FIND_IN_SET('.$data->subdepartment.',l.subdepartment )
			AND l.id > 0 AND l.visible=1 ORDER BY l.timemodified DESC';
			$learning_plans_depwise = $DB->get_records_sql($sql);
			$learning_plans=$learning_plans_depwise;
			//echo $sql;
		}
        if(empty($learning_plans)){
           return html_writer::tag('div', get_string('nolearningplans', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
        }else{
            $data = array();
            foreach($learning_plans as $learning_plan){
                $row = array();
                $plan_url = new moodle_url('/local/learningplan/plan_view.php', array('id' => $learning_plan->id));
                $plan_edit_url = new moodle_url('/local/learningplan/index.php', array('id' => $learning_plan->id));
				$plan_visible_url = new moodle_url('/local/learningplan/index.php', array('visible' => $learning_plan->id,'show'=>$learning_plan->id));
				//$plan_visible_url = new moodle_url('/local/learningplan/index.php', array('visible' => $learning_plan->id));
                if(!empty($learning_plan->startdate)){
                    $plan_startdate = date('d/m/Y', $learning_plan->startdate);
                }else{
                    $plan_startdate = 'N/A';
                }
                if(!empty($learning_plan->enddate)){
                    $plan_enddate = date('d/m/Y', $learning_plan->enddate);
                }else{
                    $plan_enddate = 'N/A';
                }
                if(empty($learning_plan->credits)){
                    $plan_credits = 'N/A';
                }else{
                    $plan_credits = $learning_plan->credits;
                }
				if(empty($learning_plan->usercreated)){
					$plan_usercreated = 'N/A';
				}else{
					$plan_usercreated = $learning_plan->usercreated;
					$user = $DB->get_record_sql("select * from {user} where id = $plan_usercreated");
					$created_user = fullname($user);
				}
                if($learning_plan->learning_type == 1){
                    $plan_type = 'Core Courses';
                }elseif($learning_plan->learning_type == 2){
                    $plan_type = 'Elective Courses';
                }
                if(!empty($learning_plan->location)){
                    $plan_location = $learning_plan->location;
                }else{
                    $plan_location = 'N/A';
                }
				/*code reverted -- starts here*/
				if(!empty($learning_plan->department)){
                    $plan_departments = $DB->get_records_sql('SELECT id, fullname FROM {local_costcenter} WHERE id IN('.$learning_plan->department.')');
					$plan_department = array();
					foreach($plan_departments as $plan_dep){
						$plan_department[] = $plan_dep->fullname;
					}
					$fullname = implode(',', $plan_department);
					$str_len = strlen($fullname);
					if($str_len > 32){
						$sub_str = substr($fullname, 0, 32);
						$plan_department = $sub_str.'<a class="toggle_department_'.$learning_plan->id.' view_more_toggle" onclick="target_audience_toggle(\'department\','.$learning_plan->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
						$plan_department .= '<div class="toggle_department_content_'.$learning_plan->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'department\','.$learning_plan->id.')" class="view_more_close">x</span><span class="view_more_content">'.$fullname.'</span></div>';
					}else{
						$plan_department = $fullname;
					}
                }else{
                    $plan_department = 'N/A';
                }
				if(!empty($learning_plan->subdepartment)){
                    //$plan_subdepartment = $DB->get_field('local_costcenter', 'fullname', array('id' => $learning_plan->subdepartment));
					$plan_subdepartments = $DB->get_records_sql('SELECT id, fullname FROM {local_costcenter} WHERE id IN('.$learning_plan->subdepartment.')');
					$plan_subdepartment = array();
					foreach($plan_subdepartments as $plan_subdep){
						$plan_subdepartment[] = $plan_subdep->fullname;
					}
					$fullname = implode(',', $plan_subdepartment);
					$str_len = strlen($fullname);
					if($str_len > 32){
						$sub_str = substr($fullname, 0, 32);
						$plan_subdepartment = $sub_str.'<a class="toggle_subdepartment_'.$learning_plan->id.' view_more_toggle" onclick="target_audience_toggle(\'subdepartment\','.$learning_plan->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
						$plan_subdepartment .= '<div class="toggle_subdepartment_content_'.$learning_plan->id.' view_more_toggle_content" style="display:none;right:0px;"><span onclick="target_audience_toggle(\'subdepartment\','.$learning_plan->id.')" class="view_more_close">x</span><span class="view_more_content">'.$fullname.'</span></div>';
					}else{
						$plan_subdepartment = $fullname;
					}
                }else{
                    $plan_subdepartment = 'N/A';
                }
				if(!empty($learning_plan->subsubdepartment)){
                    //$plan_subsubdepartment = $DB->get_field('local_costcenter', 'fullname', array('id' => $learning_plan->subsubdepartment));
					$plan_subsubdepartments = $DB->get_records_sql('SELECT id, fullname FROM {local_costcenter} WHERE id IN('.$learning_plan->subsubdepartment.')');
					$plan_subsubdepartments = array();
					foreach($plan_subdepartments as $plan_subdep){
						$plan_subsubdepartments[] = $plan_subdep->fullname;
					}
					$fullname = implode(',', $plan_subsubdepartments);
					$str_len = strlen($fullname);
					if($str_len > 32){
						$sub_str = substr($fullname, 0, 32);
						$plan_subsubdepartment = $sub_str.'<a class="toggle_subsubdepartment_'.$learning_plan->id.' view_more_toggle" onclick="target_audience_toggle(\'subsubdepartment\','.$learning_plan->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
						$plan_subsubdepartment .= '<div class="toggle_subsubdepartment_content_'.$learning_plan->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'subsubdepartment\','.$learning_plan->id.')" class="view_more_close">x</span><span class="view_more_content">'.$fullname.'</span></div>';
					}else{
						$plan_subsubdepartment = $fullname;
					}
                }else{
                    $plan_subsubdepartment = 'N/A';
                }
				/*code reverted -- ends here*/
                
                $action_icons = '';
                if (is_siteadmin() || has_capability('local/learningplan:visible', $systemcontext)) {
					if($learning_plan->visible == 0){
						if($condtion=='manage'){
						$action_icons .= html_writer::link($plan_visible_url,
                                                       html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/show'), 'title'=>'Show', 'class'=>'iconsmall')));
						}
					}elseif($learning_plan->visible == 1){
						if($condtion=='manage'){
						$action_icons .= html_writer::link($plan_visible_url,
                                                       html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/hide'), 'title'=>'Hide', 'class'=>'iconsmall')));
						}
					}
                }
                if (has_capability('local/learningplan:update', $systemcontext)) {
						if($condtion=='manage'){
                           $action_icons .= html_writer::link($plan_edit_url,
                                                        html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/edit'), 'title'=>'Edit', 'class'=>'iconsmall')));
						}
                }
                if (has_capability('local/learningplan:delete', $systemcontext)) {
					if($condtion=='manage'){
					$url = new moodle_url('/local/learningplan/index.php', array('id' => $learning_plan->id));
					$deleteicon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/delete'), 'title'=>'Delete', 'class'=>'iconsmall'));
                    $action_icons .= html_writer::link($url, $deleteicon, array('id' => "lplan_delete_confirm_".$learning_plan->id));
					$confirmationmsg = get_string('delete_confirm','local_learningplan', $learning_plan);
					
					$PAGE->requires->event_handler("#lplan_delete_confirm_".$learning_plan->id, 'click', 'M.util.moodle_show_confirm_dialog',
														array(
														'message' => $confirmationmsg,
														'callbackargs' => array('id' =>$learning_plan->id)
													));
					}
                }
                //Learning Plan
                $detail = html_writer::start_tag('div', array('class' => 'learning_plan_view'));
                    $detail .= html_writer::tag('h4', html_writer::link($plan_url, $learning_plan->name), array('class'=>'pull-left'));
                    $detail .= html_writer::start_tag('div', array('class'=>'action_icons pull-right'));
                    $detail .= $action_icons;
					$detail .= html_writer::end_tag('div');
                    $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_info pull-left span12 desktop-first-column'));
                        //Learning Plan Detailed info
                        $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_detail_info'));
                            $detail .= html_writer::start_tag('div');
                                $detail .= html_writer::start_tag('div', array('class'=>'row-fluid', 'style'=>'color:#857171;'));
									$detail .= html_writer::start_tag('div', array('class'=>'span12 pull-left desktop-first-column'));
										$detail .= html_writer::tag('b', '<span class="row-fluid pull-left">Details</span><span class="detail_short_underline"></span>', array('class'=>'span12 desktop-first-column pull-left text-left', 'style'=>'min-height:28px;color:#333;'));
										$detail .= '<div class="span4 pull-left desktop-first-column" style="min-height: 15px;"><span style="min-width: 135px;float: left;">Plan Type <span  style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;">'.$plan_type.'</span></div>';
										$detail .= '<div class="span4 pull-left" style="min-height: 15px;"><span style="min-width: 135px;float: left;">Credits <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;">'.$plan_credits.'</span></div>';
										$detail .= '<div class="span4 pull-left desktop-first-column" style="min-height: 15px;"><span style="min-width: 135px;float: left;">Creator <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;">'.$created_user.'</span></div>';
										//$detail .= '<div class="span6 pull-left desktop-first-column" style="min-height: 15px;"><span style="min-width: 135px;float: left;">Start date <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;">'.$plan_startdate.'</span></div>';
										//$detail .= '<div class="span6 pull-left" style="min-height: 15px;"><span style="min-width: 135px;float: left;">End date <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;">'.$plan_enddate.'</span></div>';
									$detail .= html_writer::end_tag('div');
									$detail .= html_writer::start_tag('div', array('class'=>'span12 pull-left desktop-first-column'));
										$detail .= html_writer::tag('b', '<span class="row-fluid pull-left">Target Audience</span><span class="detail_short_underline"></span>', array('class'=>'span12 desktop-first-column pull-left text-left', 'style'=>'min-height:28px;color:#333;'));
										$detail .= '<div class="span4 pull-left desktop-first-column" style="min-height: 15px;position:relative;"><span style="min-width: 135px;float: left;">Department <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;word-break: break-word;">'.$plan_department.'</span></div>';
										$detail .= '<div class="span4 pull-left" style="min-height: 15px;position:relative;"><span style="min-width: 135px;float: left;">Sub Department <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;word-break: break-word;">'.$plan_subdepartment.'</span></div>';
										$detail .= '<div class="span4 pull-left desktop-first-column" style="min-height: 15px;position:relative;"><span style="min-width: 155px;float: left;">Sub Sub Department <span style="margin-right: 10px;float: right;">:</span> </span><span style="color:#333;word-break: break-word;">'.$plan_subsubdepartment.'</span></div>';
									$detail .= html_writer::end_tag('div');
                                $detail .= html_writer::end_tag('div');
                                
                                $detail .= html_writer::start_tag('div');
                                if(is_siteadmin()){
									$detail .= html_writer::link($plan_url, 'More Info', array('class'=>'launch'));
									
								}else{
									/**Here The condtion for to view plan view with tabs or with out tabs By Ravi_369 **/
									if($condtion=='manage'){
										/*here condition allows you to show tabs*/
									$plan_url = new moodle_url('/local/learningplan/plan_view.php', array('id' => $learning_plan->id,'condtion'=>'manage'));
									$detail .= html_writer::link($plan_url, 'More info', array('class'=>'launch'));
									}else{
                                    $plan_url = new moodle_url('/local/learningplan/plan_view.php', array('id' => $learning_plan->id,'couid'=>$learning_plan->id));  										
										/** Here condtion that doesnot allow u to view of the tabs**/
									$detail .= html_writer::link($plan_url, 'More info', array('class'=>'launch'));
									}
									/*******End of condtion******/
								}
                                $detail .= html_writer::end_tag('div');
                            $detail .= html_writer::end_tag('div');
                            $detail .= html_writer::start_tag('div');
                            $detail .= html_writer::end_tag('div');
                        $detail .= html_writer::end_tag('div');
                        //Learning Plan Image
                        $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_image'));
                        $detail .= html_writer::end_tag('div');
                    $detail .= html_writer::end_tag('div');
                $detail .= html_writer::end_tag('div');
                $row[] = $detail;
                $data[] = $row;
            }
            $table = new html_table();
            $table->id = 'all_learning_plans';
            $table->head = array('');
            $table->data = $data;
            $return = html_writer::table($table);
			$return .= html_writer::script('$(document).ready(function(){
												$("table#all_learning_plans").dataTable({
													"language": {
															"paginate": {
																"next": ">",
																"previous": "<"
															  }
														},
													"iDisplayLength": 3,
													"bSort" : false,
													"aLengthMenu": [[3, 10, 25, 50, -1], [3, 10, 25, 50, "All"]]
												});
												$("table#all_learning_plans thead").css("display" , "none");
												$("#all_learning_plans_length").css("display" , "none");
										   });');
            $return .= '';
        }
        return $return;
    }
	/*Function to view of the single LEP detailed information in the plan_view page
	@param=planid -->LEP id	commented By Ravi_369 */
	public function single_plan_view($planid){
		
		global $CFG, $DB, $OUTPUT;
		$plan_record = $DB->get_record('local_learningplan', array('id' => $planid));
		$plan_description = !empty($plan_record->description) ? $plan_record->description : 'No Description available';
		$plan_objective = !empty($plan_record->objective) ? $plan_record->objective : 'No Objective available';
		/*Count of the enrolled users to LEP*/
		$total_enroled_users=$DB->get_record_sql('SELECT u.id,count(llu.userid) as data  FROM {local_learningplan_user} as llu JOIN {user} as u ON u.id=llu.userid WHERE llu.planid='.$planid.' AND u.deleted!=1');
		/*Count of the requested users to LEP*/
		$total_completed_users=$DB->get_records_sql("SELECT id FROM {local_learningplan_user} WHERE completiondate IS NOT NULL
													 AND status = 1 AND planid = $planid");
		$cmpltd = array();
		foreach($total_completed_users as $completed_users){
			$cmpltd[] = $completed_users->id;
		}
		
		$total_requested_users=$DB->count_records('local_learningplan_approval',array('planid'=>$planid));
		/*Count of the courses of LEP*/
		$total_assigned_course=$DB->count_records('local_learningplan_courses',array('planid'=>$planid));
		
		$total_mandatory_course=$DB->get_records_sql("SELECT id FROM {local_learningplan_courses} WHERE planid = $planid
													 AND nextsetoperator = 'and'");
		$mandatory = array();
		foreach($total_mandatory_course as $total_mandatory){
			$mandatory[] = $total_mandatory->id;
		}
		
		$total_optional_course=$DB->get_records_sql("SELECT id FROM {local_learningplan_courses} WHERE planid = $planid
													 AND nextsetoperator = 'or'");
		$optional = array();
		foreach($total_optional_course as $total_optional){
			$optional[] = $total_optional->id;
		}
		
		if(!empty($plan_record->startdate)){
			$plan_startdate = date('d/m/Y', $plan_record->startdate);
		}else{
			$plan_startdate = 'N/A';
		}
		if(!empty($plan_record->enddate)){
			$plan_enddate = date('d/m/Y', $plan_record->enddate);
		}else{
			$plan_enddate = 'N/A';
		}
		if(empty($plan_record->credits)){
			$plan_credits = 'N/A';
		}else{
			$plan_credits = $plan_record->credits;
		}
		if(empty($plan_record->usercreated)){
			$plan_usercreated = 'N/A';
		}else{
			$plan_usercreated = $plan_record->usercreated;
			$user = $DB->get_record_sql("select * from {user} where id = $plan_usercreated");
			$created_user = fullname($user);
		}
		if($plan_record->learning_type == 1){
			$plan_type = 'Core Courses';
		}elseif($plan_record->learning_type == 2){
			$plan_type = 'Elective Courses';
		}
		if($plan_record->approvalreqd == 1){
			$plan_needapproval = 'Yes';
		}else{
			$plan_needapproval = 'No';
		}
		if(!empty($plan_record->band)){
			$plan_location = $plan_record->band;
			$str_len = strlen($plan_record->band);
			if($str_len > 32){
				$sub_str = substr($plan_record->band, 0, 32);
				$plan_location = $sub_str.'<a class="emp_band_'.$plan_record->id.' view_more_toggle" onclick="target_audience_toggle(\'emp_band\','.$plan_record->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
				$plan_location .= '<div class="emp_band_content_'.$plan_record->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'emp_band\','.$plan_record->id.')" class="view_more_close">x</span><span class="view_more_content">'.$plan_record->band.'</span></div>';
			}
		}else{
			$plan_location = 'N/A';
		}
		/*code reverted -- starts here*/
		if(!empty($plan_record->department)){
			$sql="select fullname from {local_costcenter} where id IN ($plan_record->department)";
			$depart=$DB->get_records_sql($sql);
			//print_object($depart);
			$Dep='';
			foreach($depart as $dep){
				$Dep[]=$dep->fullname;
			}
			$plan_department=implode(',',$Dep);
			$str_len = strlen($plan_department);
			if($str_len > 32){
				$sub_str = substr($plan_department, 0, 32);
				$substr_department = $sub_str.'<a class="toggle_department_'.$plan_record->id.' view_more_toggle" onclick="target_audience_toggle(\'department\','.$plan_record->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
				$substr_department .= '<div class="toggle_department_content_'.$plan_record->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'department\','.$plan_record->id.')" class="view_more_close">x</span><span class="view_more_content">'.$plan_department.'</span></div>';
				$plan_department = $substr_department;
			}
			
		}else{
			$plan_department = 'N/A';
		}
		if(!empty($plan_record->subdepartment)){
			$sql="select fullname from {local_costcenter} where id IN ($plan_record->subdepartment)";
			$depart=$DB->get_records_sql($sql);
			//print_object($depart);
			$Dep='';
			foreach($depart as $dep){
				$Dep[]=$dep->fullname;
			}
			$plan_subdepartment=implode(',',$Dep);
			//$plan_subdepartment = $DB->get_field('local_costcenter', 'fullname', array('id' => $plan_record->subdepartment));
			$str_len = strlen($plan_subdepartment);
			if($str_len > 32){
				$sub_str = substr($plan_subdepartment, 0, 32);
				$substr_subdepartment = $sub_str.'<a class="toggle_subdepartment_'.$plan_record->id.' view_more_toggle" onclick="target_audience_toggle(\'subdepartment\','.$plan_record->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
				$substr_subdepartment .= '<div class="toggle_subdepartment_content_'.$plan_record->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'subdepartment\','.$plan_record->id.')" class="view_more_close">x</span><span class="view_more_content">'.$plan_subdepartment.'</span></div>';
				$plan_subdepartment = $substr_subdepartment;
			}
		}else{
			$plan_subdepartment = 'N/A';
		}
		if(!empty($plan_record->subsubdepartment)){
			$sql="select fullname from {local_costcenter} where id IN ($plan_record->subsubdepartment)";
			$depart=$DB->get_records_sql($sql);
			//print_object($depart);
			$Dep='';
			foreach($depart as $dep){
				$Dep[]=$dep->fullname;
			}
			$plan_subsubdepartment=implode(',',$Dep);
			//$plan_subsubdepartment = $DB->get_field('local_costcenter', 'fullname', array('id' => $plan_record->subsubdepartment));
			$str_len = strlen($plan_subsubdepartment);
			if($str_len > 32){
				$sub_str = substr($plan_subsubdepartment, 0, 32);
				$substr_subsubdepartment = $sub_str.'<a class="toggle_subsubdepartment_'.$plan_record->id.' view_more_toggle" onclick="target_audience_toggle(\'subsubdepartment\','.$plan_record->id.')" ><span class="view_more">&nbsp...View more</span><span class="hidden view_less">&nbsp...View less</span></a>';
				$substr_subsubdepartment .= '<div class="toggle_subsubdepartment_content_'.$plan_record->id.' view_more_toggle_content" style="display:none;"><span onclick="target_audience_toggle(\'subsubdepartment\','.$plan_record->id.')" class="view_more_close">x</span><span class="view_more_content">'.$plan_subsubdepartment.'</span></div>';
				$plan_subsubdepartment = $substr_subsubdepartment;
			}
		}else{
			$plan_subsubdepartment = 'N/A';
		}
			$return = '';
		
						$return .= html_writer::start_tag('div', array('class' => 'span12 desktop-first-column pull-left'));
						$return .= html_writer::start_tag('div', array('class' => 'plan_image pull-left'));
						$return .= html_writer::end_tag('div');
						$return .= html_writer::start_tag('div', array('class' => 'plan_detail pull-left', 'style' => 'width:100%;word-break:break-all;'));
						$return .= html_writer::start_tag('div', array('class' => 'plan_description span12 desktop-first-column pull-left', 'style' => 'margin-bottom: 15px;'));
						$return .= '<span class="info_label"><b>Description<span class="info_colon">:</span> </b></span><span class="info_value">'.$plan_description.'</span>';
						$return .= html_writer::end_tag('div');
						$return .= html_writer::start_tag('div', array('class' => 'plan_objective span12 desktop-first-column pull-left', 'style' => 'margin-bottom: 15px;'));
						$return .= '<span class="info_label"><b>Objective <span class="info_colon">:</span> </b></span><span class="info_value">'.$plan_objective.'</span>';
						$return .= html_writer::end_tag('div');
						$return .= html_writer::start_tag('div', array('class'=>'row-fluid pull-left', 'style'=>'color:#857171;margin-bottom: 15px;'));
						
						$return .= html_writer::start_tag('div', array('class'=>'span12 pull-left esktop-first-column'));
						
						$return .= '<div class="span12 pull-left">';
						$return .= html_writer::tag('b', '<span class="row-fluid pull-left">Details</span><span class="detail_short_underline"></span>', array('class'=>'span12 desktop-first-column pull-left text-left', 'style'=>'min-height:25px;color:#333;'));
						$return .= '<div class="span4 pull-left desktop-first-column">
											<span class="info_label" >Plan Type<span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$plan_type.'</span>
										</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
											<span class="info_label" >Required Approval <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$plan_needapproval.'</span>
										</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
											<span class="info_label" >Credits <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$plan_credits.'</span>
										</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
											<span class="info_label" >Creator <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$created_user.'</span>
										</div>';
										
						$return .= '<div class="span4 pull-left desktop-first-column">
						<span class="info_label" >Assigned Courses <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$total_assigned_course.'</span>
						</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
						<span class="info_label" /*style="min-width: 126px;*/" >Mandatory Courses <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.count($mandatory).'</span>
						</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
						<span class="info_label" >Optional Courses <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.count($optional).'</span>
						</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
						<span class="info_label" ">Enrolled Users <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.$total_enroled_users->data.'</span>
						</div>';
						$return .= '<div class="span4 pull-left desktop-first-column">
						<span class="info_label" >Completed Users <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;">'.count($cmpltd).'</span>
						</div>';
						$return .= '</div>';
						
						$return .= html_writer::end_tag('div');
						$return .= html_writer::start_tag('div', array('class'=>'span12 pull-left desktop-first-column'));
						$return .= html_writer::tag('b', '<span class="row-fluid pull-left">Target Audience</span><span class="detail_short_underline"></span>', array('class'=>'span12 desktop-first-column pull-left text-left', 'style'=>'min-height:25px;color:#333;'));
						$return .= '<div class="span6 pull-left desktop-first-column">
						<span class="info_label">Department <span class="info_colon">:</span> </span><span class="info_value" style="color:#333; min-height: 40px;">'.$plan_department.'</span>
						</div>';
						$return .= '<div class="span6 pull-left">
						<span class="info_label">Sub Department <span class="info_colon">:</span> </span><span class="info_value" style="color:#333; min-height: 40px;">'.$plan_subdepartment.'</span>
						</div>';
						$return .= '<div class="span6 pull-left desktop-first-column">
						<span class="info_label">Sub Sub Department <span class="info_colon">:</span> </span><span class="info_value" style="color:#333; min-height: 40px;">'.$plan_subsubdepartment.'</span>
						</div>';
						$return .= '<div class="span6 pull-left">
						<span class="info_label">Band <span class="info_colon">:</span> </span><span class="info_value" style="color:#333;min-height: 40px;">'.$plan_location.'</span>
						</div>';
						$return .= html_writer::end_tag('div');
						$return .= html_writer::end_tag('div');
						$return .= html_writer::end_tag('div');
						$return .= html_writer::end_tag('div');
		
		return $return;
	}
	/*End of the function of the single plan */
	
	public function plan_overview($planid){
		global $CFG, $DB, $OUTPUT;
		$plan_record = $DB->get_record('local_learningplan', array('id' => $planid));
		
		$return = '';
		return $return;
	}
	
	/***************Function For The Tabs View In The Learning
	@param $id=LEP id && $curr_tab=tab name
	Plan*****************/
	public function plan_tabview($id,$curr_tab,$condition){
		global $CFG, $DB, $OUTPUT;
			
		$courses_active = '';
		$users_active = '';
		$bulk_users_active = '';
		$request_users='';
		if($curr_tab == 'users'){
			$users_active = ' active ';
		}elseif($curr_tab == 'courses'){
			$courses_active = ' active ';
		}
		//elseif($curr_tab == 'bulk_users'){
		//	$bulk_users_active = ' active ';
		//}
		//<li class="'.$bulk_users_active.'">
		//	<a data-toggle="tab" href="#bulk_upload">
		//	<span><img src="'.$OUTPUT->pix_url('i/groups').'" title="Bulk Assign Users" /></span>
		//	Bulk Upload</a>
		//</li>
		elseif($curr_tab == 'request_user'){
			$request_users= ' active';
		}
		
		$total_enroled_users=$DB->get_record_sql('SELECT count(llu.userid) as data  FROM {local_learningplan_user} as llu JOIN {user} as u ON u.id=llu.userid WHERE llu.planid='.$id.' AND u.deleted!=1');
		$total_requested_users=$DB->count_records('local_learningplan_approval',array('planid'=>$id));
		$total_assigned_course=$DB->count_records('local_learningplan_courses',array('planid'=>$id));
		$return = '';
		$tabs = '<ul class="nav nav-tabs nav-justified">
						<li class="'.$courses_active.'">
							<a data-toggle="tab" href="#plan_courses">
								<span><img src="'.$OUTPUT->pix_url('i/course').'" title="Assign Courses" /></span>
								Courses</a>
						</li>
						<li class="'.$users_active.'">
							<a data-toggle="tab" href="#plan_users">
								<span><img src="'.$OUTPUT->pix_url('i/users').'" title="Assign Users" /></span>
								Users<span class="badge">'. $total_enroled_users->data .'</span></a>
						</li>
						
						<li class="'.$request_users.'">
							<a data-toggle="tab" href="#request_users">
								<span><img src="'.$OUTPUT->pix_url('i/groups').'" title="Bulk Assign Users" /></span>
								Requested users<span class="badge">'. $total_requested_users .'</span></a>
							</li>	
					  </ul>';
		$tabs .= '<div class="tab-content">';
		$tabs .= $this->learningplans_courses_tab_content($id, $curr_tab,$condition);
		$tabs .= $this->learningplans_users_tab_content($id, $curr_tab,$condition);
		//$tabs .= $this->learningplans_bulk_users_tab_content($id, $curr_tab,$condition);
		$tabs .= $this->learningplans_request_users_tab_content($id, $curr_tab,$condition);
		$tabs .= '</div>';
		$return .= $tabs;
		return $return;
	}
	/**********************End of the code in the Commented by Ravi_369*****************/
	
	/***********Function to view of course tab
	$planid=LEP_id $curr_tab="tab name"
	***************/
	public function learningplans_courses_tab_content($planid, $curr_tab,$condition){
		global $CFG, $DB, $USER,$OUTPUT;
        $systemcontext = context_system::instance();
		
		$return ='';
		
		$active_courses = ' ';
		if($curr_tab == 'courses'){
			$active_courses = ' in active';
		}
		$return .='<div id="plan_courses" class="tab-pane fade '.$active_courses.'">';
		if (has_capability('local/learningplan:assigncourses', $systemcontext)) {
			$return .= $this->learningplans_assign_courses_form($planid,$condition);
		}
		$return .='';
		$return .= $this->assigned_learningplans_courses($planid);
		$return .='';
		$return .= '</div>';
		return $return;
	}
   /**End of the function commented By Ravi_369**/
   
	/******************Function to tab view of bulk users uploads
	$planid=LEP_id $curr_tab="tab name"
	***************/ 
	public function learningplans_bulk_users_tab_content($planid, $designation, $department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment){
		global $CFG, $DB, $OUTPUT, $USER;
		$return ='';
		
		//$select_to_users= $this->select_to_users_of_learninplan($planid);/*function to select and display users in select box*/
		//$select_from_users= $this->select_from_users_of_learninplan($planid);/*function to select and display users in select box*/
		
		if(!is_null($designation) || !empty($department) || !empty($organization) || !empty($empnumber) || !empty($email) || !empty($band) || !empty($subdepartment) || !empty($sub_subdepartment)){
			$select_to_users = $this->select_to_users_of_learninplan($planid,$USER->id,$designation, $department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment);
			$select_from_users = $this->select_from_users_of_learninplan($planid,$USER->id,$designation, $department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment);
		}else{
			$select_to_users = $this->select_to_users_of_learninplan($planid,$USER->id,$designation,$department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment);
			$select_from_users = $this->select_from_users_of_learninplan($planid,$USER->id,$designation, $department,$empnumber,$organization,$email,$band,$subdepartment,$sub_subdepartment);
		}
		
		$return .='<div class="user_batches text-center">
					<form  method="post" name="form_name" id="assign_users_'.$planid.'" action="assign_courses_users.php" class="form_class" >
					<input type="hidden"  name="add_ownusers" value="<?php echo $add_users1 ?>" >
					<input type="hidden"  name="batchid" value="<?php echo $batchid ?>" >
					<fieldset>
					<ul class="button_ul">
					
					<li style="padding:18px; display:none"><label>Search</label>
					<input id="textbox" type="text"/>
					</li>
					<li><input type="button" id="select_remove" name="select_all" value="Select All">
					<input type="button" id="remove_select" name="remove_all" value="Remove All">
					</li>
					
					<li><select name="add_users[]" id="select-from" multiple size="15">';
	
        $return .= '<optgroup label="Selected member list ('.count($select_from_users).') "></optgroup>';
        if(!empty($select_from_users)){
			foreach($select_from_users as $select_from_user){
				if($select_from_user->id == $USER->id){
					$trainerid_exist=array();
				}else{
					$trainerid_exist="";
					//$trainerid_exist=$DB->get_record_sql("SELECT * FROM {local_learningplan_user} where userid=$select_from_user->id and planid=$planid");	
				}
				if((empty($trainerid_exist))){
					$symbol="";
					$check=$DB->get_record('local_learningplan_user',array('userid'=>$select_from_user->id,'status'=>1,'planid'=>$planid));
					if($check){
						$disable="disabled";
						$title="title='User Completed'";
					}else{
						$title="";
						$disable="";
					}
					$data_id=preg_replace("/[^0-9,.]/", "", $select_from_user->idnumber);
					$return .= "<option value=$select_from_user->id $disable $title>$symbol $select_from_user->firstname $select_from_user->lastname ($data_id)</option>";		
					//$PAGE->requires->event_handler('#eventconfirm'.$select_from_user->id .'', 'click', 'tmahendra_show_confirm_dialog', array('message' => get_string('deleteconfirm','local_custom_repository'), 'callbackargs' => array('confirmid' =>$select_from_user->id))); 
				}
			}
			foreach($select_from_users as $select_from_user){
				$return .= '<input type="hidden" name="planid" value=' . $select_from_user->id . ' />';
			}
		}else{
			$return .='<optgroup label="None"></optgroup>';
		}
	    $return .= '<input type="hidden" name="planid" value=' . $planid . ' />
					<input type="hidden" name="type" value="bulkusers" />';
		$return .=	'</select></li>
					</ul>
					<ul class="button_ul">
						
					<li><input type="submit" name="submit_users" value="add users" id="btn_add" style="width:98px;"></li>                    
					<li><input type="submit" name="submit_users" value="remove users" id="btn_remove"></li>
					</ul>
					
					<ul class="button_ul">
					<li><input type="button" id="select_add" name="select_all" value="Select All">
					<input type="button" id="add_select" name="remove_all" value="Remove All">
					</li>
					<li><select name="remove_users[]" id="select-to" multiple size="15">';
						
		$return .= '<optgroup label="Selected member list ('.count($select_to_users).') "></optgroup>';
		if(count($select_to_users) > 100){
			$return .= '<optgroup label="Too many users, use search."></optgroup>';
			$select_to_users = array_slice($select_to_users,0,100);
		}
		if(!empty($select_to_users)){
			foreach($select_to_users as $select_to_user){
				if($select_to_user->id == $USER->id){
					$trainerid_exist=array();
				}else{
					$trainerid_exist="";
					//$trainerid_exist=$DB->get_record_sql("SELECT * FROM {local_learningplan_user} where userid=$select_from_user->id and planid=$planid");	
				}
				$data_id=preg_replace("/[^0-9,.]/", "", $select_to_user->idnumber);
				if((empty($trainerid_exist))){
					$symbol="";
					$return .= "<option  value=$select_to_user->id >$symbol $select_to_user->firstname $select_to_user->lastname ($data_id)</option>";
				}
			}
		}else{
			$return .='<optgroup label="None"></optgroup>';
		}
						
		$return .='</select></li>
					</ul>
					</fieldset>
					</form>
					</div>';
						
		$return .="<script>
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
						
						
					</script>";								
		/*to check courses has the Learning plan enrolment or not*/
		$courses=$DB->get_records('local_learningplan_courses',array('planid'=>$planid));
		
		if($courses){/*If courses it self not assignes so to check condition*/
			$table = 'local_learningplan_courses'; ///name of table
			$conditions = array('planid'=>$planid); ///the name of the field (key) and the desired value
			$sort = 'id';
			$fields = 'id, courseid'; 
			$result = $DB->get_records_menu($table,$conditions,$sort,$fields);
            $count=count($result);
			/*finally get the count of records in total courses*/
			$data=implode(',',$result);
			$sql="select * from {enrol} where courseid IN ($data) and enrol='learningplan'";
			$check=$DB->get_records_sql($sql);
			$check_count=count($check);
			/*get the enrol records according to course*/
			if($check_count==$count){
				return $return;
			}else{
				//$return_msg ='Please apply Learning plan enrolment to all course';
				return $return_msg;
			}
		}
	}
	/*End of the function commented by Ravi_369*/
	
	/******Function to called in the bulk users upload
	$planid=LEP_id 
	*******/
	public function select_from_users_of_learninplan($planid,$userid, $designation, $department, $empnumber, $organization, $email, $band, $subdepartment, $sub_subdepartment){
		global $CFG, $DB, $OUTPUT;
		$sql="SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 ";
		if($planid!=0){
			$batch_users=$DB->get_fieldset_sql("SELECT userid FROM {local_learningplan_user} WHERE planid=$planid");
		}else{
			$batch_users=$DB->get_fieldset_sql("SELECT userid FROM {local_learningplan_user}");  
		}
		array_push($batch_users, 1);
		$batch_userss = implode(',',$batch_users);
		if(!empty($batch_userss)){
			$sql .=' AND ud.userid in(' . $batch_userss . ')';
		}
		
		if(!empty($empnumber)){
			if($empnumber !=='null' && $empnumber !=='-1'){
				$sql.= " AND u.idnumber IN({$empnumber})"; 
			}
		}
		
		if(!empty($organization)){
			if($organization !=='null' && $organization !=='-1'){ 
				$sql.= " AND ud.costcenterid IN({$organization})";
			}
		}
		
		if(!empty($email)){
			if($email !=='null' && $email !=='-1'){
				$sql.= " AND u.email IN({$email})";
			}
		}
		
		if(!empty($designation)){
			if($designation !=='null' && $designation !=='-1'){
				$sql.= " AND ud.designation IN({$designation})";
			}
		}
		
		if(!empty($department)){
			if($department !== null && $department !== '-1'){
				$sql.= " AND ud.department IN($department)";
			}
		}
		
		if(!empty($subdepartment)){
			if($subdepartment !=='null' && $subdepartment !=='-1'){
				$sql.= " AND ud.subdepartment IN({$subdepartment})";
			}
        }
		
		if(!empty($sub_subdepartment)){
			if($sub_subdepartment !=='null' && $sub_subdepartment !=='-1'){
				$sql.= " AND ud.sub_sub_department IN({$sub_subdepartment})";
			}
		}
		
		if(!empty($band)){
			if($band !=='null' && $band !=='-1'){
				$sql.= " AND ud.band IN({$band})";
			}
		}
		$users=$DB->get_records_sql($sql);
		
		return $users;
	}
	/*End of the function*/
	
	/*Function to called in the bulk users upload*/
	public function select_to_users_of_learninplan($planid, $userid, $designation, $department, $empnumber, $organization, $email, $band, $subdepartment, $sub_subdepartment){
		global $CFG, $DB, $OUTPUT;
		$users = $DB->get_record('local_learningplan',array('id'=>$planid));
		$us = $users->band;
		$array=explode(',',$us);
		$list=implode("','",$array);
		
		$sql = "SELECT u.* FROM {user} u JOIN {local_userdata} ud ON u.id =ud.userid WHERE u.id >1 AND u.deleted=0 AND u.suspended=0 ";
		
		if(!empty($empnumber)){
			if($empnumber !=='null' && $empnumber !=='-1'){
				$sql.= " AND u.idnumber IN({$empnumber})"; 
			}
		}
		
		if(!empty($organization)){
			if($organization !=='null' && $organization !=='-1'){ 
				$sql.= " AND ud.costcenterid IN({$organization})";
			}
		}elseif($users->costcenter){
			$systemcontext = context_system::instance();
            if((strlen($users->costcenter)==1) && $users->costcenter==1){
				$sql .=' AND ud.costcenterid!="" ';
			}else{
				$sql .=' AND ud.costcenterid IN ('.$users->costcenter.') ';
				
			}
		}else{
			$sql .=' AND ud.costcenterid!="" ';
		}
		
		if(!empty($email)){
			if($email !=='null' && $email !=='-1'){
				$sql.= " AND u.email IN({$email})";
			}
		}
		
		if(!empty($designation)){
			if($designation !=='null' && $designation !=='-1'){
				$sql.= " AND ud.designation IN({$designation})";
			}
		}
		
		if(!empty($department)){
			if($department !== null && $department !== '-1'){
				$sql.= " AND ud.department IN($department)";
			}
		}elseif($users->department!=''){
			$sql .=' AND ud.department IN ('.$users->department.') ';
		}else{
			//$sql.=' AND ud.department!="" ' ;
		}
		
		if(!empty($subdepartment)){
			if($subdepartment !=='null' && $subdepartment !=='-1'){
				$sql.= " AND ud.subdepartment IN({$subdepartment})";
			}
        }elseif($users->subdepartment!=''){
			$sql .=' AND ud.subdepartment IN ('.$users->subdepartment.') ';
		}else{
			//$sql.=' AND ud.subdepartment!="" ';
		}
		
		if(!empty($sub_subdepartment)){
			if($sub_subdepartment !=='null' && $sub_subdepartment !=='-1'){
				$sql.= " AND ud.sub_sub_department IN({$sub_subdepartment})";
			}
		}elseif($users->subsubdepartment!=''){
			$sql .=' AND ud.sub_sub_department IN('.$users->subsubdepartment.') ';
		}else{
			//$sql.=' AND ud.sub_sub_department!="" ';
		}
		
		if(!empty($band)){
			if($band !=='null' && $band !=='-1'){
				$sql.= " AND ud.band IN({$band})";
			}
		}elseif($users->band!=''){
			$sql .=" AND ud.band IN('$list')";
		}else{
			//$sql .=' AND ud.band!=""  ';
		}
		
		if($planid!=0){
			$batch_users=$DB->get_fieldset_sql("SELECT userid FROM {local_learningplan_user} WHERE planid=$planid");
		}else{
			$batch_users=$DB->get_fieldset_sql("SELECT userid FROM {local_learningplan_user}");  
		}
		array_push($batch_users, 1);
		$batch_userss = implode(',',$batch_users);
		if(!empty($batch_userss)){
			$sql .=' AND ud.userid not in(' . $batch_userss . ')';
		}
		
		$users=$DB->get_records_sql($sql);
		return $users;
	}
	/*End of the function*/
	
	/*Function to view the tab of the requested users*/
	public function learningplans_request_users_tab_content($planid, $curr_tab,$condition){
		global $CFG, $DB, $USER,$OUTPUT;
        $systemcontext = context_system::instance();
		
		$total_requested_users=$DB->count_records('local_learningplan_approval',array('planid'=>$planid));
		$return ='';
		
		$active_courses = ' ';
		if($curr_tab == 'request_user'){
			$active_courses = ' in active';
		}
		$return .='<div id="request_users" class="tab-pane fade '.$active_courses.'">
		<h3>Requested users</h3>
		';
		$table = new html_table();
		$head = array('Learning Path Name','Requested users','Approved users','Rejected users');
		if(empty($total_requested_users)){
			$table->head = '';
		}elseif(!empty($total_requested_users)){
			$table->head = $head;
		}
		$table->id = 'publishedexams';    
		$out =  html_writer::table($table);
		if($planid==0){
				
				$is_teammanager=$DB->record_exists('local_userdata',array('supervisorid'=>$USER->id));
				$sql = "SELECT f.* ,fa.id as fapprovalid FROM {local_learningplan} f JOIN
				{local_learningplan_approval} fa ON f.id = fa.planid";
				
				if(has_capability('local/learningplan:manage', $systemcontext) && (!is_siteadmin($USER->id))){
				$sql .= " AND f.costcenter
				IN (
				SELECT c.id
				FROM mdl_local_userdata ud
				JOIN mdl_local_costcenter c ON ud.costcenterid = c.id
				AND ud.userid =$USER->id
				)";
				}
		}else{
			$sql = "SELECT f.* ,fa.id as fapprovalid FROM {local_learningplan} f JOIN
            {local_learningplan_approval} fa ON f.id = fa.planid where fa.planid=$planid";
						
		}
		$drcourses = $DB->get_records_sql($sql);
		if(!empty($drcourses)){
			$return .=$out;
		}else{
			$return .= "<div class='alert alert-info text-center' style='float:left;margin-top:10px;width:96%;padding:5px 1.9%;'>
		   				No data available in table
		   			</div>";
		}
		require_once($CFG->dirroot . '/local/learningplan/approvals/lep_custom.php');
		$return .= '</div>';
		return $return;


	}
	/*End of the function Commented By Ravi_369*/
	
	/*Function to view the users and assign users*/
	public function learningplans_users_tab_content($planid, $curr_tab,$condition){
		global $CFG, $DB, $OUTPUT;
        $systemcontext = context_system::instance();
		
		$return = '';
		
		$active_users = ' ';
		if($curr_tab == 'users'){
			$active_users = ' in active';
		}
		$return .= '<div id="plan_users" class="tab-pane fade '.$active_users.'">';
		if (has_capability('local/learningplan:assignhisusers', $systemcontext)) {
			/*to check courses has the Learning plan enrolment or not*/
			$courses=$DB->get_records('local_learningplan_courses',array('planid'=>$planid));
			if($courses){
				$table = 'local_learningplan_courses'; ///name of table
				$conditions = array('planid'=>$planid); ///the name of the field (key) and the desired value
				$sort = 'id';
				$fields = 'id, courseid'; 
				$result = $DB->get_records_menu($table,$conditions,$sortid,$fields);
				$count=count($result);
				/*finally get the count of records in total courses*/
				$data=implode(',',$result);
				$sql="select * from {enrol} where courseid IN ($data) and enrol='learningplan'";
				$check=$DB->get_records_sql($sql);
				$check_count=count($check);
				/*get the enrol records according to course*/
				if($check_count == $count){
					/********The Below query written to check the all coures have the condition if two courses has condition 0
					then while completion cron runs gets error to avoid we should make them to submit*******/
					$courses_zero_count=$DB->get_records('local_learningplan_courses',array('planid'=>$planid,'nextsetoperator'=>0));
					if(count($courses_zero_count)==1 || count($courses_zero_count)==0){
						$return.='<a href="'.$CFG->wwwroot.'/local/learningplan/lpusers_enroll.php?id='.$planid.'" class="show">
										<span class="pull-right knowmore assigning button">Bulk Upload</span></a>';
						$return .= $this->learningplans_assign_users_form($planid,$condition);
					}else{
						$return .='Please apply Learning plan Condtion to all course please submit button in the coures tab';	
					}
				}else{
					$return .='Please apply Learning plan enrollment to all courses';
				}
			}
		}
		$return .= $this->assigned_learningplans_users($planid);
		$return .= '</div>';
		
		return $return;
	}
	/*End of the function*/
	
	public function learningplans_assign_courses_form($planid,$condition){
		global $CFG, $DB, $OUTPUT;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
		//print_object($condition);
		$learningplan_lib = new learningplan;
		$sql = "SELECT courseid, planid FROM {local_learningplan_courses} WHERE planid = $planid";
		$existing_plan_courses = $DB->get_records_sql($sql);
		//$existing_plan_courses_records = $DB->get_records('local_learningplan_courses', array('planid' => $planid));
		$return = '';
			$assign_button = '<a class="pull-right assigning " onclick="assign_courses_form_toggle('.$planid.')" id="plan_assign_courses_'.$planid.'">'.get_string('assign_courses', 'local_learningplan').'</a>';
			$return .= $assign_button;
			$return .= '<div class="assign_courses_container">';
			$courses = $learningplan_lib->learningplan_courses_list($planid);
			$return .= '<form autocomplete="off"  id="lp_assign_course_'. $planid .'" class="mform" action="assign_courses_users.php" method="post"';
            $return .= '<div>
							<div id="fitem_id_t_id[]" class="fitem fitem_fselect ">
							<div class="fitemtitle">
							<label for="learning_plan_courses[]">Select courses</label>
							</div>
							<div class="felement fselect">
							<select name="learning_plan_courses[]" row="2" column="10" multiple class="learningplan-assign-course" id="assign-course-select' . $planid . '">';
			if(!empty($courses)){
				foreach ($courses as $key => $value) {
					if(array_key_exists($key, $existing_plan_courses)){
						//$return .= '<option selected=selected value=' . $key . '>' . $value . '</option>';
					}else{
						$return .= '<option value=' . $key . '>' . $value . '</option>';
					}
				}
			}else{
				$return .= '<option></option>';
			}
            $return .= "</select>
						</div>
						</div>";
			$return .= '<input type="hidden" name="planid" value=' . $planid . ' />
								<input type="hidden" name="type" value="assign_courses" />
								<input type="hidden" name="condtion" value="' . $condition . '" />';
            $return .= '<div id="fitem_id_submitbutton" class="fitem fitem_actionbuttons fitem_fsubmit">
								<div class="felement fsubmit">
									<input type="submit" id="submit_learningplan_courses' . $planid . '" class="form-submit" value="Assign" />
								</div>
							</div>';
            
            $return .= '</form>';
		$return .= '</div>';
		
		return $return;
	}
	
	public function learningplans_assign_users_form($planid,$condition){
		global $CFG, $DB, $OUTPUT;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
		require_once($CFG->dirroot.'/local/filterclass.php');
		
		$filter_class = new custom_filter; 
		//$users = $filter_class->get_all_users_id_fullname($planid);/*Filter page added this function*/
		
		$sql = "SELECT userid, planid FROM {local_learningplan_user} WHERE planid = $planid";
		$existing_plan_users = $DB->get_records_sql($sql);
		$return = '';
			$assign_button = '<a class="pull-right assigning " onclick="assign_users_form_toggle('.$planid.')" id="plan_assign_users_'.$planid.'">'.get_string('assign_users', 'local_learningplan').'</a>';
			$return .= $assign_button;
			$return .= '<div class="assign_users_container">';
				$return .= '<form autocomplete="off" id="assign_users_'.$planid.'" action="assign_courses_users.php" method="post" class="mform">';
					$return .= '<fieldset class="hidden">
									<div>
										<div id="fitem_id_t_id[]" class="fitem fitem_fselect ">
											<div class="fitemtitle">
												<label for="id_u_id[]">Select users</label>
											</div>
											<div class="felement ftext">
												<select name="learning_plan_users[]" id="id_lpassignusers" size="10" multiple class="learningplan-assign-users">';
					//foreach ($users as $key => $value) {
					//	if(array_key_exists($key, $existing_plan_users)){
					//		//$return .= '<option selected=selected value=' . $key . '>' . $value . '</option>';
					//	}else{
					//		$return .= '<option value=' . $key . '>' . $value . '</option>';
					//	}
					//}
									$return .= "</select>
											</div>
										</div>
									</div>
								</fieldset>";
					$return .= '<input type="hidden" name="planid" value=' . $planid . ' />
					            <input type="hidden" name="condtion" value="' . $condition . '" />
								<input type="hidden" name="type" value="assign_users" />';
					$return .= '<fieldset class="hidden">
									<div>
										<div id="fitem_id_submitbutton" class="fitem fitem_actionbuttons fitem_fsubmit">
											<div class="felement fsubmit">
												<input type="submit" class="form-submit" value="Assign" />
											</div>
										</div>';
						$return .= '</div>
								</fieldset>
							</form>';
			$return .= '</div>';
		return $return;
	}
	/****Function to view the  course and functionality with the sortorder @param $planid=LEP_id****/
	public function assigned_learningplans_courses($planid){
		global $CFG, $DB, $OUTPUT, $PAGE;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
		require_once($CFG->dirroot.'/local/includes.php');
		
        $systemcontext = context_system::instance();
		
		$learningplan_lib = new learningplan;
		$includes = new user_course_details;
		
		$courses = $learningplan_lib->get_learningplan_assigned_courses($planid);
		
		$return = '';
		$return .='<form action="assign_courses_users.php" method="post">';
		$return .= html_writer::tag('h3', get_string('assigned_courses', 'local_learningplan'), array());
		if(empty($courses)){
			$return .= html_writer::tag('div', get_string('nolearningplancourses', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
		}else{
			
			$table_data = array();
			/*******To check the highest sortorder of courses below query written and to compare list of courses*******/
			$sql="Select id,sortorder from {local_learningplan_courses} where planid=$planid order by sortorder DESC limit 1 ";
		 	$find=$DB->get_record_sql($sql);
			/****End of the query****/
			
			/*************Below query written to check the users assigned to LEP or NOT and Disable submit button************************/
			$userscount=$DB->get_record('local_learningplan_user',array('planid'=>$planid));
			/*end of query*/
			
			/******* The below query has been written taken count if we have submitted condition and later we added new course then submit should open************/
			$courses_zero_count=$DB->get_records('local_learningplan_courses',array('planid'=>$planid,'nextsetoperator'=>0));
			/*end of query*/
			
			
			
			if($userscount && (count($courses_zero_count)==1 || count($courses_zero_count)==0)){
				$disbaled_button="disabled";
			}else{
				$disbaled_button="";
			}
			
			//print_object(count($courses_zero_count));
			/*List of courses making list of course*/		
            foreach($courses as $course){
				
				if($course->next=='and'){
					//echo "checked";
					$select='echo checked="checked"';
					
				}elseif($course->next=='or'){
					
								$select='';
							}
				
				$startdiv ='<div class="lp_course_sortorder" id="dat'.$course->id.'">';
				$enddiv='<div>';
				$course_url = new moodle_url('/course/view.php', array('id'=>$course->id));
				$course_view_link = html_writer::link($course_url, $course->fullname, array());
				$course_summary_image_url = $includes->course_summary_files($course);
				$course_summary = empty($course->summary) ? 'Course summary not provided' : $course->summary;
				$course_total_activities = $includes->total_course_activities($course->id);
				$course_total_activities_link = html_writer::link($course_url, $course_total_activities, array());
				
				$actions = '';/****actions like delete and move up and down****/
				$buttons= ''; /****buttons are select box****/
				
				if (has_capability('local/learningplan:assigncourses', $systemcontext)) {
					
					$unassign_url = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid, 'unassigncourse' => $course->lepid));
					$unassign_link = html_writer::link($unassign_url,
													   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/delete'), 'class' => 'iconsmall', 'title' => 'Unassign'))
													   , array('class' => 'pull-right','id' => 'unassign_course_'.$course->lepid.''));
					
													
					if($course->sortorder==0){ /****condtion to check the sortorder and make arrows of up and down
											    for the first record ot course*****/
					//print_object($course->next);exit;
						
							$unassign_url1 = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid,'instance' => $course->lepid, 'order' => 'down'));
							$unassign_link1 = html_writer::link($unassign_url1,
														   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/down'), 'class' => 'iconsmall', 'title' => 'Unassign'))
														   , array('class' => 'pull-right'));
							
							if($disbaled_button==""){
							$actions .= $unassign_link1; /*Arrows down for first course*/
							}
							/*condition for the select the dropdown if already selected*/
							/*Select box*/
							$buttons .='<div style="float:left; text-align:center;">										
												<label class="switch">
												<input class="switch-input" type="checkbox" id="next_val'.$course->id.'" value="'.$course->id.'" "'.$select.'">
												<span class="switch-label" data-on="Man" data-off="Opt"></span> 
												<span class="switch-handle"></span> 
											</label>
							
							<input type="hidden" value="'.$course->lepid.'" id="courseid'.$course->lepid.'" name="row[]">
							<input type="hidden" value="'.$planid.'" name="plan">
							</div>';
							
							/*End of the select box*/
							$select='';
					}elseif($course->sortorder==$find->sortorder){/*condition to check the last course and make the up arrow*/
						
							$unassign_url2 = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid,'instance' => $course->lepid, 'order' => 'up'));
							$unassign_link_up = html_writer::link($unassign_url2,
															   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/up'), 'class' => 'iconsmall', 'title' => 'Unassign'))
															   , array('class' => 'pull-right'));
							if($disbaled_button==""){
							$actions .=$unassign_link_up;
							}
							$buttons .='<div style="float:left; text-align:center;">										
												<label class="switch">
												<input class="switch-input" type="checkbox" id="next_val'.$course->id.'" value="'.$course->id.'" "'.$select.'">
												<span class="switch-label" data-on="Man" data-off="Opt"></span> 
												<span class="switch-handle"></span> 
											</label>
							
							<input type="hidden" value="'.$course->lepid.'" id="courseid'.$course->lepid.'" name="row[]">
							<input type="hidden" value="'.$planid.'" name="plan">
							</div>';
							
							
					}else{ /*Else condition Not for first and last record should have the both arrows*/
						
							
							$unassign_url2 = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid,'instance' => $course->lepid, 'order' => 'up'));
							$unassign_link1 = html_writer::link($unassign_url2,html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/up'), 'class' => 'iconsmall', 'title' => 'Unassign'))													   , array('class' => 'pull-right'));
							
							$unassign_url2 = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid,'instance' => $course->lepid, 'order' => 'down'));
							$unassign_link_down = html_writer::link($unassign_url2,
															   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/down'), 'class' => 'iconsmall', 'title' => 'Unassign'))
															   , array('class' => 'pull-right'));
							if($disbaled_button==""){
							$actions .=$unassign_link_down;
							$actions .= $unassign_link1;
							}
							/*select box*/
							$buttons .='<div style="float:left; text-align:center;">										
												<label class="switch">
												<input class="switch-input" type="checkbox" id="next_val'.$course->id.'" value="'.$course->id.'" "'.$select.'">
												<span class="switch-label" data-on="Man" data-off="Opt"></span> 
												<span class="switch-handle"></span> 
											</label>
							
							<input type="hidden" value="'.$course->lepid.'" id="courseid'.$course->lepid.'" name="row[]"></div>';
							/*end of the select box*/
							$courseid_condition[]=$course->lepid;
							$select='';
					}
					
							$confirmationmsg = get_string('unassign_courses_confirm','local_learningplan', $course);
						
							$PAGE->requires->event_handler("#unassign_course_".$course->lepid, 'click', 'M.util.moodle_show_course_confirm_dialog',
														array('message' => $confirmationmsg,
														'callbackargs' => array('planid' =>$planid, 'courseid' =>$course->lepid)
													));
				     
							$actions .= $unassign_link;
				}
			
                $table_row = array();
				$course_data = '';
				$course_data .= $startdiv;
			
				if($course->sortorder == 0){/*Condtion to set the enable to first sortorder*/
					$disable_class1 = ' '; /*Empty has been sent to class*/
				}
				$course_data .= '<div class="course_complete_info row-fluid pull-left '.$disable_class1.'" id="course_info_'.$course->id.'" >';	
				$course_data .= '<h4 style="padding-left:10px;">'.$course_view_link.'<div class="pull-right" style="min-width:135px;">'.$buttons.$actions.'</div></h4>';
				
								
				if(!is_siteadmin()){
				$course_data .= '<div class="course_image_comtainer pull-left span3 desktop-first-column">
				<img class="learningplan_course_image" src="'.$course_summary_image_url.'" title="'.$course->fullname.'"/>
				</div>';
				}
				if(!is_siteadmin()){
				$course_data .= '<div class="course_data_container pull-left span5 desktop-first-column">';
				$course_data .= '<div class="course_summary">';
				$course_data .= $course_summary;
				$course_data .= '</div>';
				$course_data .= '</div>';
				}
				if(!is_siteadmin()){
				$course_data .= '<div class="course_data_container pull-right span4 desktop-first-column">';
				$course_data .= '<div class="course_activity_details text-right">';
				$course_data .= '<span style="font-size:18px;line-height:30px;">Total activities : </span><span style="font-size:25px;">'.$course_total_activities_link.'</span>';
				$course_data .= '</div>';
				$course_data .= html_writer::link($course_url, 'Launch', array('class'=>'launch', 'style'=>'margin-top:28px;'));
				}
				$course_data .= '</div>';
				//$course_data .= $buttons;
				$course_data .= '</div>';
				$course_data .=$submitbuttons;
				$course_data .=html_writer::script("$('#next_val".$course->id."').click(function() {
											var checked = $(this).is(':checked');
											
										if(checked){
											   var checkbox_value = '';
											   var plan=$planid;
											   var value='and';
											  checkbox_value = $(this).val();
											 
										}else{
										    var plan=$planid;
											var checkbox_value = '';
											 var value='or';
											checkbox_value = $(this).val();
										}
											$.ajax({
											type: 'POST',
											url: M.cfg.wwwroot + '/local/learningplan/ajax.php?course='+checkbox_value+'&planid='+plan+'&value='+value,
											data: { checked : checked },
											success: function(data) {
										
											},
											error: function() {
											alert('it broke');
											},
											complete: function() {
										
											}
											});
										});
										$(document).ready(function() {
				$('#assign-course-select$planid').select2();});");
				
				$course_data .='';
				$course_data .=$enddiv;
				$table_row[] = $course_data;
				
					$table_data[] = $table_row;
				
			
			
			}
			
			$table = new html_table();
			$table->head = array('');
			$table->id = 'learning_plan_courses_admin_view';
			$table->data = $table_data;
			$return .= html_writer::table($table);
			$return .='</form>';
			$return .= html_writer::script('$(document).ready(function(){
												//$("table#learning_plan_courses_admin_view").dataTable({
													//"language": {
													//	"paginate": {
													//		"next": ">",
													//		"previous": "<"
													//	  }
													//},
												//	"iDisplayLength": 3,
												//	"aLengthMenu": [[3, 10, 25, 50, -1], [3, 10, 25, 50, "All"]],
												//	"ordering": false
												//});
												$("table#learning_plan_courses_admin_view thead").css("display" , "none");
										   });');
			
		}
		
		return $return; 
	}
	/******End of the function of the which has sortorder and condition for the courses*******/
	public function assigned_learningplans_users($planid){
		global $CFG, $DB, $OUTPUT, $PAGE;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
        $systemcontext = context_system::instance();
		
		$learningplan_lib = new learningplan;
		
		$users = $learningplan_lib->get_learningplan_assigned_users($planid);
		
		$return = '';
		
		$return .= html_writer::tag('h3', get_string('assigned_users', 'local_learningplan'), array());
		if(empty($users)){
			$return .= html_writer::tag('div', get_string('nolearningplanusers', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
		}else{
			$table_data = array();
            foreach($users as $user){
				$course_url = new moodle_url('/local/learningplan/local_learningplan_courses.php', array('planid'=>$planid,'id'=>$user->id));
				$courses_link = html_writer::link($course_url, 'View more', array('id'=>$user->id));
				if($user->status==1){
					$completed="Completed..."." ".$courses_link;
				}  
				$user_url = new moodle_url('/local/users/profile.php', array('id'=>$user->id));
				$user_profile_link = html_writer::link($user_url, fullname($user), array());
				$start_date = empty($user->timecreated) ? 'N/A' : date('d M Y',$user->timecreated);
				$completion_date = empty($user->completiondate) ? 'N/A' : date('d M Y',$user->completiondate); 
				$status = empty($user->status) ? 'Not Completed' : $completed;
				
				if (has_capability('local/learningplan:assignhisusers', $systemcontext)) {
					$unassign_url = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid, 'unassignuser' => $user->id));
					$unassign_link = html_writer::link($unassign_url,
													html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/delete'), 'class' => 'iconsmall', 'title' => 'Unassign'))
													, array('id' => 'unassign_user_'.$user->id.''));
					if($completed=="Completed..."." ".$courses_link){
						$unassign_link1 = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'class' => 'iconsmall', 'title' => 'Completed'));
						$actions = $unassign_link;
					}
					$confirmationmsg = get_string('unassign_users_confirm','local_learningplan', $user);
							
					$PAGE->requires->event_handler("#unassign_user_".$user->id, 'click', 'M.util.moodle_show_user_confirm_dialog',
														array(
														'message' => $confirmationmsg,
														'callbackargs' => array('planid' =>$planid, 'userid' =>$user->id)
													));
					/*This query amd condition is used to check the completed users should not be deleted*/
					$check=$DB->get_record('local_learningplan_user',array('userid'=>$user->id,'status'=>1,'planid'=>$planid));
					if($check){
					$actions = $unassign_link1;
					}else{
					$actions = $unassign_link;
					}
					
					$table_header = get_string('learning_plan_actions', 'local_learningplan');
				}else{
					$actions = '';
					$table_header = '';
				}
		   		
                $table_row = array();
				$table_row[] = $user_profile_link;
				$table_row[] = $start_date;
				$table_row[] = $completion_date;
				$table_row[] = $status;
				if (has_capability('local/learningplan:assignhisusers', $systemcontext)) {
					$table_row[] = $actions;
				}
				
				$table_data[] = $table_row;
			}
			$table = new html_table();
			$table->id = 'learning_plan_users';
			$table->head = array(get_string('username', 'local_learningplan'),
								 get_string('start_date', 'local_learningplan'),
								 get_string('completion_date', 'local_learningplan'),
								 get_string('learning_plan_status', 'local_learningplan')
								 );
			if (has_capability('local/learningplan:assignhisusers', $systemcontext)) {
				$table->head[] = get_string('learning_plan_actions', 'local_learningplan');
			}
			$table->align = array('left', 'center', 'center', 'center', 'center');
			$table->data = $table_data;
			
			$return .= html_writer::table($table);
			$return .= html_writer::script('$(document).ready(function(){
												$("table#learning_plan_users").dataTable({
													language: {
														"paginate": {
															"next": ">",
															"previous": "<"
														  }
													}
												});
												//$("table#learning_plan_users thead").css("display" , "none");
										   });');
		}
		
		return $return;
	}
	
	public function assigned_learningplans_courses_employee_view($planid, $userid,$condition){
		global $CFG, $DB, $OUTPUT, $PAGE,$USER;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
		require_once($CFG->dirroot.'/local/includes.php');
		
        $systemcontext = context_system::instance();
		
		$learningplan_lib = new learningplan;
		$includes = new user_course_details;
		
		$courses = $learningplan_lib->get_learningplan_assigned_courses($planid);
		//print_object($courses);
		$return = '';
		$return .= html_writer::tag('h3', get_string('assigned_courses', 'local_learningplan'), array());
		if(empty($courses)){
			$return .= html_writer::tag('div', get_string('nolearningplancourses', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
		}else{
			$table_data = array();
            foreach($courses as $course){
				//print_object($course);
				/**************To show course completed or not********/
				$sql="select id from {course_completions} as cc where userid=".$USER->id." and course=".$course->id." and timecompleted!=''";
			   
				$completed=$DB->get_record_sql($sql);
			    
				if(!empty($completed)){/**********Condition and button********/
					//$cpmpleted_buttons ='<h4 class=""><span class="label label-default",style="text-align:center;">Completed</span></h4>';
				}else{
					//$cpmpleted_buttons="";
				}
				$course_url = new moodle_url('/course/view.php', array('id'=>$course->id));
				$course_view_link = html_writer::link($course_url, $course->fullname, array());
				$course_summary_image_url = $includes->course_summary_files($course);
				$course_summary = empty($course->objective) ? 'Course Summary not provided' : $course->summary;
				$course_objective = empty($course->objective) ? 'Course Objective not provided' : $course->objective;
				$course_total_activities = $includes->total_course_activities($course->id);
				$course_total_activities_link = html_writer::link($course_url, $course_total_activities, array());
				$course_completed_activities = $includes->user_course_completed_activities($course->id, $userid);
				$course_completed_activities_link = html_writer::link($course_url, $course_completed_activities, array());
				$course_pending_activities = $course_total_activities - $course_completed_activities;
				$course_pending_activities_link = html_writer::link($course_url, $course_pending_activities, array());
				
				$actions = '';
				$buttons = '';
				/*Select box*/
				if($course->next=='or'){ $select='selected';}else{
								$select='';
							}/*condition for the select the dropdown if already selected*/
							/*Select box*/
				if($course->next=='or' || $course->next=='and'){			
							
					if($course->next=='and'){
						$buttons .='<h4 class="course_sort_status"><span class="label label-default mandatory-course" >Mandatory</span></h4>';
					}
					elseif($course->next=='or'){
						$buttons .='<h4 class="course_sort_status"><span class="label label-default optional-course" >Optional</span></h4>';
					}		
				}
							/*End of the select box*/
				if (has_capability('local/learningplan:assigncourses', $systemcontext)) {
					if($condition=='view'){
						
					}else{
					
					$unassign_url = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid, 'unassigncourse' => $course->id));
					$unassign_link = html_writer::link($unassign_url,
													   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/delete'), 'class' => 'iconsmall', 'title' => 'Unassign'))
													   , array(
															   'class' => 'pull-right',
															   'id' => 'unassign_course_'.$course->id.''));
					$confirmationmsg = get_string('unassign_courses_confirm','local_learningplan', $course);
						
					$PAGE->requires->event_handler("#unassign_course_".$course->id, 'click', 'M.util.moodle_show_course_confirm_dialog',
														array(
														'message' => $confirmationmsg,
														'callbackargs' => array('planid' =>$planid, 'courseid' =>$course->id)
													));
					$actions = $unassign_link;
					}
				}
				
				
				
				
                $table_row = array();
				$course_data = '';
				if($course->sortorder == 0){/*Condtion to set the enable to first sortorder*/
					$disable_class1 = ' '; /*Empty has been sent to class*/
				}
				
				$course_data .= '<div class="course_complete_info row-fluid pull-left '.$disable_class1.'" id="course_info_'.$course->id.'">';
					$course_data .= '<h4>'.$course_view_link.$actions.''.$buttons.'</h4>';
					//$course_data .=	$buttons;
				if($course->sortorder!==''){/*Condition to check the sortorder and disable the course */
					
					/**** Function to get the all the course details like the nextsetoperator,sortorder
					@param planid,sortorder,courseid of the record
					****/
					$disable_class = $learningplan_lib->get_previous_course_status($planid,$course->sortorder,$course->id);
					$find_completion=$learningplan_lib->get_completed_lep_users($course->id,$planid);
					
		           //print_object($find_completion);
						 
								if($disable_class->nextsetoperator!=''){/*condition to check not empty*/
						        
									if($disable_class->nextsetoperator=='and' && $find_completion==''){/*Condition to check the nextsetoperator*/
									
									if($course->sortorder>=$disable_class->sortorder){/*Condition to cehck the sortorder and make all the disable*/
										$disable_class1='course_disabled';
									}
									
									}else{
						
									}
								}
					//}
				}
				/* End of the function and condition By Ravi_369*/
				
					$course_data .= '<div class="course_image_comtainer pull-left span3 desktop-first-column">
										<img class="learningplan_course_image" src="'.$course_summary_image_url.'" title="'.$course->fullname.'"/>
									</div>';
					$course_data .= '<div class="course_data_container pull-left span5 desktop-first-column">';
						$course_data .= '<div class="course_summary">';
							$course_data .= '<div class="clearfix">'.$course_summary.'</div>';
						$course_data .= '</div>';
					$course_data .= '</div>';
					$course_data .= '<div class="course_data_container pull-right span4 desktop-first-column">';
						$course_data .= '<div class="course_activity_details text-right">';
							$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Activities to Complete : </span><span style="font-size:25px;">'.$course_total_activities_link.'</span></div>';
							$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Completed Activities : </span><span style="font-size:25px;">'.$course_completed_activities_link.'</span></div>';
							$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Pending Activities : </span><span style="font-size:25px;">'.$course_pending_activities_link.'</span></div>';
						$course_data .= '</div>';
					
				/********LAUNCH button for every courses to enrol********/
				/*First check the enrolment method*/
				$check_course_enrol=$DB->get_field('enrol','id',array('courseid'=>$course->id,'enrol'=>'learningplan'));
				/***Then check the userid***/
				$find_user=$DB->get_field('user_enrolments','id',array('enrolid'=>$check_course_enrol,'userid'=>$USER->id));
				
				if(!$find_user){/*Condition to check the user enroled or not*/
				$plan_url = new moodle_url('/local/learningplan/index.php', array('courseid' => $course->id,'planid'=>$planid,'userid'=>$USER->id));
				$detail = html_writer::link($plan_url, 'Launch', array('class'=>'launch'));
				}else{/*if already enroled then show enroled */
				if(!empty($completed)){
					$plan_url = "#";
				    $detail = html_writer::link($plan_url, 'Completed', array('class'=>'launch'));
					}else{	
				$plan_url = "#";
				$detail = html_writer::link($plan_url, 'Enrolled', array('class'=>'launch'));
					}
				}
				$course_data .=$cpmpleted_buttons;
				$course_data .= $detail;	
				$course_data .= '</div>';
				$course_data .= '</div>'; 	
				
				
				$table_row[] = $course_data;
				//$return .= $course_data;
				$table_data[] = $table_row;
			}
			//$table_data[] = $table_row;
			$table = new html_table();
			$table->head = array('');
			$table->id = 'learning_plan_courses';
			$table->data = $table_data;
			$return .= html_writer::table($table);
			$return .= html_writer::script('$(document).ready(function(){
												//$("table#learning_plan_courses").dataTable({
													//language: {
													//	"paginate": {
													//		"next": ">",
													//		"previous": "<"
													//	  }
													//}
												//	"iDisplayLength": 3,
												//	"aLengthMenu": [[3, 10, 25, 50, -1], [3, 10, 25, 50, "All"]]
												//});
												//$("table#learning_plan_courses thead").css("display" , "none");
										   });');
		}
		
		return $return;
	}
	public function all_enroled_learningplans(){
        global $DB, $CFG, $OUTPUT, $USER, $PAGE;
        $systemcontext = context_system::instance();
        
	
	$sql="select llp.* from {local_learningplan} llp JOIN {local_learningplan_user} as lla on llp.id=lla.planid where userid=$USER->id and llp.visible=1";
	$learning_plans = $DB->get_records_sql($sql);
        if(empty($learning_plans)){
           return html_writer::tag('div', get_string('nolearningplans', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
        }else{
            $data = array();
            foreach($learning_plans as $learning_plan){
                $row = array();
                $plan_url = new moodle_url('/local/learningplan/plan_view.php', array('id' => $learning_plan->id));
                $plan_edit_url = new moodle_url('/local/learningplan/index.php', array('id' => $learning_plan->id));
				$plan_visible_url = new moodle_url('/local/learningplan/index.php', array('visible' => $learning_plan->id,'show'=>$learning_plan->id));
                if(!empty($learning_plan->startdate)){
                    $plan_startdate = date('d/m/Y', $learning_plan->startdate);
                }else{
                    $plan_startdate = 'N/A';
                }
                if(!empty($learning_plan->enddate)){
                    $plan_enddate = date('d/m/Y', $learning_plan->enddate);
                }else{
                    $plan_enddate = 'N/A';
                }
                if(empty($learning_plan->credits)){
                    $plan_credits = 'N/A';
                }else{
                    $plan_credits = $learning_plan->credits;
                }
                if($learning_plan->learning_type == 1){
                    $plan_type = 'Core Courses';
                }elseif($learning_plan->learning_type == 2){
                    $plan_type = 'Elective Courses';
                }
                if(!empty($learning_plan->location)){
                    $plan_location = $learning_plan->location;
                }else{
                    $plan_location = 'N/A';
                }
                
                $action_icons = '';
                if (is_siteadmin() || has_capability('local/learningplan:visible', $systemcontext)) {
					if($learning_plan->visible == 0){
						$action_icons .= html_writer::link($plan_visible_url,
                                                       html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/show'), 'title'=>'Show', 'class'=>'iconsmall')));
					}elseif($learning_plan->visible == 1){
						$action_icons .= html_writer::link($plan_visible_url,
                                                       html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/hide'), 'title'=>'Hide', 'class'=>'iconsmall')));
					}
                }
				/*********commented by Ravi_369 update and delete*******/
//                if (has_capability('local/learningplan:update', $systemcontext)) {
//                    $action_icons .= html_writer::link($plan_edit_url,
//                                                        html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/edit'), 'title'=>'Edit', 'class'=>'iconsmall')));
//                }
//                if (has_capability('local/learningplan:delete', $systemcontext)) {
//					$url = new moodle_url('/local/learningplan/index.php', array('id' => $learning_plan->id));
//					$deleteicon = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('i/delete'), 'title'=>'Delete', 'class'=>'iconsmall'));
//                    $action_icons .= html_writer::link($url, $deleteicon, array('id' => "lplan_delete_confirm_".$learning_plan->id));
//					$confirmationmsg = get_string('delete_confirm','local_learningplan', $learning_plan);
//					
//					$PAGE->requires->event_handler("#lplan_delete_confirm_".$learning_plan->id, 'click', 'M.util.moodle_show_confirm_dialog',
//														array(
//														'message' => $confirmationmsg,
//														'callbackargs' => array('id' =>$learning_plan->id)
//													));
//                }
                //Learning Plan
                $detail = html_writer::start_tag('div', array('class' => 'learning_plan_view'));
                    $detail .= html_writer::tag('h4', html_writer::link($plan_url, $learning_plan->name), array('class'=>'pull-left'));
                    $detail .= html_writer::start_tag('div', array('class'=>'action_icons pull-right'));
                    $detail .= $action_icons;
					$detail .= html_writer::end_tag('div');
                    $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_info pull-left span12 desktop-first-column'));
                        //Learning Plan Detailed info
                        $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_detail_info'));
                            $detail .= html_writer::start_tag('div');
                                $detail .= html_writer::start_tag('div');
                                    //$detail .= '<div><span>Start date : </span><span>'.$plan_startdate.'</span></div>';
                                    //$detail .= '<div><span>End date : </span><span>'.$plan_enddate.'</span></div>';
                                    $detail .= '<div><span>Type : </span><span>'.$plan_type.'</span></div>';
                                    $detail .= '<div><span>Credits : </span><span>'.$plan_credits.'</span></div>';
                                    $detail .= '<div><span>Location : </span><span>'.$plan_location.'</span></div>';
                                $detail .= html_writer::end_tag('div');
                                
                                $detail .= html_writer::start_tag('div');
                                if(!is_siteadmin()){
                              
								$detail .= html_writer::link($plan_url, 'Launch', array('class'=>'launch'));
								
								}else{
									$detail .= html_writer::link($plan_url, 'Launch', array('class'=>'launch'));
									
								}
                                $detail .= html_writer::end_tag('div');
                            $detail .= html_writer::end_tag('div');
                            $detail .= html_writer::start_tag('div');
                            $detail .= html_writer::end_tag('div');
                        $detail .= html_writer::end_tag('div');
                        //Learning Plan Image
                        $detail .= html_writer::start_tag('div', array('class' => 'learning_plan_image'));
                        $detail .= html_writer::end_tag('div');
                    $detail .= html_writer::end_tag('div');
                $detail .= html_writer::end_tag('div');
                $row[] = $detail;
                $data[] = $row;
            }
            $table = new html_table();
            $table->id = 'all_learning_plans_mylep';
            $table->head = array('');
            $table->data = $data;
            $return = html_writer::table($table);
			$return .= html_writer::script('$(document).ready(function(){
												$("table#all_learning_plans_mylep").dataTable({
													language: {
														"paginate": {
															"next": ">",
															"previous": "<"
														  }
													},
													"iDisplayLength": 3,
													"aLengthMenu": [[3, 10, 25, 50, -1], [3, 10, 25, 50, "All"]]
												});
												$("table#all_learning_plans_mylep thead").css("display" , "none");
												$("#all_learning_plans_length").css("display" , "none");
										   });');
            $return .= '';
        }
        return $return;
    }
public function assigned_learningplans_courses_browse_employee_view($planid, $userid,$condition){
		global $CFG, $DB, $OUTPUT, $PAGE,$USER;
		require_once($CFG->dirroot.'/local/learningplan/lib.php');
		require_once($CFG->dirroot.'/local/includes.php');
		
        $systemcontext = context_system::instance();
		
		$learningplan_lib = new learningplan;
		$includes = new user_course_details;
		
		$courses = $learningplan_lib->get_learningplan_assigned_courses($planid);
		
		$return = '';
		$return .= html_writer::tag('h3', get_string('assigned_courses', 'local_learningplan'), array());
		if(empty($courses)){
			$return .= html_writer::tag('div', get_string('nolearningplancourses', 'local_learningplan'), array('class' => 'alert alert-info text-center pull-left', 'style' => 'width:96%;padding-left:2%;padding-right:1%;'));
		}else{
			$table_data = array();
			/**********To disable the links before enrol to plan**********/
			$check=$DB->get_record('local_learningplan_user',array('userid'=>$USER->id,'planid'=>$planid));
			/*End of query*/
            foreach($courses as $course){
				
				if($check){
					$course_url = new moodle_url('/course/view.php', array('id'=>$course->id));
				}else{
					$course_url="#";
				}
				
				$course_view_link = html_writer::link($course_url, $course->fullname, array());
				$course_summary_image_url = $includes->course_summary_files($course);
				$course_summary = empty($course->objective) ? 'Course Summary not provided' : $course->summary;
				$course_objective = empty($course->objective) ? 'Course Objective not provided' : $course->objective;
				$course_total_activities = $includes->total_course_activities($course->id);
				$course_total_activities_link = html_writer::link($course_url, $course_total_activities, array());
				$course_completed_activities = $includes->user_course_completed_activities($course->id, $userid);
				$course_completed_activities_link = html_writer::link($course_url, $course_completed_activities, array());
				$course_pending_activities = $course_total_activities - $course_completed_activities;
				$course_pending_activities_link = html_writer::link($course_url, $course_pending_activities, array());
				
				$actions = '';
				$buttons = '';
				/*Select box*/
				if($course->next=='or'){ $select='selected';}else{
								$select='';
							}/***condition for the select the dropdown if already selected***/
							
				if($course->next=='or' || $course->next=='and'){			
							
					if($course->next=='and'){
						$buttons .='<h4 class="course_sort_status"><span class="label label-default mandatory-course" >Mandatory</span></h4>';
					}
					elseif($course->next=='or'){
						$buttons .='<h4 class="course_sort_status"><span class="label label-default optional-course" >Optional</span></h4>';
					}		
				}
							/*End of the select box*/
				if (has_capability('local/learningplan:assigncourses', $systemcontext)) {
					if($condition=='view'){
						
					}else{
					
					$unassign_url = new moodle_url('/local/learningplan/assign_courses_users.php', array('planid' => $planid, 'unassigncourse' => $course->id));
					$unassign_link = html_writer::link($unassign_url,
													   html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/delete'), 'class' => 'iconsmall', 'title' => 'Unassign'))
													   , array(
															   'class' => 'pull-right',
															   'id' => 'unassign_course_'.$course->id.''));
					$confirmationmsg = get_string('unassign_courses_confirm','local_learningplan', $course);
						
					$PAGE->requires->event_handler("#unassign_course_".$course->id, 'click', 'M.util.moodle_show_course_confirm_dialog',
														array(
														'message' => $confirmationmsg,
														'callbackargs' => array('planid' =>$planid, 'courseid' =>$course->id)
													));
					$actions = $unassign_link;
					}
				}
				
				
				
				
                $table_row = array();
				$course_data = '';
				if($course->sortorder == 0){/*Condtion to set the enable to first sortorder*/
					$disable_class1 = ' '; /*Empty has been sent to class*/
				}
				
				$course_data .= '<div class="course_complete_info row-fluid pull-left '.$disable_class1.'" id="course_info_'.$course->id.'">';
				$course_data .= '<h4>'.$course_view_link.$actions.''.$buttons.'</h4>';
					
				if($course->sortorder!==''){/*Condition to check the sortorder and disable the course */
					
					/**** Function to get the all the course details like the nextsetoperator,sortorder
					@param planid,sortorder,courseid of the record
					****/
					$disable_class = $learningplan_lib->get_previous_course_status($planid,$course->sortorder,$course->id);
					$find_completion=$learningplan_lib->get_completed_lep_users($course->id,$planid);
					
		           
						 
								if($disable_class->nextsetoperator!=''){/*condition to check not empty*/
						        
									if($disable_class->nextsetoperator=='and' && $find_completion==''){/*Condition to check the nextsetoperator*/
									
									if($course->sortorder>=$disable_class->sortorder){/*Condition to cehck the sortorder and make all the disable*/
										$disable_class1='course_disabled';
									}
									
									}else{
						
									}
								}
					//}
				}
				/* End of the function and condition By Ravi_369*/
					
					$course_data .= '<div class="course_image_comtainer pull-left span3 desktop-first-column">
										<img class="learningplan_course_image" src="'.$course_summary_image_url.'" title="'.$course->fullname.'"/>
									</div>';
					$course_data .= '<div class="course_data_container pull-left span5 desktop-first-column">';
					$course_data .= '<div class="course_summary">';
					$course_data .= '<div class="clearfix">'.$course_summary.'</div>';
					$course_data .= '</div>';
					$course_data .= '</div>';
					$course_data .= '<div class="course_data_container pull-right span4 desktop-first-column">';
					$course_data .= '<div class="course_activity_details text-right">';
					$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Activities to Complete : </span><span style="font-size:25px;">'.$course_total_activities_link.'</span></div>';
					$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Completed Activities : </span><span style="font-size:25px;">'.$course_completed_activities_link.'</span></div>';
					$course_data .= '<div class="row-fluid"><span style="font-size:18px;line-height:30px;">Pending Activities : </span><span style="font-size:25px;">'.$course_pending_activities_link.'</span></div>';
					$course_data .= '</div>';
				
				    $course_data .= $detail;	
				    $course_data .= '</div>';
				    $course_data .= '</div>';
				
				
				$table_row[] = $course_data;
				$table_data[] = $table_row;
			}
			
			$table = new html_table();
			$table->head = array('');
			$table->id = 'learning_plan_courses';
			$table->data = $table_data;
			$return .= html_writer::table($table);
			$return .= html_writer::script('$(document).ready(function(){
												//$("table#learning_plan_courses").dataTable({
													//language: {
													//	"paginate": {
													//		"next": ">",
													//		"previous": "<"
													//	  }
													//}
												//	"iDisplayLength": 3,
												//	"aLengthMenu": [[3, 10, 25, 50, -1], [3, 10, 25, 50, "All"]]
												//});
												//$("table#learning_plan_courses thead").css("display" , "none");
										   });');
		}
		
		return $return;
	}
	
public function learningplaninfo_for_employee($planid){
		global $DB, $PAGE, $USER;
		$lib = new learningplan();
		$includeslib = new user_course_details();
		
		$lplan = $DB->get_record('local_learningplan', array('id'=>$planid));
		
		$lptype = $lplan->approvalreqd == 1 ? 'Core Courses' : 'Elective Courses';
		$lpapproval = $lplan->approvalreqd == 1 ? get_string('yes') : get_string('no');
		
		$lpimgurl = $lib->get_learningplansummaryfile($planid);
		
		$mandatarycourses_count = $lib->learningplancourses_count($planid, 'and');
		$optionalcourses_count = $lib->learningplancourses_count($planid, 'or');
		
		$lplanassignedcourses = $lib->get_learningplan_assigned_courses($planid);
		
		$managerenderer = $PAGE->get_renderer('block_manage');
		
		$lpinfo = '';
		$lpinfo .= "<div class='row' >";
		
			$lpinfo .= "<div class='col-md-1'>";
			$lpinfo .= "</div>";
			
				$lpinfo .= "<div class='col-md-10 main_content learningplan_view'>";
					$lpinfo .= "<div class='row no-margin no-padding' align='center'>";
						$topimgurl = new moodle_url('/local/learningplan/images/roadways.png');
						$lpinfo .= html_writer::img($topimgurl, 'Learningplan',array('id'=>'image', 'style'=>'width: 100%;'));
					$lpinfo .= "</div>";
					
					$lpinfo .= "<div class='plan-content' style='margin-top: 15px;'>";
						$lpinfo .= "<div class='plan-header'>";
							$lpinfo .= "<div class='row'>";
								$lpinfo .= "<div class='col-md-9' align='left'>";
									$lpinfo .="<label class='label-header'>".$lplan->name."</label>";
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-3' align='right'>";
								
						//print_object($planid);						
						$condition="view";
						
						/***********The query Check Whether user enrolled to LEP or NOT**********/
						$plan_record = $DB->get_record('local_learningplan', array('id' => $planid));
						$sql="select id from {local_learningplan_user} where planid=$planid and userid=".$USER->id."";
						$check=$DB->get_record_sql($sql);
						/*End of Query*/
						
						/*******The Below query is check the approval status for the LOGIN USERS on the his LEP************/
						$check_approvalstatus=$DB->get_record('local_learningplan_approval',array('planid'=>$plan_record->id,'userid'=>$USER->id));
						
						/*End of Query*/
						//print_object($check);
						if($check){ /****condition to check user already enrolled to the LEP If Enroled he get option enrolled ********/
						
						if($check_approvalstatus->approvestatus==1){
						$lpinfo .= "<a href=$back_url> <button class='btn enroll'>ENROLLED TO PLAN</button></a>";
						$back_url = "#";
						//echo html_writer::link($back_url, 'Enrolled to Plan', array('class' => 'btn enroll'));
						}else{
						$back_url ="#";
						//echo html_writer::link($back_url, 'Already Enrolled', array('class' => 'pull-right already_enrolled_plan nourl'));
						$lpinfo .= "<a href='$back_url' id='enrolle'> <button class='btn enroll'>Already Enrolled</button></a>";
						}
						}else{/****Else he has 4 option like the Send Request or Waiting or Rejected or Enroled****/
						
						if(!is_siteadmin()){
						
						if($condition!='manage'){ /*******condition to check the manage page or browse page******/
						
						//print_object($plan_record);
						//print_object($check_approvalstatus);
						
						if($plan_record->approvalreqd==1  && (!empty($check_approvalstatus))) /***** If user has LEP with approve with 1 means request yes and
															empty not check approval status means he has sent request******/
						{
						
						$learningplan_lib = new learningplan;
						$check_users= $learningplan_lib->check_courses_assigned_target_audience($USER->id,$plan_record->id);
						/****The above Function is to check the user is present in the target audience or not***/
						
						if($check_users==1){/*if there then he will be shown the options*/
						
						$check_approvalstatus=$DB->get_record('local_learningplan_approval',array('planid'=>$plan_record->id,'userid'=>$USER->id));
						
						if($check_approvalstatus->approvestatus==0 && !empty($check_approvalstatus)){
						$back_url = "#";
						$lpinfo .= "<a href='$back_url' id='request'> <button class='btn enroll'>Waiting</button></a>";
						//echo html_writer::link($back_url, 'Waiting', array('class' => 'pull-right actions nourl'));  
						}elseif($check_approvalstatus->approvestatus==2 && !empty($check_approvalstatus)){
						$back_url = "#";
						//echo html_writer::link($back_url, 'Rejected',array('class' => 'pull-right actions','title'=>'Your Request has been Rejected contact supervisor'));
						$lpinfo .= "<a href='$back_url' id='request'> <button class='btn enroll'>Rejected</button></a>";
						}    
						if(empty($check_approvalstatus)){
						
						$back_url = new moodle_url('/local/learningplan/plan_view.php',array('id'=>$plan_record->id,'enrolid'=>$plan_record->id));
						$lpinfo .= "<a href='$back_url' id='enroll'> <button class='btn enroll'>Enroll to Plan</button></a>";
						echo html_writer::link($back_url, 'Enroll to Plan', array('class' => 'btn enroll','id'=>'enroll1'));
						$notify = new stdClass();
						$notify->name = $plan_record->name;
						$PAGE->requires->event_handler("#enroll1",
						'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('enroll_notify','local_learningplan',$notify),
								 'callbackargs' => array('confirmdelete' =>$plan_record->id)));
						//$lpinfo .= "<a href=$back_url> <button class='btn enroll'>ENROLL</button></a>";
						}
						}
						}else if(($plan_record->approvalreqd==1) && (empty($check_approvalstatus))){
						$learningplan_lib = new learningplan; 
						$check_users= $learningplan_lib->check_courses_assigned_target_audience($USER->id,$plan_record->id);
						
						if($check_users==1){
						$back_url = new moodle_url('/local/learningplan/index.php', array('approval' => $plan_record->id));	
						$lpinfo .= "<a href='$back_url' id='request'> <button class='btn enroll'>SEND REQUEST</button></a>";
						$approve=  html_writer::link('Send Request', array('class' => 'pull-right enrol_to_plan nourl','id'=>'request'));
						$notify_info = new stdClass();
						$notify_info->name = $plan_record->name;
						$PAGE->requires->event_handler("#request",
						'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('delete_notify','local_learningplan',$notify_info),
								 'callbackargs' => array('confirmdelete' =>$plan_record->id)));
						
						}
						}else if($plan_record->approvalreqd==0  && (empty($check_approvalstatus))){
						
						$back_url = new moodle_url('/local/learningplan/plan_view.php',array('id'=>$plan_record->id,'enrolid'=>$plan_record->id));
						$lpinfo .= "<a href='$back_url' id='enroll'> <button class='btn enroll'>Enroll to Plan</button></a>";
						//echo html_writer::link($back_url, 'Enroll to Plan', array('class' => 'pull-right enrol_to_plan ','id'=>'enroll'));
						$notify = new stdClass();
						$notify->name = $plan_record->name;
						$PAGE->requires->event_handler("#enroll",
						'click', 'M.util.bajaj_show_confirm_dialog', array('message' => get_string('enroll_notify','local_learningplan',$notify),
								 'callbackargs' => array('confirmdelete' =>$plan_record->id)));
						}
						}
						}
						}/** End of condtion **/
						if($lplan->learning_type == 1){
								$plan_type = 'Core Courses';
								}elseif($lplan->learning_type == 2){
								$plan_type = 'Elective Courses';
								}
						if(!empty($lplan->startdate)){
						$plan_startdate = date('d/m/Y', $lplan->startdate);
						}else{
						$plan_startdate = 'N/A';
						}
						if(!empty($lplan->enddate)){
						$plan_enddate = date('d/m/Y', $lplan->enddate);
						}else{
						$plan_enddate = 'N/A';
						}
							
								//	$lpinfo .= "<button class='btn enroll'>ENROLL</button>";
								$lpinfo .="</div>";
							$lpinfo .= "</div>";
						$lpinfo .= "</div>";
						
						$lpinfo .= "<div class='plan-body'>";
						
							$lpinfo .= "<div class='row'>";
								$lpinfo .= "<div class='col-md-4' align='center' style='margin-bottom: 10px'>";
									$lpinfo .= html_writer::img($lpimgurl, 'Learningplan',array('id'=>'image', 'class'=>'content-img'));
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-8'>";
									$lpinfo .= "<div class='plan-body-content' style='text-align:justify'>";
										$lpinfo .= "<b>DESCRIPTION: </b>".$lplan->description; 
									$lpinfo .= "</div>";
									$lpinfo .= "<div class='plan-body-content' style='text-align:justify'>";
										$lpinfo .= "<b>OBJECTIVE: </b>".$lplan->objective;
									$lpinfo .= "</div>";
								$lpinfo .= "</div>";
							$lpinfo .= "</div>";
							
							$lpinfo .= "<div class='plan-body-content mt-10'>";
							$lpinfo .= "<div class='row'>";
								$lpinfo .="<div class='col-md-12'>";
									$lpinfo .= "<b style='margin-left: 0px;'>DETAILS:</b>";
								$lpinfo .="</div>";
							$lpinfo .= "</div>";
							
							$lpinfo .= "<div class='row'>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Plan Type:</label> <label>".$lptype."</label>";
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Required Approval:</label> <label>".$lpapproval."</label>";
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Credits:</label> <label>".$lplan->credits."</label>";
								$lpinfo .= "</div>";
							$lpinfo .= "</div>";
						
							$lpinfo .= "<div class='row'>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Start Date:</label> <label>N/A</label>";
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>End Date:</label> <label>N/A</label>";
								$lpinfo .= "</div>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Mandatory Courses:</label> <label>".$mandatarycourses_count."</label>";
								$lpinfo .= "</div>";
							$lpinfo .= "</div>";
							
							$lpinfo .= "<div class='row'>";
								$lpinfo .= "<div class='col-md-4'>";
									$lpinfo .= "<label class='col-name'>Optional Courses:</label> <label>".$optionalcourses_count."</label>";
								$lpinfo .= "</div>";
							$lpinfo .= "</div>";
							
						$lpinfo .= "<div>";
					$lpinfo .= "</div>";
					
					$lpinfo .= "<hr>";
				
					$lpinfo .= "<div class='row' align='center'>";
					$lpinfo .= "<h3 class='lp-head'>Your Learning Path</h3>";
					$lpinfo .= "<div style='padding: 0px 15px;'>";
					if($course_det->identifiedas == 1){
										$identifiedfilter = get_string('mooc');
									}elseif($course_det->identifiedas == 2){
										$identifiedfilter = get_string('ilt');
									}else if($course_det->identifiedas == 3){
										$identifiedfilter = get_string('elearning');
									}
						if($lplanassignedcourses){
							foreach($lplanassignedcourses as $assignedcourse){
								$courseimgurl = $managerenderer->get_course_summary_file($assignedcourse);
								$courseimg = html_writer::img($courseimgurl, 'Course image', array());
								
								$c_category = $DB->get_field('course_categories', 'name', array('id'=>$assignedcourse->category));
								
								$coursetypes = $DB->get_field('local_coursedetails', 'identifiedas', array('courseid'=>$assignedcourse->id));
								if($coursetypes){
									$types = array();
									$ctypes = explode(',', $coursetypes);
									$identify = array();
									$identify['1'] = get_string('mooc');
									$identify['2'] = get_string('ilt');
									$identify['3'] = get_string('elearning');
									$identify['4'] = get_string('learningplan');
									foreach($ctypes as $ctype){
										$types[] = $identify[$ctype];
									}
								}
								
								
								$coursepageurl = new moodle_url('/course/view.php', array('id'=>$assignedcourse->id));
								if($assignedcourse->next == 'and'){
									$optional_or_mandtry = "<span class='mandatory'>Mandatory</span>";
								}else{
									$optional_or_mandtry = "<span class='optional'>Optional</span>";
								}
								/******To make course link enable after the enrolled to lep******/
								$check=$DB->get_field('local_learningplan_user','id',array('userid'=>$USER->id,'planid'=>$planid));
								if($check){
									$enrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'learningplan'));
									/*******The three enrolment added bcos we need to get link in any of enrolment so.There was issues in production***/
									$selfenrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'self'));
									$autoenrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'auto'));
									$manualenrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'manual'));
									$classroomenrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'classroom'));
									$sql="select id from {user_enrolments} where userid=$USER->id and enrolid in('$enrol','$selfenrol','$autoenrol','$manualenrol','$classroomenrol')"; 		
									$enrolledcourse=$DB->get_field_sql($sql);
									
								$rname = format_string($assignedcourse->fullname);
								if($rname > substr(($rname),0,20)){
								$fullname = substr(($rname),0,20).'...';
								}else{
								$fullname =$rname; 
								}
								if($enrolledcourse){
									
								$courselink = html_writer::link($coursepageurl, $fullname, array('class'=>'coursesubtitle','title'=>$assignedcourse->fullname));
								}else{
								$coursepageurl="#";
								$courselink = html_writer::link($coursepageurl, $fullname, array('class'=>'coursesubtitle','title'=>$assignedcourse->fullname));
								}
								}else{
                                      /***The course name added**/
									$rname = format_string($assignedcourse->fullname);
									if($rname > substr(($rname),0,20)){
									$fullname = substr(($rname),0,20).'...';
									}else{
									$fullname =$rname; 
									}	
								$coursepageurl="#";
								$courselink = html_writer::link($coursepageurl, $fullname, array('class'=>'coursesubtitle','title'=>$assignedcourse->fullname));
								}
								
								$progressbar = $includeslib->user_course_completion_progress($assignedcourse->id,$USER->id);
								if(!$progressbar){
									$progressbarval = 0;
									$progress_bar_width = "min-width: 0px;";
								}else{
									$progressbarval = round($progressbar);
									$progress_bar_width = "min-width: 20px;";
								}
									/**************To show course completed or not********/
				$sql="select id from {course_completions} as cc where userid=".$USER->id." and course=".$assignedcourse->id." and timecompleted!=''";
			   
				$completed=$DB->get_record_sql($sql);
								/********LAUNCH button for every courses to enrol********/
				/*First check the enrolment method*/
				//$check_course_enrol=$DB->get_field('enrol','id',array('courseid'=>$assignedcourse->id,'enrol'=>'learningplan'));
								$sql="select enrol,id from {enrol} where courseid=$assignedcourse->id";
								$get_data=$DB->get_records_sql_menu($sql);
								$data=implode(',',$get_data);
								
								/********This below query is used to check the user already enroled to course with other enrolments methods******/
								$sql="select id from {user_enrolments} where enrolid IN($data) and userid=$USER->id";
								$find_user=$DB->record_exists_sql($sql) ;
				
				//print_object($find_user);
				/***Then check the userid***/
								//$find_user=$DB->get_field('user_enrolments','id',array('enrolid'=>$check_course_enrol,'userid'=>$USER->id));
				
								if(!$find_user){/*Condition to check the user enroled or not*/
									$plan_url = new moodle_url('/local/learningplan/index.php', array('courseid' => $assignedcourse->id,'planid'=>$lplan->id,'userid'=>$USER->id));
									$launch = html_writer::link($plan_url, 'LAUNCH', array('class'=>'btn btn-sm btn-info pull-right btn-enrol btm-btn '));
								}else{/*if already enroled then show enroled */
									if(!empty($completed)){
										$plan_url = "#";
										$launch = html_writer::link($plan_url, 'Completed', array('class'=>'btn btn-sm btn-info pull-right btn-enrol btm-btn'));
									}else{
										$plan_url = "#";
										$launch = html_writer::link($plan_url, 'Enrolled', array('class'=>'btn btn-sm btn-info pull-right btn-enrol btm-btn'));
									}
								}
								$course_data = '';
								//print_object($assignedcourse->sortorder);exit;	
								if($assignedcourse->sortorder == 0){/*Condtion to set the enable to first sortorder*/
								$disable_class1 = ' '; /*Empty has been sent to class*/
								}
				
				/* End of the function and condition By Ravi_369*/				
								
								
								//$launch = html_writer::link($coursepageurl, 'LAUNCH', array('class'=>'btn btn-sm btn-info pull-right btn-enrol'));
								//print_object($disable_class1);
								$lpinfo .= "<div class='col-xs-12 col-sm-6 col-md-4 divslide'>";
								$lpinfo .= "<div class='course-body {$disable_class1}'>";
								if($assignedcourse->sortorder!==''){/*Condition to check the sortorder and disable the course */
					
					/**** Function to get the all the course details like the nextsetoperator,sortorder
					@param planid,sortorder,courseid of the record
					****/
					$disable_class = $lib->get_previous_course_status($planid,$assignedcourse->sortorder,$assignedcourse->id);
					$find_completion=$lib->get_completed_lep_users($assignedcourse->id,$planid);
					
		           //print_object($find_completion);
						 
								if($disable_class->nextsetoperator!=''){/*condition to check not empty*/
						        
									if($disable_class->nextsetoperator=='and' && $find_completion==''){/*Condition to check the nextsetoperator*/
									
									if($assignedcourse->sortorder>=$disable_class->sortorder){/*Condition to cehck the sortorder and make all the disable*/
										$disable_class1='course_disabled';
									}
									
									}else{
						
									}
								}
					//}
				}
									
										$lpinfo .= "<div class='course-img'>";
											$lpinfo .= $courseimg;
											$lpinfo .= "<div class='course-toast'>".$c_category."</div>";
										$lpinfo .= "</div>";
		
										$lpinfo .= "<div class='progress progress-striped'>";
											//$lpinfo .= "<div class='progress-bar progress-bar-success' role='progressbar' aria-valuenow='20' aria-valuemin='0' aria-valuemax='100' style='width:".$progressbarval."%'>".$progressbarval."%";
											$lpinfo .= '<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="20" aria-valuemin="0" aria-valuemax="100" style="width: '.$progressbarval.'%;'.$progress_bar_width.'">
                                                            '.$progressbarval.'%';
											$lpinfo .= "</div>";
										$lpinfo .= "</div>";
		
										$lpinfo .= "<div class='coursepage-name'>";
											$lpinfo .= $courselink.$optional_or_mandtry."<br>";
											$lpinfo .= "<div class='course-author'>";
												$lpinfo .= "<div class='row' align='left'>";
													$lpinfo .= "<div class='col-md-12 author-name pull-left'>
																	<span>Type: ".implode(', ', $types)."</span>";
														$lpinfo .= "</div>";
														
														/**********To disable the The status like Launch || Enrolled || Completed || before enrol to plan**********/
															$check=$DB->get_field('local_learningplan_user','id',array('userid'=>$USER->id,'planid'=>$planid));
															/*End of query*/
															if($check){
																$lpinfo .= "<div class='pull-right row-fluid'>".$launch."</div>";
															}else{
																$plan_url="#";
																$launch = html_writer::link($plan_url, 'Enrol To Plan', array('class'=>'btn btn-sm btn-info pull-right btm-btn btn-enrol'));
																$lpinfo .= "<div class='pull-right row-fluid'>$launch</div>";
															}
												$lpinfo .= "</div>";
											$lpinfo .= "</div>";
										$lpinfo .= "</div>";
									$lpinfo .= "</div>";
								$lpinfo .= "</div>";
							}
						}

					$lpinfo .= "</div>";

				$lpinfo .= "</div>";
					
				$lpinfo .= "<div class='clearfix'></div>";
					
				$lpinfo .= "</div>";
				$lpinfo .= "</div>";
			
			$lpinfo .= "</div>";
		$lpinfo .= "</div>";
		
		return $lpinfo;
	}
	
} // end of class  