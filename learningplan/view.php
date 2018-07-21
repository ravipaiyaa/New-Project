<?php
require_once(dirname(__FILE__) . '/../../config.php');

global $CFG, $PAGE, $OUTPUT;
require_login();
require_once($CFG->dirroot . '/local/learningplan/lib.php');
require_once($CFG->dirroot . '/local/costcenter/lib.php');
require_once($CFG->dirroot . '/local/includes.php');

$PAGE->set_url('/local/learningplan/view.php');

$PAGE->requires->jquery();
//$PAGE->requires->jquery_plugin('ui');
//$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->js('/local/learningplan/js/delete_custom.js');
//$PAGE->requires->js('/local/learningplan/js/unassign_courses_confirm.js');
//$PAGE->requires->js('/local/learningplan/js/unassign_users_confirm.js');


$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->set_title(get_string('pluginname', 'local_learningplan'));
$PAGE->set_pagelayout('iltfullpage');
$id = optional_param('id', null, PARAM_INT);
/**The Below code is for when user click on the course view url he is to login as high role so the code written by niranjan**/
	    $systemcontext = context_system::instance();
	
		$roles = get_user_roles($systemcontext, $USER->id);
		//print_object($roles);
		if(empty($USER->access['rsw']) && (count($roles) >= 1) && !is_siteadmin($USER->id)){
		$singlerole = $roles[key($roles)];
		$rolerecord = $DB->get_record('role', array('id'=>5));
		if(role_switch($rolerecord->id, $systemcontext)){
		// purge_all_caches();
		//redirect('index.php');
		redirect('view.php?id='.$id.'');
		}
		}
	/*end of the code*/
$PAGE->requires->jquery();
echo $OUTPUT->header();

    $renderer = $PAGE->get_renderer('local_learningplan');
    if($id){
		echo $renderer->learningplaninfo_for_employee($id);
    }
    
echo $OUTPUT->footer();