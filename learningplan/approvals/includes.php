<?php
/**
 * *************************************************************************
 * *                  Mod Facetoface   				                      **
 * *************************************************************************
 * @copyright   emeneo.com                                                **
 * @link        emeneo.com                                                **
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later  **
 * *************************************************************************
 * ************************************************************************
*/
require_once(dirname(__FILE__) . '/../../../config.php');
require_once($CFG->dirroot . '/mod/facetoface/lib.php');
//require_once($CFG->dirroot . '/mod/facetoface/batchinfo.php');
class facetoface_enrolment{
    function f2fEnrolment($f2fid=0,$cat){
        global $DB,$USER,$CFG;
        $sql="SELECT  fa.id as fapprovalid, fa.f2fid, fa.userid as f2fuserid,fa.timecreated as f2ftimecreated,fa.approvestatus as status,u.*,ud.*
                    FROM {local_facetoface_approval} fa JOIN
                    {local_userdata} ud ON fa.userid=ud.userid JOIN
                    {user} u ON ud.userid=u.id ";
        if($cat == 'approve'){
            $sql .="WHERE fa.approvestatus IN (1) AND ";
        }else if($cat == 'new'){
            $sql .="WHERE fa.approvestatus = 0 AND ";
        }
        else if($cat == 'rejected'){
            $sql .="WHERE fa.approvestatus = 2 AND ";
        }
        
         $sql .=" fa.f2fid=$f2fid
              AND u.deleted=0 AND u.suspended=0";
     
        $userenrolments = $DB->get_records_sql($sql);
                    
             
        return $userenrolments;
    }
   
    function confirmEnrolment($tableid,$f2fid){
     global $DB,$CFG,$USER;
      require_once($CFG->dirroot.'/lib/moodlelib.php');
     require_once $CFG->dirroot.'/mod/facetoface/lib.php';
         foreach ($tableid as $id){
            $enroluser = new stdClass();
             $mainid=$DB->get_field('local_facetoface_approval','userid',array('id'=>$id));
             $enroluser->id = $id;
             $enroluser->approvestatus = 1;
             $enroluser->approvedby = $USER->id;
            $enroluser->timemodified = time();
            $enroluser->usermodified = $USER->id;
         $confirm = $DB->update_record('local_facetoface_approval',$enroluser);
         assign_batch($mainid,$f2fid);
        
         
         }
     
   }
   
   function cancelEnrolment($tableid,$f2fid){
     global $DB,$CFG,$USER;
      require_once($CFG->dirroot.'/lib/moodlelib.php');
     require_once $CFG->dirroot.'/mod/facetoface/lib.php';
         foreach ($tableid as $id){
             $enroluser = new stdClass();
             //$mainid=$DB->get_field('local_facetoface_approval','id',array('userid'=>$id));
             $enroluser->id = $id;
             $enroluser->approvestatus = 2;
             $enroluser->approvedby = $USER->id;
            $enroluser->timemodified = time();
            $enroluser->usermodified = $USER->id;
        
         $confirm = $DB->update_record('local_facetoface_approval',$enroluser);
         
         }   
   }
   function planEnrolment($f2fid=0,$cat){
        global $DB,$USER,$CFG;
        $sql="SELECT  fa.id as fapprovalid, fa.planid, fa.userid as f2fuserid,fa.timecreated as f2ftimecreated,fa.approvestatus as status,u.*,ud.*
                    FROM {local_learningplan_approval} fa JOIN
                    {local_userdata} ud ON fa.userid=ud.userid JOIN
                    {user} u ON ud.userid=u.id ";
        if($cat == 'approve'){
            $sql .="WHERE fa.approvestatus IN (1) AND ";
        }else if($cat == 'new'){
            $sql .="WHERE fa.approvestatus = 0 AND ";
        }
        else if($cat == 'rejected'){
            $sql .="WHERE fa.approvestatus = 2 AND ";
        }
        
         $sql .=" fa.planid=$f2fid
              AND u.deleted=0 AND u.suspended=0";
     
        $userenrolments = $DB->get_records_sql($sql);
                    
             
        return $userenrolments;
    }
}