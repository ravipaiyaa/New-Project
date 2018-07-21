<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $USER, $CFG,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/local/learningplan/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
$planid = optional_param('id', 0, PARAM_INT);
$users = optional_param('user', 'courses', PARAM_TEXT);


global $DB, $USER, $CFG,$PAGE,$OUTPUT;




$learningplan=new  learningplan();
$sql="select llu.id,llu.userid,llu.planid from {local_learningplan_user} as llu";

$allusers=$DB->get_records_sql($sql);
print_object($allusers);
foreach($allusers as $all){
   
//$completed=$learningplan->complete_the_lep(170,139);
$completed=$learningplan->complete_the_lep($all->planid,$all->userid);

}


