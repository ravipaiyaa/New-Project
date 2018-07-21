<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $USER, $CFG,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/local/learningplan/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
$planid = optional_param('id', 0, PARAM_INT);
$users = optional_param('user', 'courses', PARAM_TEXT);


global $DB, $USER, $CFG,$PAGE,$OUTPUT;


//$PAGE->set_url('/local/learningplan/lep_course_enrol.php', array('id' => $id));
//$PAGE->set_title(get_string('pluginname', 'local_learningplan'));
//$PAGE->set_pagelayout('admin');
////Header and the navigation bar
//$PAGE->set_heading(get_string('pluginname', 'local_learningplan'));
//$PAGE->navbar->ignore_active();
//$PAGE->navbar->add( get_string('pluginname', 'local_learningplan'), new moodle_url('/local/learningplan/index.php'));

$learningplan=new  learningplan();
$sql="select llu.id,llu.userid,llu.planid from {local_learningplan_user} as llu";
echo $sql;
$allusers=$DB->get_records_sql($sql);
print_object(count($allusers) );
//print_object($allusers);
foreach($allusers as $all){
  
$enrol=$learningplan->to_enrol_users_check_completion($all->planid,$all->userid);
//$completed=$learningplan->complete_the_lep($all->planid,$all->userid);
//print_object($completed);
}
//if($enrol){
//    echo "cron run sucess";
//}

