<?php
define("AJAX_SCRIPT", true);
require_once('../../../config.php');
global $CFG,$DB,$USER,$PAGE;
$systemcontext = context_system::instance();
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
require_once($CFG->dirroot . '/mod/facetoface/approvals/includes.php');
$PAGE->requires->js('/mod/facetoface/js/jquery.dataTables.min.js',true);//*This js and css files for data grid of batches*//
$PAGE->requires->css('/mod/facetoface/css/jquery.dataTables.css',true);
$action = optional_param('action','', PARAM_TEXT);
$req = optional_param('req','', PARAM_TEXT);
$cat = optional_param('cat','', PARAM_TEXT);
$facetofaceid = optional_param('fid','', PARAM_INT);
$PAGE->set_context($systemcontext);

switch ($action) {
// coe published the exam
  case 'request_user':
$is_teammanager=$DB->record_exists('local_userdata',array('supervisorid'=>$USER->id));

     $sql = "SELECT f.* ,fa.id as fapprovalid FROM {facetoface} f JOIN
            {local_facetoface_approval} fa ON f.id = fa.f2fid WHERE f.active = 1 ";
             //echo $sql;
if(has_capability('mod/facetoface/ilt_approval:manage', $systemcontext, $USER->id) && ($is_teammanager) && (!is_siteadmin($USER->id))){

    $sql .= " AND f.costcenter
                IN (
                SELECT c.id
                FROM mdl_local_userdata ud
                JOIN mdl_local_costcenter c ON ud.costcenterid = c.id
                AND ud.userid =$USER->id
                )";
        
}   //echo $sql;
    $drcourses = $DB->get_records_sql($sql);

//echo "<h2 class='tmhead2'>".get_string('requested_courses', 'facetoface')."</h2>";

    $data = array();                    
foreach($drcourses as $drcourse){
   // print_object($drcourse);
   $enrol_confirm = new facetoface_enrolment();
    $cat="approve";
    $enrol_users = $enrol_confirm->f2fEnrolment($drcourse->id,$cat);
    //print_object($enrol_users);
    foreach($enrol_users as $enroldata){
        $name=$enroldata->firstname;
    }
    $row = array();
    $draftcourseheadig = '<div class="pl_newtoggle">'.html_writer:: tag('h5',$drcourse->name,array()).'</div>';
        $draftheadig = html_writer:: tag('a',$draftcourseheadig,array('href'=>$CFG->wwwroot.'/mod/facetoface/ilt_aprove.php?f2fid='.$drcourse->id.'&cat=approve'));
        //SELECT *
        //                               FROM {local_facetoface_approval}
        //                               where f2fid=$drcourse->id and approvestatus IN (0,2) 
        $user_Requested=$DB->get_records_sql("SELECT fa.id as f2fuserid
                                FROM {local_facetoface_approval} fa JOIN {local_userdata} ud ON fa.userid=ud.userid
                                JOIN {user} u ON ud.userid=u.id
                                WHERE fa.approvestatus = 0 AND fa.f2fid=$drcourse->id AND u.deleted=0 AND u.suspended=0");
        

        $user_Approved=$DB->get_records_sql("SELECT fa.id as f2fuserid
                                FROM {local_facetoface_approval} fa JOIN {local_userdata} ud ON fa.userid=ud.userid
                                JOIN {user} u ON ud.userid=u.id
                                WHERE fa.approvestatus = 1 AND fa.f2fid=$drcourse->id AND u.deleted=0 AND u.suspended=0");
				        
				$user_rejected=$DB->get_records_sql("SELECT fa.id as f2fuserid
                                FROM {local_facetoface_approval} fa JOIN {local_userdata} ud ON fa.userid=ud.userid
                                JOIN {user} u ON ud.userid=u.id
                                WHERE fa.approvestatus = 2 AND fa.f2fid=$drcourse->id AND u.deleted=0 AND u.suspended=0");
        
        //$count_requested = html_writer:: tag('a',count($user_Requested),array('href'=>$CFG->wwwroot.'/mod/facetoface/ilt_aprove.php?f2fid='.$drcourse->id.'&cat=approve'));
        $count_requested=count($user_Requested);
        //$count_Approved = html_writer:: tag('a',count($user_Approved),array('href'=>$CFG->wwwroot.'/mod/facetoface/ilt_aprove.php?f2fid='.$drcourse->id.'&cat=reject'));
        $count_Approved=count($user_Approved);
				
				$count_Rejected=count($user_rejected);
         
         $row['data1']= $drcourse->name;
         $row['data2']=$count_requested;              
         $row['data3']= $drcourse->id;
         $row['data4']= $count_Approved;
         $row['data5']=$count_Rejected;              
         $row['data6']=$drcourse->name;
         $row['data7']=$drcourse->name;
		
		 
		 $row["DT_RowId"]= "row_".$drcourse->id;
         $data[]=$row;
 }
 				  $json_data = array(
				  "data"  => $data
            );            
            echo json_encode($json_data);
        exit;
        break;
}


switch ($req) {
// coe published the exam
//
  case 'req_data':
		$facetofaceid;
		//$cat='new';
		$enrol_confirm = new facetoface_enrolment();
		$enrol_users = $enrol_confirm->f2fEnrolment($facetofaceid,$cat);
        
		if(!empty($enrol_users)){
		foreach ( $enrol_users as $enrol ) {
			//print_object($enrol);exit;
         $row['data1']= $enrol->firstname . ' ' . $enrol->lastname;
         $row['data2']=$enrol->email;              
         $row['data3']= $enrol->department;
         $row['data4']= $enrol->designation;
         $row['data5']=$enrol->userid;
		 $row['fapprovalid']=$enrol->fapprovalid;	
		$row['data6']='<input type="button" value="' . get_string ( 'btncancel', 'facetoface' ) . '"onclick="doSubmit(\'reject\','.$enrol->fapprovalid.');"></td></tr>'; 
					//$row['data7']='<div class="form_popup" id="action_plan_category_form">
					//
					//				<label for="new_category" >Give reason to reject</label>
					//				<input type="text" id="action_plan_category" name="new_category"></input>
					//				<input type="hidden" id="hidden-input" name="hiddenid"/>
					//				<input type="button" id="submit_action_plan_category" onclick="reject_confirmation()" class="pull-right" style="margin-right:20px;" tabindex="-1" value="submit" >
					//			
					//		</div>';
         $data[]=$row;
 }
 				 
				
                    
            echo json_encode($data);
        exit;
        break;
		
		}
		else
		{
			echo "0";
		}
		

}