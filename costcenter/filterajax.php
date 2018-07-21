<?php
require_once('../../config.php');
require_once($CFG->dirroot.'/local/costcenter/lib.php');
require_once($CFG->dirroot.'/local/filterclass.php');
global $CFG, $DB, $USER;

$action = optional_param('action','', PARAM_RAW);
$type = optional_param('type','', PARAM_RAW);
$like = optional_param('q','', PARAM_RAW);
$course_id = optional_param('courseid', 0,PARAM_INT);
$filterpage = optional_param('filterpage', '', PARAM_RAW);
$page = optional_param('page', 0,PARAM_INT);


$PAGE->set_context(context_system::instance());
$costcenterlib = new costcenter();
$filter_class = new custom_filter; 

if(($action == 'courseenroll')){
    if(is_siteadmin()){
        $costcenter="";
    }else{
        if($filterpage == 'course'){
            $costcenter=$DB->get_field('local_coursedetails','costcenterid',array('courseid'=>$course_id));
            if($costcenter==1){
                $costcenter=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id)) ;
            }
        }else{
            $costcenter=$DB->get_field('local_userdata','costcenterid',array('userid'=>$USER->id)) ;
        }
    }
    
    switch($type){
        case 'idnumber':
            $idnumbers = $costcenterlib->get_enrolledcoursefilter_users_employeeids($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($idnumbers);
        break;
        case 'email':
            $emails = $costcenterlib->get_enrolledcoursefilter_users_emails($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($emails);
        break;
        case 'band':
            $bands = $costcenterlib->get_enrolledcoursefilter_users_bands($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($bands);
        break;
        case 'department':
            $departments = $costcenterlib->get_enrolledcoursefilter_users_departments($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($departments);
        break;
        case 'subdepartment':
            $subdepartments = $costcenterlib->get_enrolledcoursefilter_users_subdepartments($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($subdepartments);
        break;
        case 'sub_sub_department':
            $sub_sub_departments = $costcenterlib->get_enrolledcoursefilter_users_subsub_departments($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($sub_sub_departments);
        break;
        case 'designation':
            $designations = $costcenterlib->get_enrolledcoursefilter_users_designation($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($designations);
        break;
        case 'costcenter':
            $costcenters = $costcenterlib->get_enrolledcoursefilter_users_costcenters($like,$page,$course_id, $filterpage);
            echo json_encode($costcenters);
        break;
        case 'empname':
            $fullname = $filter_class->get_all_users_id_fullname($costcenter,$like,$page,$course_id, $filterpage);
            echo json_encode($fullname);
        break;
    }
}