<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $USER, $CFG,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/local/learningplan/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
$planid = optional_param('planid', 0, PARAM_INT);
$users = optional_param('id', 0, PARAM_INT);
$PAGE->set_context(context_system::instance());
$PAGE->set_url('/local/learningplan/local_learningplan_courses.php', array('id'=>$users->id));
$PAGE->set_title(get_string('pluginname', 'local_learningplan'));
$PAGE->set_pagelayout('admin');
$PAGE->requires->jquery();
$PAGE->requires->js('/local/learningplan/js/jquery.dataTables.min.js',true);//*This js and css files for data grid of batches*//
$PAGE->requires->css('/local/learningplan/css/jquery.dataTables.css');

echo $OUTPUT->header();
echo "<h2 class='tmhead2'>".get_string('lep_header', 'local_learningplan').'</h2>';
$learningplan=new  learningplan();
$completed=$learningplan->lep_complete_courses($planid,$users);
 
$data = array();
foreach($completed as $complete){
    $row = array();
    $row[] = $DB->get_field('course','fullname',array('id'=>$complete->courseid));
    $sql = "SELECT id,timecompleted from  {course_completions}  WHERE userid = $users AND course = ".$complete->courseid."";
 $check=$DB->get_record_sql($sql);
 //print_object($check);
  if($check->timecompleted != NULL){
                  $status = html_writer::start_tag("span",array("class"=>"label label-info","style"=>"background-color:grey;padding: 5px;"));
                 $status .= 'Completed';
                  $status .= html_writer::end_tag("span");
             }
             else{
                $status = html_writer::start_tag("span",array("class"=>"label label-info","style"=>"background-color:grey;padding: 5px;"));
                $status .= 'Not Completed';
                $status .= html_writer::end_tag("span");
             }
             $row[] = $status;
   $data[]=$row; 
}

$table = new html_table();
$table->id = 'table_lep_completions';
$table->head = array('Courses', 'Status');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
$return = '<input type="submit" id="submit_learningplan_courses' . $planid . '" class="form-submit" value="Back" />';
$url = new moodle_url('/local/learningplan/plan_view.php', array('id'=>$planid,'condtion'=>'manage'));
$out = html_writer::link($url,$return, array('id'=>$planid,'condtion'=>'manage'));
echo $out;
echo $OUTPUT->footer();