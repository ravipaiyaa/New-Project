<?php
//define('AJAX_SCRIPT', true);

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/learningplan/lib.php');
//require_once ($CFG->dirroot. '/mod/facetoface/lib.php');
//require_once($CFG->dirroot . '/local/users/lib.php');

global $DB, $PAGE,$CFG;
require_login();
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$course = optional_param('course', 0, PARAM_INT);
$plan = optional_param('planid', 0, PARAM_INT);
$value = optional_param('value', '', PARAM_TEXT);
 
if($value=="and"){
    
    $id=$DB->get_record('local_learningplan_courses',array('planid'=>$plan,'courseid'=>$course));
    $sql="update {local_learningplan_courses} SET nextsetoperator='and' where id=$id->id";
    $DB->execute($sql);
}elseif($value=="or"){
     $id=$DB->get_record('local_learningplan_courses',array('planid'=>$plan,'courseid'=>$course));
     $sql="update {local_learningplan_courses} SET nextsetoperator='or' where id=$id->id";
    $DB->execute($sql); 
}
